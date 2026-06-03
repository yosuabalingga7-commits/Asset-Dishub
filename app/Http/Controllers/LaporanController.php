<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;  
use App\Models\LaporanMasyarakat;
use App\Models\User;
use App\Notifications\MaintenanceNotification;
use Illuminate\Support\Str;          
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class LaporanController extends Controller
{
    /**
     * --- TAMBAHAN UNTUK LANDING PAGE LINTAS ---
     * Menampilkan Halaman Depan Utama
     */
    public function indexLanding()
    {
        return view('landing.index');
    }

    /**
     * --- TAMBAHAN UNTUK FORM PUBLIK ---
     * Menampilkan Form untuk Masyarakat (User Umum)
     */
    public function createPublic()
    {
        $assets = Asset::all();
        return view('masyarakat.lapor', compact('assets'));
    }

    /**
     * --- TAMBAHAN UNTUK PROSES SIMPAN PUBLIK ---
     * Memproses simpan dari form landing page (menggunakan method store yang sudah ada)
     */
    public function storePublic(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Tampilan Form untuk Masyarakat (User Umum) - Method Lama
     */
    public function index()
    {
        $assets = Asset::all();
        return view('masyarakat.lapor', compact('assets'));
    }

    /**
     * API untuk mencari semua aset terdekat dalam radius
     */
    public function nearestAssets(Request $request)
    {
        $lat = $request->get('lat');
        $lng = $request->get('lng');
        $radius = $request->get('radius', 100);
        
        if (!$lat || !$lng) {
            return response()->json(['aset' => []]);
        }
        
        $asets = Asset::withinRadius((float)$lat, (float)$lng, (int)$radius)->get();
        
        return response()->json([
            'aset' => $asets->map(function($item) {
                return [
                    'id' => $item->id,
                    'id_asset' => $item->id_asset,
                    'nama' => $item->nama,
                    'kategori' => $item->kategori,
                    'lat' => (float)$item->lat,
                    'lng' => (float)$item->lng,
                    'jarak' => (float)$item->distance
                ];
            })
        ]);
    }

    /**
     * Format nomor WA ke format internasional (62xxx)
     */
    private function formatPhone($phone)
    {
        if (!$phone) return '';
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }

    /**
     * Proses Simpan Laporan dari Masyarakat
     * FITUR BARU:
     * - User memilih aset dari dropdown
     * - Validasi jarak aset dari lokasi pelapor
     * - Hanya aset dengan status 'Baik' yang bisa dilaporkan
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $rules = [
            'nama_pelapor' => 'required|string|max:255',
            'kontak_pelapor' => 'required|numeric|digits_between:10,15',
            'judul_laporan' => 'required|string|max:255',
            'kondisi_aset' => 'required', 
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'alamat' => 'required',
            'asset_id' => 'required|exists:assets,id',
        ];
        
        $request->validate($rules);

        try {
            // 2. Ambil aset yang dipilih user
            $selectedAssetId = $request->input('asset_id');
            $asset = Asset::find($selectedAssetId);
            
            if (!$asset) {
                throw new \Exception('Aset tidak ditemukan');
            }
            
            // 3. Validasi jarak aset dari lokasi pelapor
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;
            $radius = 100;
            
            $jarak = $this->calculateDistance($asset->lat, $asset->lng, $lat, $lng);
            
            if ($jarak > $radius) {
                \Log::warning('Aset terlalu jauh dari lokasi pelapor', [
                    'asset_id' => $selectedAssetId,
                    'jarak' => $jarak,
                    'radius' => $radius
                ]);
                throw new \Exception('Aset yang dipilih terlalu jauh dari lokasi Anda (jarak: ' . round($jarak) . ' meter). Silakan pilih aset yang lebih dekat.');
            }
            
            // 4. Cek status aset
            $isAssetAvailable = $asset->isAvailableForReport();
            
            if (!$isAssetAvailable) {
                $message = 'Terima kasih atas kontribusi Anda! Laporan Anda telah kami terima dan akan segera ditindaklanjuti oleh tim Dishub Kabupaten Bandung Barat.';
                
                \Log::info('Laporan tidak disimpan - aset tidak tersedia', [
                    'nama_pelapor' => $request->nama_pelapor,
                    'judul_laporan' => $request->judul_laporan,
                    'asset_id' => $selectedAssetId,
                    'status_aset' => $asset->status
                ]);
                
                if ($request->is('lapor-kerusakan/*')) {
                    return redirect()->route('landing')->with('success', $message);
                }
                return back()->with('success', $message);
            }
            
            // 5. Buat ID Tiket Otomatis
            $ticketNumber = 'LP-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            // 6. Proses Simpan Foto
            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $fileName = time() . '_' . $ticketNumber . '.' . $file->getClientOriginalExtension();
                $fotoPath = $file->storeAs('laporan_masyarakat', $fileName, 'public');
            }

            // 7. Simpan ke Database
            $laporan = LaporanMasyarakat::create([
                'ticket_number' => $ticketNumber,
                'nama_pelapor' => $request->nama_pelapor,
                'kontak_pelapor' => $request->kontak_pelapor,
                'judul_laporan' => $request->judul_laporan,
                'deskripsi_keluhan' => '-', 
                'kondisi_aset' => $request->kondisi_aset,
                'lat' => $request->lat,
                'lng' => $request->lng,
                'alamat' => $request->alamat,
                'lokasi_koordinat' => $request->lat . ',' . $request->lng,
                'foto' => $fotoPath,
                'status' => 'masuk',
                'asset_id' => $asset->id,
                'id_asset' => $asset->id_asset,
            ]);

            // 8. UPDATE STATUS ASET
            $kondisi = $request->input('kondisi_aset');
            
            if (in_array($kondisi, ['hilang', 'lainnya'])) {
                $statusBaru = 'Kritis';
            } else {
                $statusBaru = 'Rusak';
            }
            
            $asset->update(['status' => $statusBaru]);
            
            \Log::info('Status aset diperbarui', [
                'id_asset' => $asset->id_asset,
                'kondisi_laporan' => $kondisi,
                'status_baru' => $statusBaru,
                'ticket_number' => $ticketNumber
            ]);

            // 9. KIRIM NOTIFIKASI INTERNAL KE SUPER ADMIN
            $superAdmins = User::where('role', 'admin')->get();
            $notifData = [
                'title' => 'LAPORAN MASYARAKAT BARU',
                'message' => 'Laporan baru dari ' . $request->nama_pelapor . ' mengenai ' . $request->judul_laporan,
                'url' => route('admin.pengaduan.show', $laporan->id),
                'type' => 'info'
            ];
            foreach ($superAdmins as $admin) {
                $admin->notify(new MaintenanceNotification($notifData));
            }

            // 10. KIRIM NOTIFIKASI WHATSAPP KE ADMIN
            try {
                $adminUser = User::where('role', 'admin')->whereNotNull('no_wa')->first();
                
                if ($adminUser) {
                    $nomorAdminFormatted = $this->formatPhone($adminUser->no_wa);
                    $linkDetail = route('admin.pengaduan.show', $laporan->id);
                    
                    $pesan = "LAPORAN MASYARAKAT BARU\n\n"
                           . "Tiket: " . $ticketNumber . "\n"
                           . "Pelapor: " . $request->nama_pelapor . "\n"
                           . "Judul: " . $request->judul_laporan . "\n"
                           . "Lokasi: " . $request->alamat . "\n"
                           . "Kondisi: " . $request->kondisi_aset . "\n"
                           . "ID Aset: " . $asset->id_asset . "\n\n"
                           . "Link Laporan:\n" . $linkDetail . "\n\n"
                           . "Cek detail di Dashboard Admin KBB Smart Asset.";

                    Http::timeout(3)->connectTimeout(3)->post('http://localhost:3000/send-message', [
                        'phone' => $nomorAdminFormatted,
                        'message' => $pesan,
                    ]);
                }
            } catch (\Exception $waEx) {
                \Log::error("Gagal Kirim Notifikasi WA: " . $waEx->getMessage());
            }

            // 11. Pesan sukses dengan tiket
            $message = 'Laporan berhasil dikirim! Tiket: ' . $ticketNumber;
            
            if ($request->is('lapor-kerusakan/*')) {
                return redirect()->route('landing')->with('success', $message);
            }
            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan laporan: ' . $e->getMessage());
        }
    }
    
    /**
     * Hitung jarak antara dua koordinat dalam meter (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meter
        
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Tampilan Daftar Laporan untuk Admin (admin/laporan)
     */
    public function adminIndex()
    {
        $tickets = LaporanMasyarakat::latest()->get(); 
        return view('admin.pengaduan.index', compact('tickets'));
    }

    /**
     * Tampilan Manajemen Tiket untuk Admin (admin/tiket)
     */
    public function adminTiket()
    {
        $tickets = LaporanMasyarakat::latest()->get();
        return view('admin.pengaduan.index', compact('tickets'));
    }

    /**
     * Detail Laporan/Tiket
     */
    public function show($id)
    {
        $laporan = LaporanMasyarakat::with('asset')->findOrFail($id);
        
        if(!$laporan->logs) {
            $laporan->logs = collect([]);
        }

        return view('admin.pengaduan.detail', compact('laporan'));
    }

    /**
     * ========== METHOD BARU: VALIDASI LAPORAN OLEH ADMIN ==========
     * Memproses pilihan kepemilikan 'Dishub' atau 'Pihak Ke-3'
     */
    public function validateReport(Request $request, $id)
    {
        $request->validate([
            'kepemilikan' => 'required|in:Dishub,Pihak Ke-3',
            'catatan_admin' => 'nullable|string'
        ]);

        try {
            $laporan = LaporanMasyarakat::findOrFail($id);
            
            $laporan->update([
                'is_validated' => true,
                'kepemilikan' => $request->kepemilikan,
                'status' => 'Proses Perbaikan',
                'catatan_admin' => $request->catatan_admin
            ]);

            if ($laporan->id_asset) {
                Asset::where('id_asset', $laporan->id_asset)->update([
                    'status' => 'Proses Perbaikan'
                ]);
            }

            return back()->with('success', 'Laporan berhasil divalidasi dengan kepemilikan: ' . $request->kepemilikan);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memvalidasi laporan: ' . $e->getMessage());
        }
    }

    /**
     * ========== METHOD BARU: UPDATE STATUS / KONFIRMASI PETUGAS OLEH ADMIN ==========
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required',
            'foto_perbaikan' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'catatan_admin' => 'nullable|string'
        ]);

        try {
            $laporan = LaporanMasyarakat::findOrFail($id);
            $statusBaru = $request->status;

            $fotoPath = null;
            if ($request->hasFile('foto_perbaikan')) {
                $file = $request->file('foto_perbaikan');
                $fileName = 'perbaikan_' . time() . '_' . $laporan->ticket_number . '.' . $file->getClientOriginalExtension();
                $fotoPath = $file->storeAs('assets/perbaikan', $fileName, 'public');
            }

            $laporan->update([
                'status' => $statusBaru,
                'catatan_admin' => $request->catatan_admin ?? $laporan->catatan_admin
            ]);

            if (strtolower($statusBaru) == 'baik' || strtolower($statusBaru) == 'selesai') {
                if ($laporan->id_asset) {
                    $dataUpdateAset = [
                        'status' => 'Baik',
                        'catatan_terakhir' => $request->catatan_admin
                    ];

                    if ($fotoPath) {
                        $dataUpdateAset['foto_terakhir'] = $fotoPath;
                    }

                    Asset::where('id_asset', $laporan->id_asset)->update($dataUpdateAset);
                    
                    \Log::info("Asset {$laporan->id_asset} berhasil dikembalikan ke status 'Baik' via konfirmasi laporan.");
                }
            }

            return back()->with('success', 'Status laporan dan data aset berhasil diperbarui menjadi: ' . $statusBaru);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui status laporan: ' . $e->getMessage());
        }
    }
}