<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceTicket; 
use App\Models\User;
use App\Models\Asset;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PenugasanController extends Controller
{
    public function tugasTersedia()
    {
        $userId = Auth::id();
        $tasks = MaintenanceTicket::with(['asset', 'report'])
            ->where('user_id', $userId)
            ->where('status', 'proses')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.petugas.tugas-tersedia', compact('tasks'));
    }

    public function riwayatTugas()
    {
        $userId = Auth::id();
        $tasks = MaintenanceTicket::with(['asset', 'report'])
            ->where('user_id', $userId)
            ->where('status', 'selesai')
            ->orderBy('finished_at', 'desc')
            ->get();

        return view('admin.petugas.tugas-selesai', compact('tasks'));
    }

    public function index($id)
    {
        $ticket = MaintenanceTicket::with(['asset', 'user', 'report'])
            ->where('id', $id)
            ->first();

        if (!$ticket) {
            return redirect()->back()->with('error', 'Tiket tidak ditemukan.');
        }

        $logs = DB::table('maintenance_logs')
            ->join('users', 'maintenance_logs.user_id', '=', 'users.id')
            ->where('ticket_id', $id)
            ->select('maintenance_logs.*', 'users.name as user_name')
            ->orderBy('created_at', 'desc')
            ->get();

        $petugasList = User::all(); 

        return view('admin.penugasan.index', compact('ticket', 'logs', 'petugasList'));
    }

    public function assignTask(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'deadline' => 'required|date'
        ]);

        DB::transaction(function () use ($request, $id) {
            $ticket = MaintenanceTicket::findOrFail($id);
            $assignedUser = User::find($request->user_id);

            $ticket->update([
                'seksi_id' => $assignedUser->seksi_id, 
                'user_id' => $request->user_id,
                'deadline' => $request->deadline,
                'started_at' => now(),
                'status' => 'proses',
            ]);

            DB::table('maintenance_logs')->insert([
                'ticket_id' => $id,
                'user_id' => Auth::id(),
                'action' => 'Penugasan Petugas',
                'note' => 'Tiket telah ditugaskan kepada petugas lapangan.',
                'status_after' => 'proses',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });

        return redirect()->back()->with('success', 'Petugas berhasil ditugaskan.');
    }

    public function updateProgress(Request $request, $id)
    {
        $request->validate([
            'status_to' => 'required',
            'note' => 'required',
            'progress_percent' => 'nullable|integer|min:0|max:100',
            'attachment' => 'nullable|image|max:5120'
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('maintenance/perbaikan', 'public');
        }

        DB::transaction(function () use ($request, $id, $path) {
            $ticket = MaintenanceTicket::findOrFail($id);
            
            $updateData = [
                'status' => $request->status_to,
            ];

            if ($request->status_to == 'selesai') {
                $updateData['foto_perbaikan'] = $path;
                $updateData['finished_at'] = now();
                $updateData['completion_notes'] = $request->note;

                // SINKRONISASI KE ASET & LAPORAN
                if ($ticket->asset_id) {
                    Asset::where('id', $ticket->asset_id)->update(['status' => 'Baik']);
                }
                if ($ticket->report_id) {
                    Report::where('id', $ticket->report_id)->update(['status' => 'Selesai']);
                }
            }

            $ticket->update($updateData);

            DB::table('maintenance_logs')->insert([
                'ticket_id' => $id,
                'user_id' => Auth::id(),
                'action' => 'Update Progres Lapangan',
                'note' => $request->note,
                'photo_evidence' => $path,
                'status_after' => $request->status_to,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });

        return redirect()->back()->with('success', 'Progres berhasil diperbarui.');
    }
}