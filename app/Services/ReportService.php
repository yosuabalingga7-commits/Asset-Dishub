<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Report;
use App\Models\User;
use App\Notifications\MaintenanceNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ReportService
{
    /**
     * Create a public report and update asset status.
     *
     * @param array $data
     * @param \Illuminate\Http\UploadedFile|null $file
     * @return Report|null
     * @throws \Exception
     */
    public function createPublicReport(array $data, $file = null)
    {
        $asset = Asset::find($data['asset_id']);
        if (!$asset) {
            throw new \Exception('Aset tidak ditemukan');
        }

        // Validasi jarak spasial GIS menggunakan ST_DistanceSphere
        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];
        $radius = 100;

        $distanceResult = DB::selectOne(
            "SELECT ST_DistanceSphere(
                ST_SetSRID(ST_MakePoint(?, ?), 4326),
                coordinates
            ) as distance FROM assets WHERE id = ?",
            [$lng, $lat, $asset->id]
        );
        
        $jarak = $distanceResult ? (float) $distanceResult->distance : null;

        if ($jarak === null || $jarak > $radius) {
            Log::warning('Aset terlalu jauh dari lokasi pelapor', [
                'asset_id' => $asset->id,
                'jarak' => $jarak,
                'radius' => $radius
            ]);
            throw new \Exception('Aset yang dipilih terlalu jauh dari lokasi Anda (jarak: ' . round($jarak ?? 0) . ' meter). Silakan pilih aset yang lebih dekat.');
        }

        // Cek apakah aset tersedia untuk dilaporkan (status harus 'Baik')
        if (!$asset->isAvailableForReport()) {
            Log::info('Laporan tidak disimpan - aset tidak tersedia', [
                'nama_pelapor' => $data['nama_pelapor'],
                'judul_laporan' => $data['judul_laporan'],
                'asset_id' => $asset->id,
                'status_aset' => $asset->status
            ]);
            return null; // Mengindikasikan aset sudah dilaporkan sebelumnya
        }

        return DB::transaction(function () use ($data, $file, $asset, $lat, $lng) {
            $ticketNumber = 'LP-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $fotoPath = null;
            if ($file) {
                $fileName = time() . '_' . $ticketNumber . '.' . $file->getClientOriginalExtension();
                $fotoPath = $file->storeAs('laporan_masyarakat', $fileName, 'public');
            }

            $report = Report::create([
                'ticket_number' => $ticketNumber,
                'source' => 'masyarakat',
                'nama_pelapor' => $data['nama_pelapor'],
                'kontak_pelapor' => $data['kontak_pelapor'],
                'judul_laporan' => $data['judul_laporan'],
                'deskripsi_keluhan' => '-',
                'kondisi_aset' => $data['kondisi_aset'],
                'lat' => $lat,
                'lng' => $lng,
                'alamat' => $data['alamat'],
                'lokasi_koordinat' => $lat . ',' . $lng,
                'foto' => $fotoPath,
                'status' => 'masuk',
                'asset_id' => $asset->id,
                'id_asset' => $asset->id_asset,
            ]);

            // Update status aset
            $kondisi = $data['kondisi_aset'];
            $statusBaru = in_array($kondisi, ['hilang', 'lainnya']) ? 'Kritis' : 'Rusak';
            $asset->update(['status' => $statusBaru]);

            Log::info('Status aset diperbarui', [
                'id_asset' => $asset->id_asset,
                'kondisi_laporan' => $kondisi,
                'status_baru' => $statusBaru,
                'ticket_number' => $ticketNumber
            ]);

            // Kirim notifikasi admin dan WA
            $this->sendNotifications($report, $asset);

            return $report;
        });
    }

    /**
     * Create an officer report and update asset status.
     *
     * @param array $data
     * @param \Illuminate\Http\UploadedFile|null $file
     * @return Report
     * @throws \Exception
     */
    public function createOfficerReport(array $data, $file = null)
    {
        $asset = Asset::find($data['asset_id']);
        if (!$asset) {
            throw new \Exception('Aset tidak ditemukan');
        }

        return DB::transaction(function () use ($data, $file, $asset) {
            $ticketNumber = 'LP-P-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $namaFoto = null;
            if ($file) {
                $namaFoto = time() . '_petugas_' . $ticketNumber . '_' . $file->getClientOriginalName();
                $tujuanUpload = public_path('uploads/laporan_petugas');
                if (!File::isDirectory($tujuanUpload)) {
                    File::makeDirectory($tujuanUpload, 0777, true, true);
                }
                $file->move($tujuanUpload, $namaFoto);
            }

            $report = Report::create([
                'ticket_number' => $ticketNumber,
                'source' => 'petugas',
                'nama_petugas'  => $data['nama_pelapor'],
                'nip'           => $data['nip'],
                'no_wa'         => $data['kontak_pelapor'],
                'judul_laporan' => $data['judul_laporan'],
                'kondisi_aset'  => $data['kondisi_aset'],
                'deskripsi'     => $data['deskripsi'] ?? null,
                'foto'          => $namaFoto,
                'lat'           => $data['lat'],
                'lng'           => $data['lng'],
                'status'        => 'masuk',
                'asset_id'      => $asset->id,
                'id_asset'      => $asset->id_asset,
            ]);

            $kondisi = $data['kondisi_aset'];
            $statusBaru = in_array($kondisi, ['Hilang/Dicuri', 'Lainnya']) ? 'Kritis' : 'Rusak';
            $asset->update(['status' => $statusBaru]);

            Log::info('Status aset diperbarui via LaporanPetugasController (Service)', [
                'id_asset' => $asset->id_asset,
                'kondisi_laporan' => $kondisi,
                'status_baru' => $statusBaru,
                'ticket_number' => $ticketNumber
            ]);

            return $report;
        });
    }

    /**
     * Send notifications (Internal Database & WhatsApp API)
     */
    protected function sendNotifications(Report $report, Asset $asset)
    {
        // 1. Notifikasi internal ke super admin
        try {
            $superAdmins = User::where('role', 'admin')->get();
            $notifData = [
                'title' => 'LAPORAN MASYARAKAT BARU',
                'message' => 'Laporan baru dari ' . $report->nama_pelapor . ' mengenai ' . $report->judul_laporan,
                'url' => route('admin.pengaduan.show', $report->id),
                'type' => 'info'
            ];
            /** @var \App\Models\User $admin */
            foreach ($superAdmins as $admin) {
                $admin->notify(new MaintenanceNotification($notifData));
            }
        } catch (\Exception $e) {
            Log::error("Gagal Kirim Notifikasi Internal: " . $e->getMessage());
        }

        // 2. Notifikasi WhatsApp ke Admin
        try {
            $adminUser = User::where('role', 'admin')->whereNotNull('no_wa')->first();
            
            if ($adminUser) {
                $nomorAdminFormatted = $this->formatPhone($adminUser->no_wa);
                $linkDetail = route('admin.pengaduan.show', $report->id);
                
                $pesan = "LAPORAN MASYARAKAT BARU\n\n"
                       . "Tiket: " . $report->ticket_number . "\n"
                       . "Pelapor: " . $report->nama_pelapor . "\n"
                       . "Judul: " . $report->judul_laporan . "\n"
                       . "Lokasi: " . $report->alamat . "\n"
                       . "Kondisi: " . $report->kondisi_aset . "\n"
                       . "ID Aset: " . $asset->id_asset . "\n\n"
                       . "Link Laporan:\n" . $linkDetail . "\n\n"
                       . "Cek detail di Dashboard Admin KBB Smart Asset.";

                Http::timeout(3)->connectTimeout(3)->post('http://localhost:3000/send-message', [
                    'phone' => $nomorAdminFormatted,
                    'message' => $pesan,
                ]);
            }
        } catch (\Exception $waEx) {
            Log::error("Gagal Kirim Notifikasi WA: " . $waEx->getMessage());
        }
    }

    private function formatPhone($phone)
    {
        if (!$phone) return '';
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }
}
