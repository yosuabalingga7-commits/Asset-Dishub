<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceTicket; 
use App\Models\LaporanMasyarakat;
use App\Models\Asset; 
use App\Models\User; 
use App\Notifications\MaintenanceNotification; // TAMBAHAN UNTUK NOTIFIKASI
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
                'is_dishub' => true 
            ];
        });

        // 4. Transformasi data Laporan Warga
        $dataLaporan = $laporanWarga->map(function ($laporan) {
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
                'source' => 'MASYARAKAT', 
                'is_dishub' => true 
            ];
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
        
        // Mengambil user dengan relasi seksi agar di view bisa muncul nama seksinya
        $listSeksi = User::with('seksi')->get();

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
            // AMBIL DATA ORANGNYA BERDASARKAN ID DARI DROPDOWN
            $userDb = User::findOrFail($request->user_id);
            
            $petugasName = $userDb->name;
            $petugasWA = $userDb->no_wa;
            $dbUserId = $userDb->id; 
            $targetSeksiId = $userDb->seksi_id; // KUNCI: Otomatis ambil seksi_id dari profil user

            $ticket = DB::transaction(function () use ($request, $dbUserId, $petugasName, $targetSeksiId) {
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
                    
                    // OTOMATIS SINKRON DENGAN PROFIL USER TERPILIH
                    'seksi_id'         => $targetSeksiId, 
                    'user_id'          => $dbUserId, 
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

            // --- KIRIM NOTIFIKASI INTERNAL KE SEKSI TERPILIH ---
            $userDb->notify(new MaintenanceNotification([
                'title' => 'PENUGASAN BARU',
                'message' => 'Anda ditugaskan untuk tiket ' . $ticket->ticket_code . '. Segera tindaklanjuti.',
                'url' => route('admin.maintenance.show', $ticket->id),
                'type' => 'urgent'
            ]));

            // --- WA BOT NOTIFIKASI ---
            if ($request->send_wa == '1' && !empty($petugasWA)) {
                $urlDetail = route('admin.maintenance.show', $ticket->id);
                $now = Carbon::now();
                $hour = $now->hour;
                
                if ($hour >= 5 && $hour < 11) { $salam = "Selamat Pagi"; }
                elseif ($hour >= 11 && $hour < 15) { $salam = "Selamat Siang"; }
                elseif ($hour >= 15 && $hour < 18) { $salam = "Selamat Sore"; }
                else { $salam = "Selamat Malam"; }

                $panggilan = "Pak/Bu";
                $namaPetugas = strtoupper($petugasName);
                $jenisAset = $request->jenis_aset ?: ($ticket->jenis_aset ?: 'Aset Dishub');

                $pesan = "--- *NOTIFIKASI KBB-SMART ASSET* ---\n\n"
                       . $salam . ", " . $panggilan . " *" . $namaPetugas . "*.\n\n"
                       . "Izin memberitahukan, terdapat penugasan perbaikan aset baru: *" . $jenisAset . "*.\n\n"
                       . "🔗 *DETAIL TUGAS:* \n" . $urlDetail . "\n\n"
                       . "Mohon untuk segera ditindaklanjuti. Terima kasih.\n\n"
                       . "*Admin Dishub KBB*";

                try {
                    Http::timeout(5)->post('http://localhost:3000/send-message', [
                        'phone' => $this->formatPhone($petugasWA),
                        'message' => $pesan,
                    ]);
                } catch (\Exception $waError) {
                    \Log::error("Gagal panggil Robot WA: " . $waError->getMessage());
                }
            }

            return redirect()->route('admin.maintenance.index')->with('success', 'Tiket maintenance berhasil dibuat.');

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

        $listPetugas = User::with('seksi')->get();

        return view('admin.maintenance.edit', compact('ticket', 'categories', 'listPetugas'));
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

                // --- KIRIM NOTIF BALIK KE SUPER ADMIN SAAT SELESAI ---
                $superAdmins = User::where('role', 'super_admin')->get();
                $notifData = [
                    'title' => 'PERBAIKAN SELESAI',
                    'message' => 'Seksi ' . Auth::user()->name . ' telah menyelesaikan tiket ' . $maintenance->ticket_code,
                    'url' => route('admin.maintenance.show', $maintenance->id),
                    'type' => 'success'
                ];
                foreach ($superAdmins as $admin) {
                    $admin->notify(new MaintenanceNotification($notifData));
                }
            }

            $maintenance->update($input);

            DB::commit();
            return redirect()->route('admin.maintenance.index')->with('success', 'Data perbaikan berhasil diperbarui.');
            
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

                // --- KIRIM NOTIF BALIK KE SUPER ADMIN ---
                $superAdmins = User::where('role', 'super_admin')->get();
                foreach ($superAdmins as $admin) {
                    $admin->notify(new MaintenanceNotification([
                        'title' => 'PERBAIKAN SELESAI',
                        'message' => 'Tiket ' . $ticket->ticket_code . ' telah ditandai selesai.',
                        'url' => route('admin.maintenance.show', $ticket->id),
                        'type' => 'success'
                    ]));
                }
            }
            
            $ticket->save();
            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return redirect()->route('admin.maintenance.index')->with('success', 'Status berhasil diperbarui!');
            
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