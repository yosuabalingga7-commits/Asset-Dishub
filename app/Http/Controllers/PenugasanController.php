<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PenugasanController extends Controller
{
    /**
     * Menampilkan Halaman Detail Penugasan & Tindak Lanjut
     */
    public function index($id)
    {
        // Menggunakan Eloquent Model 'Ticket' agar sinkron dengan Migration & Model yang baru
        // Kita eager load 'asset' dan 'petugas' sesuai relasi di Model
        $ticket = Ticket::with(['asset', 'petugas'])
            ->where('id', $id)
            ->first();

        if (!$ticket) {
            return redirect()->back()->with('error', 'Tiket tidak ditemukan.');
        }

        // Ambil Timeline (Logs) - Sesuaikan nama tabel jika Anda punya tabel logs
        // Jika belum ada tabel maintenance_logs, bagian ini bisa disesuaikan nanti
        $logs = DB::table('maintenance_logs')
            ->join('users', 'maintenance_logs.user_id', '=', 'users.id')
            ->where('ticket_id', $id)
            ->select('maintenance_logs.*', 'users.name as user_name')
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil daftar petugas untuk dropdown (Filter berdasarkan role petugas)
        $petugasList = User::where('role', 'petugas')->get();

        return view('admin.penugasan.index', compact('ticket', 'logs', 'petugasList'));
    }

    /**
     * Logika Menugaskan Petugas
     */
    public function assignTask(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'deadline' => 'required|date'
        ]);

        DB::transaction(function () use ($request, $id) {
            // Update menggunakan Model agar 'updated_at' otomatis
            $ticket = Ticket::findOrFail($id);
            $ticket->update([
                'assigned_to' => $request->assigned_to,
                'deadline' => $request->deadline,
                'assigned_at' => now(),
                'status' => 'Ditugaskan', // Sesuaikan dengan Enum di Migration Anda
            ]);

            // Catat ke Timeline (Pastikan tabel maintenance_logs sudah ada)
            DB::table('maintenance_logs')->insert([
                'ticket_id' => $id,
                'user_id' => Auth::id(),
                'action' => 'Penugasan Petugas',
                'note' => 'Tiket telah ditugaskan kepada petugas lapangan.',
                'status_after' => 'Ditugaskan',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });

        return redirect()->back()->with('success', 'Petugas berhasil ditugaskan.');
    }

    /**
     * Logika Update Progres Lapangan
     */
    public function updateProgress(Request $request, $id)
    {
        $request->validate([
            'status_to' => 'required',
            'note' => 'required',
            'progress_percent' => 'nullable|integer|min:0|max:100',
            'attachment' => 'nullable|image|max:2048'
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            // Simpan foto sesudah perbaikan
            $path = $request->file('attachment')->store('maintenance_evidence', 'public');
        }

        DB::transaction(function () use ($request, $id, $path) {
            $ticket = Ticket::findOrFail($id);
            
            $updateData = [
                'status' => $request->status_to,
                'progress_percent' => $request->progress_percent ?? $ticket->progress_percent,
            ];

            if ($request->status_to == 'Selesai') {
                $updateData['foto_sesudah'] = $path;
            }

            $ticket->update($updateData);

            // Catat ke Timeline
            DB::table('maintenance_logs')->insert([
                'ticket_id' => $id,
                'user_id' => Auth::id(),
                'action' => 'Update Progres Lapangan',
                'note' => $request->note,
                'progress_percent' => $request->progress_percent ?? $ticket->progress_percent,
                'photo_evidence' => $path,
                'status_after' => $request->status_to,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });

        return redirect()->back()->with('success', 'Progres berhasil diperbarui.');
    }
}