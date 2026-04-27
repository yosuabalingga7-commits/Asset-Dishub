<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceTicket; 
use App\Models\LaporanMasyarakat;
use App\Models\Asset;
use App\Models\User; // TAMBAHAN UNTUK NOTIFIKASI
use App\Notifications\MaintenanceNotification; // TAMBAHAN UNTUK NOTIFIKASI
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TugasController extends Controller
{
    /**
     * Menampilkan daftar tugas yang tersedia
     */
    public function tersedia()
    {
        $userId = Auth::id();
        
        $ticketsFromDb = MaintenanceTicket::with(['report', 'asset', 'user'])
            ->where('user_id', $userId)
            ->whereIn('status', ['pending', 'proses']) 
            ->orderBy('created_at', 'desc')
            ->get();

        $tasks = $ticketsFromDb->map(function ($ticket) {
            $safeFormat = function($date) {
                if (!$date) return '-';
                try {
                    return Carbon::parse($date)->format('d M Y');
                } catch (\Exception $e) {
                    return '-';
                }
            };

            $statusAsli = $ticket->status;
            
            // Menggunakan (object) untuk memastikan Blade bisa memanggil dengan ->
            return (object) [
                'id' => $ticket->id, 
                'ticket_code' => $ticket->ticket_code ?? ('MNT-' . strtoupper(substr(md5($ticket->id), 0, 8))),
                'status' => $statusAsli,
                'prioritas' => $ticket->priority ?? 'NORMAL',
                'asset' => $ticket->asset, 
                'jenis_aset' => $ticket->jenis_aset ?? ($ticket->report->jenis_aset ?? 'Aset'),
                'lokasi' => $ticket->location_address ?? ($ticket->report->alamat ?? ($ticket->latitude . ', ' . $ticket->longitude)),
                'petugas' => $ticket->user->name ?? 'Belum Ditugaskan',
                'tgl_laporan' => $safeFormat($ticket->created_at),
                'foto' => ($ticket->report && $ticket->report->foto) 
                            ? asset('storage/' . $ticket->report->foto) 
                            : (($ticket->asset && $ticket->asset->foto) ? asset('storage/' . $ticket->asset->foto) : asset('img/panelPJU.png')),
                'progress_percent' => $statusAsli == 'selesai' ? 100 : ($statusAsli == 'proses' ? 50 : 0),
                'source' => $ticket->kepemilikan ?? 'DISHUB',
            ];
        });

        return view('admin.petugas.tugas-tersedia', compact('tasks'));
    }

    /**
     * Menampilkan daftar tugas yang sudah selesai
     */
    public function selesai()
    {
        $userId = Auth::id();
        
        $ticketsFromDb = MaintenanceTicket::with(['asset', 'report', 'user'])
            ->where('user_id', $userId)
            ->where('status', 'selesai')
            ->latest()
            ->get();

        $tasks = $ticketsFromDb->map(function ($ticket) {
            return (object) [
                'id' => $ticket->id,
                'ticket_code' => $ticket->ticket_code ?? ('MNT-' . $ticket->id),
                'status' => 'selesai',
                'asset' => $ticket->asset,
                'jenis_aset' => $ticket->jenis_aset ?? ($ticket->report->jenis_aset ?? 'Aset Umum'),
                'location_address' => $ticket->location_address ?? ($ticket->report->alamat ?? 'Bandung Barat'),
                'finished_at' => $ticket->finished_at ? Carbon::parse($ticket->finished_at) : null,
                'updated_at' => Carbon::parse($ticket->updated_at),
                'foto' => ($ticket->report && $ticket->report->foto) ? $ticket->report->foto : ($ticket->asset->foto ?? null),
            ];
        });

        return view('admin.petugas.tugas-selesai', compact('tasks'));
    }

    /**
     * Update status tugas menjadi Selesai
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'foto_perbaikan' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'completion_notes' => 'required'
        ]);

        DB::beginTransaction();
        try {
            $task = MaintenanceTicket::findOrFail($id);
            
            $task->status = 'selesai';
            $task->completion_notes = $request->completion_notes;
            $task->finished_at = now();

            if ($request->hasFile('foto_perbaikan')) {
                $path = $request->file('foto_perbaikan')->store('maintenance/perbaikan', 'public');
                $task->foto_perbaikan = $path;
            }

            if ($task->asset_id) {
                Asset::where('id', $task->asset_id)->update(['status' => 'Baik']);
            }

            if ($task->report_id) {
                LaporanMasyarakat::where('id', $task->report_id)->update(['status' => 'Selesai']);
            }

            $task->save();

            // --- KIRIM NOTIFIKASI BALIK KE SUPER ADMIN ---
            $superAdmins = User::where('role', 'super_admin')->get();
            $notifData = [
                'title' => 'TUGAS SELESAI DIKERJAKAN',
                'message' => 'Seksi ' . Auth::user()->name . ' telah menyelesaikan tugas: ' . ($task->ticket_code ?? 'MNT-'.$task->id),
                'url' => route('admin.maintenance.show', $task->id),
                'type' => 'success'
            ];
            
            foreach ($superAdmins as $admin) {
                $admin->notify(new MaintenanceNotification($notifData));
            }

            DB::commit();

            return back()->with('success', 'Laporan perbaikan berhasil dikirim dan status aset diperbarui!');
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }
}