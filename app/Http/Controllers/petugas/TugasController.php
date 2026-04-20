<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceTicket; // Diubah agar sesuai dengan migrasi maintenance_tickets
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TugasController extends Controller
{
    /**
     * Menampilkan daftar tugas yang tersedia (Belum selesai)
     * Status: pending, process (Sesuai dengan default migrasi)
     */
    public function tersedia()
    {
        $user = Auth::user();
        
        // Mengambil tiket yang ditujukan ke seksi user login (berdasarkan seksi_id)
        $tasks = MaintenanceTicket::where('seksi_id', $user->id)
            ->whereIn('status', ['pending', 'process'])
            ->latest()
            ->get();

        return view('admin.petugas.tugas-tersedia', compact('tasks'));
    }

    /**
     * Menampilkan daftar tugas yang sudah selesai
     */
    public function selesai()
    {
        $user = Auth::user();
        
        // Mengambil tiket yang statusnya sudah 'finished'
        $tasks = MaintenanceTicket::where('seksi_id', $user->id)
            ->where('status', 'finished')
            ->latest()
            ->get();

        return view('admin.petugas.tugas-selesai', compact('tasks'));
    }

    /**
     * Update status tugas menjadi Selesai dengan Upload Foto
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'foto_perbaikan' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'completion_notes' => 'required'
        ]);

        $task = MaintenanceTicket::findOrFail($id);
        
        $data = [
            'status' => 'finished',
            'completion_notes' => $request->completion_notes,
            'finished_at' => now(),
        ];

        // Logika upload foto jika ada
        if ($request->hasFile('foto_perbaikan')) {
            $path = $request->file('foto_perbaikan')->store('perbaikan', 'public');
            $data['foto_perbaikan'] = $path;
        }

        $task->update($data);

        return back()->with('success', 'Laporan perbaikan berhasil dikirim!');
    }
}