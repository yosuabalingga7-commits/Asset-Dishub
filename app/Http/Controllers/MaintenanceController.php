<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceTicket; 
use App\Models\Report;
use App\Models\Asset; 
use App\Models\User; 
use App\Models\Category;
use App\Notifications\MaintenanceNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL; 
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http; 

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $ticketsFromDb = MaintenanceTicket::with(['report', 'asset', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // LOGIKA: Ambil laporan masyarakat yang statusnya "Proses Perbaikan" tapi BELUM memiliki tiket
        $laporanWarga = Report::where('status', 'Proses Perbaikan')
            ->whereDoesntHave('tiket') // Menggunakan relasi 'tiket' sesuai diskusi sebelumnya
            ->orderBy('updated_at', 'desc')
            ->get();

        $dataTickets = $ticketsFromDb->map(function (MaintenanceTicket $ticket) {
            $safeFormat = function($date) {
                if (!$date) return '-';
                try {
                    return $date instanceof Carbon ? $date->format('d M Y') : Carbon::parse($date)->format('d M Y');
                } catch (\Exception $e) {
                    return '-';
                }
            };

            // Ambil dari kolom kepemilikan database tiket
            $displaySource = $ticket->kepemilikan ?? ($ticket->isDishub() ? 'DISHUB' : 'Pihak Ketiga');
            
            // Standarisasi string untuk tampilan index halaman admin
            if (strtolower($displaySource) === 'dishub') {
                $displaySource = 'DISHUB';
            } else {
                $displaySource = 'Pihak Ketiga';
            }

            $statusAsli = $ticket->status;
            $statusSlug = strtolower($statusAsli);
            if ($statusSlug == 'proses') $statusSlug = 'process';
            if ($statusSlug == 'selesai') $statusSlug = 'finished';

            return [
                'id' => $ticket->id, 
                'ticket_code' => $ticket->ticket_code,
                'status' => $statusAsli,
                'status_slug' => $statusSlug, 
                'prioritas' => $ticket->priority, 
                'asset' => $ticket->jenis_aset ?? ($ticket->report->jenis_aset ?? 'Aset Umum'),
                'lokasi' => $ticket->location_address ?? ($ticket->report->alamat ?? ($ticket->latitude . ', ' . $ticket->longitude)),
                'deskripsi' => $ticket->description,
                'petugas' => $ticket->user->name ?? $ticket->technician_name ?? 'Belum Ditugaskan', 
                'tgl_laporan' => $safeFormat($ticket->created_at),
                'tgl_mulai'   => $safeFormat($ticket->started_at),
                'tgl_selesai' => $safeFormat($ticket->finished_at),
                'foto_sebelum' => ($ticket->report && $ticket->report->foto) 
                                    ? asset('storage/' . $ticket->report->foto) 
                                    : asset('img/panelPJU.png'),
                'foto_sesudah' => $ticket->foto_perbaikan ? asset('storage/' . $ticket->foto_perbaikan) : null,
                'progress_percent' => $statusAsli == 'selesai' ? 100 : ($statusAsli == 'proses' ? 50 : 0),
                'source' => $displaySource, 
                'is_dishub' => ($displaySource === 'DISHUB') // PERBAIKAN: Dibuat dinamis sesuai isi data database
            ];
        });

        $dataLaporan = $laporanWarga->map(function ($laporan) {
            // PERBAIKAN: Mengambil data kepemilikan yang sudah disimpan saat klik tombol validasi di database
            $sourceLaporan = $laporan->kepemilikan ?? 'Pihak Ketiga';
            
            if (strtolower($sourceLaporan) === 'dishub') {
                $sourceLaporan = 'DISHUB';
            } else {
                $sourceLaporan = 'Pihak Ketiga';
            }

            return [
                'id' => $laporan->id,
                'ticket_code' => 'WAITING',
                'status' => 'pending', 
                'status_slug' => 'pending',
                'prioritas' => $laporan->priority ?? 'NORMAL',
                'asset' => $laporan->judul_laporan,
                'lokasi' => $laporan->alamat ?? ($laporan->lat . ', ' . $laporan->lng),
                'deskripsi' => $laporan->deskripsi_keluhan ?? $laporan->isi_laporan ?? 'Laporan Masyarakat',
                'petugas' => 'Proses Validasi',
                'tgl_laporan' => $laporan->created_at ? $laporan->created_at->format('d M Y') : '-',
                'tgl_mulai' => $laporan->updated_at ? $laporan->updated_at->format('d M Y') : '-',
                'tgl_selesai' => '-',
                'foto_sebelum' => $laporan->foto ? asset('storage/' . $laporan->foto) : asset('img/panelPJU.png'),
                'foto_sesudah' => null,
                'progress_percent' => 0,
                'source' => $sourceLaporan, // PERBAIKAN: Menggunakan nilai dinamis hasil validasi
                'is_dishub' => ($sourceLaporan === 'DISHUB') // PERBAIKAN: Menyesuaikan status kepemilikan secara dinamis
            ];
        });

        $allTickets = $dataTickets->concat($dataLaporan);
        $currentPage = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 9; 
        
        $tickets = new LengthAwarePaginator(
            $allTickets->slice(($currentPage - 1) * $perPage, $perPage)->values(), 
            $allTickets->count(), 
            $perPage, 
            $currentPage, 
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.maintenance.index', compact('tickets'));
    }

    public function create(Request $request)
    {
        $reportId = $request->query('report_id');
        $laporan = $reportId ? Report::find($reportId) : null;
        
        // Logika tambahan: Jika laporan sudah punya tiket, arahkan ke tiket tersebut
        if($laporan && $laporan->tiket) {
            return redirect()->route('admin.maintenance.show', $laporan->tiket->id)
                             ->with('info', 'Tiket untuk laporan ini sudah pernah dibuat.');
        }

        // AMANKAN URL PARAMETER KE COMPACT: Ambil source dari URL filter modal validasi (?source=dishub atau pihak-ke-3)
        $sourceUrl = $request->query('source');

        if ($laporan && $sourceUrl) {
            // Harmonisasi string agar seragam ke format database utama
            $laporan->kepemilikan = (strtolower($sourceUrl) === 'dishub') ? 'Dishub' : 'Pihak Ke-3';
        }

        $listSeksi = User::with('seksi')->get();
        $categories = Category::orderBy('nama_kategori', 'asc')->get(); 

        return view('admin.maintenance.create', compact('laporan', 'categories', 'listSeksi'));
    }

    public function edit($id)
    {
        $ticket = MaintenanceTicket::findOrFail($id);
        $categories = Category::orderBy('nama_kategori', 'asc')->get();
        $listSeksi = User::with('seksi')->get();

        return view('admin.maintenance.create', [
            'maintenance' => $ticket, 
            'laporan' => $ticket->report,
            'categories' => $categories,
            'listSeksi' => $listSeksi
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'     => 'required',
            'category_id' => 'required',
            'category'    => 'required',
            'jenis_aset'  => 'required',
        ]);

        try {
            $userDb = User::findOrFail($request->user_id);
            $laporan = Report::find($request->report_id);
            
            // CEK DOUBLE INPUT: Pastikan laporan belum punya tiket
            if ($laporan && $laporan->tiket) {
                return redirect()->route('admin.maintenance.index')->with('error', 'Tiket untuk laporan ini sudah ada.');
            }

            $ticket = DB::transaction(function () use ($request, $userDb, $laporan) {
                $ticketCode = 'MNT-' . strtoupper(Str::random(8));
                $finalDescription = $request->description ?? ($laporan->deskripsi_keluhan ?? 'Perbaikan rutin aset');

                // FIX UTAMA: Jangan di-hardcode ke 'Pihak Ketiga'! Ambil langsung input source_type dari Form Blade.
                $fixKepemilikan = $request->source_type ?? 'Dishub';

                // PENGAMAN PARAMETER: Ambil asset_id/id_asset secara fleksibel
                $rawAssetId = $request->asset_id ?? $request->id_asset ?? ($laporan ? $laporan->asset_id : null);
                
                // Cari ID numerik asli dari tabel assets untuk foreign key
                $numericAssetId = null;
                if ($rawAssetId) {
                    $assetRecord = DB::table('assets')->where('id', $rawAssetId)->orWhere('id_asset', $rawAssetId)->first();
                    if ($assetRecord) {
                        $numericAssetId = $assetRecord->id;
                    }
                }

                $newTicket = MaintenanceTicket::create([
                    'ticket_code'      => $ticketCode,
                    'report_id'        => $request->report_id, 
                    'asset_id'         => $numericAssetId,
                    'category_id'      => $request->category_id, 
                    'category'         => $request->category,
                    'kepemilikan'      => $fixKepemilikan, 
                    'priority'         => $request->priority ?? 'Sedang',
                    'status'           => 'proses', 
                    'location_address' => $request->location_address,
                    'latitude'         => $request->latitude,
                    'longitude'        => $request->longitude,
                    'description'      => $finalDescription,
                    'jenis_aset'       => $request->jenis_aset,
                    'seksi_id'         => $userDb->seksi_id, 
                    'user_id'          => $userDb->id, 
                    'technician_name'  => $userDb->name, 
                    'deadline'         => $request->deadline,
                    'started_at'       => now(),
                ]);

                if ($request->report_id) {
                    Report::where('id', $request->report_id)->update([
                        'status' => 'Proses Perbaikan',
                        'kepemilikan' => $fixKepemilikan,
                        'updated_at' => now()
                    ]);
                }
                return $newTicket;
            });

            $userDb->notify(new MaintenanceNotification([
                'title' => 'PENUGASAN BARU',
                'message' => 'Tiket ' . $ticket->ticket_code . ' ditugaskan kepada Anda.',
                'url' => route('admin.maintenance.show', $ticket->id),
                'type' => 'urgent'
            ]));

            if ($request->send_wa == '1' && !empty($userDb->no_wa)) {
                $this->sendWaNotification($userDb, $ticket, $laporan);
            }

            return redirect()->route('admin.maintenance.index')->with('success', 'Tiket maintenance berhasil dibuat.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    private function sendWaNotification($user, $ticket, $laporan = null)
    {
        $jam = date('H');
        if ($jam < 10) {
            $waktu = 'Pagi';
        } elseif ($jam < 15) {
            $waktu = 'Siang';
        } elseif ($jam < 18) {
            $waktu = 'Sore';
        } else {
            $waktu = 'Malam';
        }

        $namaAset = $ticket->jenis_aset ?? ($laporan->judul_laporan ?? 'Aset Dishub');
        $linkTugas = route('admin.maintenance.show', $ticket->id);

        $pesan = "--- NOTIFIKASI KBB-SMART ASSET ---\n\n"
               . "Selamat $waktu, Pak/Bu " . strtoupper($user->name) . ".\n\n"
               . "Izin memberitahukan, terdapat penugasan perbaikan aset baru: *$namaAset*.\n\n"
               . "Untuk informasi lebih lengkap mengenai lokasi dan instruksi pengerjaan, silakan klik tautan resmi berikut:\n\n"
               . "🔗 DETAIL TUGAS: \n$linkTugas\n\n"
               . "Mohon untuk segera ditindaklanjuti. Terima kasih atas kerja samanya.\n\n"
               . "Hormat kami,\n"
               . "Admin Dishub KBB";

        try {
            Http::timeout(5)->post('http://localhost:3000/send-message', [
                'phone' => $this->formatPhone($user->no_wa),
                'message' => $pesan,
            ]);
        } catch (\Exception $e) { 
            \Log::error("WA Error: " . $e->getMessage()); 
        }
    }

    public function update(Request $request, $id) 
    {
        $request->validate([
            'status' => 'required',
            'foto_perbaikan' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        try {
            DB::beginTransaction();
            $maintenance = MaintenanceTicket::findOrFail($id);
            $input = $request->except('foto_perbaikan');

            if ($request->status == 'selesai') {
                $input['finished_at'] = now();
                if ($request->hasFile('foto_perbaikan')) {
                    $input['foto_perbaikan'] = $request->file('foto_perbaikan')->store('maintenance/perbaikan', 'public');
                }
                
                // Amankan target ID Numerik dari tiket perbaikan
                $assetId = $maintenance->asset_id;
                
                if ($assetId) {
                    // Update ke tabel assets menggunakan ID numerik asli
                    DB::table('assets')->where('id', $assetId)->update([
                        'status' => 'Baik',
                        'updated_at' => now()
                    ]);
                }
                if ($maintenance->report_id) {
                    Report::where('id', $maintenance->report_id)->update(['status' => 'Selesai']);
                }
            }

            $maintenance->update($input);
            DB::commit();
            return redirect()->route('admin.maintenance.index')->with('success', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function show($id) { 
        $ticket = MaintenanceTicket::with(['report', 'user', 'asset'])->findOrFail($id); 
        return view('admin.maintenance.show', compact('ticket')); 
    }

    private function formatPhone($phone) {
        if (!$phone) return '';
        $phone = preg_replace('/[^0-9]/', '', $phone);
        return str_starts_with($phone, '0') ? '62' . substr($phone, 1) : $phone;
    }
}