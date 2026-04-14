<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceTicket; 
use App\Models\LaporanMasyarakat;
use App\Models\Asset; 
use App\Models\User; 
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
        // 1. Ambil data dari tabel maintenance_tickets
        $ticketsFromDb = MaintenanceTicket::with(['report', 'asset', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Ambil data dari tabel LaporanMasyarakat yang berstatus 'Proses Perbaikan' 
        $laporanWarga = LaporanMasyarakat::where('status', 'Proses Perbaikan')
            ->whereDoesntHave('maintenanceTicket') 
            ->orderBy('updated_at', 'desc')
            ->get();

        // 3. Transformasi data MaintenanceTicket
        $dataTickets = $ticketsFromDb->map(function ($ticket) {
            $safeFormat = function($date) {
                if (!$date) return '-';
                try {
                    return $date instanceof Carbon ? $date->format('d M Y') : Carbon::parse($date)->format('d M Y');
                } catch (\Exception $e) {
                    return '-';
                }
            };

            $displaySource = $ticket->kepemilikan;
            if (!$displaySource) {
                $displaySource = $ticket->isDishub() ? 'DISHUB' : 'UMUM/PIHAK 3';
            }

            $statusSlug = strtolower($ticket->status);
            if ($statusSlug == 'proses') $statusSlug = 'process';
            if ($statusSlug == 'selesai') $statusSlug = 'finished';

            return [
                'id' => $ticket->id, 
                'ticket_code' => $ticket->ticket_code,
                'status' => $statusSlug, 
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
                'progress_percent' => $statusSlug == 'finished' ? 100 : ($statusSlug == 'process' ? 50 : 0),
                'source' => $displaySource, 
                'is_dishub' => (str_contains(strtoupper($displaySource), 'DISHUB'))
            ];
        })->filter(function($item) {
            return $item['is_dishub'] === true;
        });

        // 4. Transformasi data Laporan Warga
        $dataLaporan = $laporanWarga->map(function ($laporan) {
            $isProbablyDishub = str_contains(strtolower($laporan->judul_laporan), 'pju') || 
                                str_contains(strtolower($laporan->judul_laporan), 'lalin') ||
                                str_contains(strtolower($laporan->judul_laporan), 'rambu');

            return [
                'id' => $laporan->id,
                'ticket_code' => 'WAITING',
                'status' => 'pending', 
                'status_slug' => 'pending',
                'prioritas' => $laporan->priority ?? 'NORMAL',
                'asset' => $laporan->judul_laporan,
                'lokasi' => $laporan->alamat ?? ($laporan->lat . ', ' . $laporan->lng),
                'deskripsi' => $laporan->isi_laporan ?? $laporan->deskripsi_laporan,
                'petugas' => 'Proses Validasi',
                'tgl_laporan' => $laporan->created_at ? $laporan->created_at->format('d M Y') : '-',
                'tgl_mulai' => $laporan->updated_at ? $laporan->updated_at->format('d M Y') : '-',
                'tgl_selesai' => '-',
                'foto_sebelum' => $laporan->foto ? asset('storage/' . $laporan->foto) : asset('img/panelPJU.png'),
                'foto_sesudah' => null,
                'progress_percent' => 0,
                'source' => $isProbablyDishub ? 'DISHUB' : 'UMUM', 
                'is_dishub' => $isProbablyDishub 
            ];
        })->filter(function($item) {
            return $item['is_dishub'] === true;
        });

        $allTickets = $dataTickets->concat($dataLaporan);

        $currentPage = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 9; 
        $currentItems = $allTickets->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        $tickets = new LengthAwarePaginator(
            $currentItems, 
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
        $laporan = $reportId ? LaporanMasyarakat::find($reportId) : null;
        
        $listSeksi = collect([
            (object)['id' => 101, 'name' => 'Seksi PJU', 'no_wa' => '081234567890', 'gender' => 'pria'],
            (object)['id' => 102, 'name' => 'Seksi Perlengkapan Jalan', 'no_wa' => '081234567891', 'gender' => 'wanita'],
            (object)['id' => 103, 'name' => 'Seksi Fasilitas Lalin', 'no_wa' => '081234567892', 'gender' => 'pria']
        ]);

        $realUsers = User::all();
        if ($realUsers->isNotEmpty()) {
            $listSeksi = $listSeksi->concat($realUsers);
        }

        $categories = collect([
            (object)['id' => 1, 'name' => 'PENERANGAN JALAN UMUM (PJU)'],
            (object)['id' => 2, 'name' => 'PERLENGKAPAN JALAN'],
            (object)['id' => 3, 'name' => 'FASILITAS LALU LINTAS'],
            (object)['id' => 4, 'name' => 'PENGENDALIAN & PENGAWASAN'],
            (object)['id' => 5, 'name' => 'PRASARANA TRANSPORTASI']
        ]);

        return view('admin.maintenance.create', compact('laporan', 'categories', 'listSeksi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required',
            'user_id'     => 'required',
        ]);

        try {
            $userId = $request->user_id;
            $petugasName = 'Unknown';
            $petugasWA = '';

            $userDb = User::find($userId);
            if ($userDb) {
                $petugasName = $userDb->name;
                $petugasWA = $userDb->no_wa;
            } else {
                $staticSeksi = [
                    101 => ['name' => 'Seksi PJU', 'wa' => '081234567890', 'gender' => 'pria'],
                    102 => ['name' => 'Seksi Perlengkapan Jalan', 'wa' => '081234567891', 'gender' => 'wanita'],
                    103 => ['name' => 'Seksi Fasilitas Lalin', 'wa' => '081234567892', 'gender' => 'pria'],
                ];
                if (isset($staticSeksi[$userId])) {
                    $petugasName = $staticSeksi[$userId]['name'];
                    $petugasWA = $staticSeksi[$userId]['wa'];
                }
            }

            $ticket = DB::transaction(function () use ($request, $userId, $petugasName) {
                $ticketCode = 'MNT-' . strtoupper(Str::random(8));
                $categoryName = $request->category ?? ''; 
                
                $isDishubCategory = str_contains(strtolower($categoryName), 'pju') || 
                                    str_contains(strtolower($categoryName), 'lalu lintas');

                $kepemilikanValue = $request->source_type ?? ($isDishubCategory ? 'DISHUB' : 'UMUM/PIHAK 3');

                $newTicket = MaintenanceTicket::create([
                    'ticket_code'      => $ticketCode,
                    'report_id'        => $request->report_id, 
                    'asset_id'         => $request->asset_id,
                    'category_id'      => $request->category_id, 
                    'category'         => $request->category ?? 'umum',
                    'kepemilikan'      => $kepemilikanValue, 
                    'priority'         => $request->priority ?? 'Sedang',
                    'status'           => 'proses', 
                    'location_address' => $request->location_address,
                    'latitude'         => $request->latitude,
                    'longitude'        => $request->longitude,
                    'description'      => $request->description,
                    'jenis_aset'       => $request->jenis_aset,
                    'user_id'          => $userId < 100 ? $userId : null,
                    'technician_name'  => $petugasName, 
                    'deadline'         => $request->deadline,
                    'edit_reason'      => $request->edit_reason,
                    'started_at'       => now(),
                ]);

                if ($request->report_id) {
                    LaporanMasyarakat::where('id', $request->report_id)->update([
                        'status' => 'Proses Perbaikan',
                        'updated_at' => now()
                    ]);
                }
                return $newTicket;
            });

            // --- UPDATE FUNGSI WA BOT (OTOMATIS TANPA REDIRECT) ---
            if ($request->send_wa == '1' && !empty($petugasWA)) {
                $urlDetail = route('admin.maintenance.show', $ticket->id);
                $now = Carbon::now();
                $hour = $now->hour;
                
                if ($hour >= 5 && $hour < 11) { $salam = "Selamat Pagi"; }
                elseif ($hour >= 11 && $hour < 15) { $salam = "Selamat Siang"; }
                elseif ($hour >= 15 && $hour < 18) { $salam = "Selamat Sore"; }
                else { $salam = "Selamat Malam"; }

                $staticSeksiInfo = [
                    101 => ['gender' => 'pria'],
                    102 => ['gender' => 'wanita'],
                    103 => ['gender' => 'pria'],
                ];
                $panggilan = (isset($staticSeksiInfo[$userId]) && $staticSeksiInfo[$userId]['gender'] == 'wanita') ? "Bu" : "Pak";
                $namaPetugas = strtoupper($petugasName);
                $jenisAset = $request->jenis_aset ?: ($ticket->jenis_aset ?: 'Aset Dishub');

                $pesan = "--- *NOTIFIKASI KBB-SMART ASSET* ---\n\n"
                       . $salam . ", " . $panggilan . " *" . $namaPetugas . "*.\n\n"
                       . "Izin memberitahukan, terdapat penugasan perbaikan aset baru: *" . $jenisAset . "*.\n\n"
                       . "Untuk informasi lebih lengkap mengenai lokasi dan instruksi pengerjaan, silakan klik tautan resmi berikut:\n\n"
                       . "🔗 *DETAIL TUGAS:* \n" . $urlDetail . "\n\n"
                       . "Mohon untuk segera ditindaklanjuti. Terima kasih atas kerja samanya.\n\n"
                       . "_Hormat kami,_\n"
                       . "*Admin Dishub KBB*";

                // PANGGIL ROBOT WA DI PORT 3000
                try {
                    Http::timeout(5)->post('http://localhost:3000/send-message', [
                        'phone' => $petugasWA,
                        'message' => $pesan,
                    ]);
                } catch (\Exception $waError) {
                    \Log::error("Gagal panggil Robot WA: " . $waError->getMessage());
                    // Tetap lanjut redirect walau WA gagal agar data tersimpan
                }
            }
            // --- AKHIR UPDATE WA BOT ---

            return redirect()->route('admin.maintenance.index')->with('success', 'Tiket maintenance berhasil dibuat dan instruksi telah dikirim otomatis via WhatsApp.');

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $ticket = MaintenanceTicket::findOrFail($id);
        
        $categories = collect([
            (object)['id' => 1, 'name' => 'PENERANGAN JALAN UMUM (PJU)'],
            (object)['id' => 2, 'name' => 'PERLENGKAPAN JALAN'],
            (object)['id' => 3, 'name' => 'FASILITAS LALU LINTAS'],
            (object)['id' => 4, 'name' => 'PENGENDALIAN & PENGAWASAN'],
            (object)['id' => 5, 'name' => 'PRASARANA TRANSPORTASI']
        ]);

        $listPetugas = collect([
            (object)['id' => 101, 'name' => 'Seksi PJU', 'gender' => 'pria'],
            (object)['id' => 102, 'name' => 'Seksi Perlengkapan Jalan', 'gender' => 'wanita'],
            (object)['id' => 103, 'name' => 'Seksi Fasilitas Lalin', 'gender' => 'pria']
        ]);

        $realUsers = User::all();
        if ($realUsers->isNotEmpty()) {
            $listPetugas = $listPetugas->concat($realUsers);
        }

        return view('admin.maintenance.edit', compact('ticket', 'categories', 'listPetugas'));
    }

    public function update(Request $request, $id) 
    {
        $request->validate([
            'description' => 'nullable',
            'status' => 'required',
            'foto_perbaikan' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'completion_notes' => 'nullable'
        ]);

        try {
            DB::beginTransaction();

            $maintenance = MaintenanceTicket::findOrFail($id);
            $input = $request->all();

            if ($request->status == 'selesai') {
                $input['finished_at'] = now();

                if ($request->hasFile('foto_perbaikan')) {
                    $file = $request->file('foto_perbaikan');
                    $filename = 'perbaikan_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('maintenance/perbaikan', $filename, 'public');
                    $input['foto_perbaikan'] = $path;
                }

                if ($maintenance->asset_id) {
                    Asset::where('id', $maintenance->asset_id)->update(['status' => 'Baik']);
                }

                if ($maintenance->report_id) {
                    LaporanMasyarakat::where('id', $maintenance->report_id)->update(['status' => 'Selesai']);
                }
            }

            $maintenance->update($input);

            DB::commit();
            return redirect()->route('admin.maintenance.index')->with('success', 'Data perbaikan berhasil dikonfirmasi dan status aset telah diperbarui.');
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function show($id) 
    { 
        $ticket = MaintenanceTicket::with(['report', 'user', 'asset'])->findOrFail($id); 
        return view('admin.maintenance.show', compact('ticket')); 
    }

    public function updateStatus(Request $request, $id) 
    { 
        try {
            DB::beginTransaction();
            
            $ticket = MaintenanceTicket::findOrFail($id);
            
            $ticket->status = $request->status;
            $ticket->completion_notes = $request->completion_notes;
            
            if($request->status == 'selesai') {
                $ticket->finished_at = now();
                
                if ($request->hasFile('foto_perbaikan')) {
                    $file = $request->file('foto_perbaikan');
                    $filename = 'perbaikan_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('maintenance/perbaikan', $filename, 'public');
                    $ticket->foto_perbaikan = $path;
                }

                if ($ticket->asset_id) {
                    Asset::where('id', $ticket->asset_id)->update(['status' => 'Baik']);
                }

                if ($ticket->report_id) {
                    LaporanMasyarakat::where('id', $ticket->report_id)->update(['status' => 'Selesai']);
                }
            }
            
            $ticket->save();
            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('admin.maintenance.index')->with('success', 'Tugas berhasil diselesaikan!');
            
        } catch (\Exception $e) {
            DB::rollback();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
    
    private function formatPhone($phone) {
        if (!$phone) return '';
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }
}