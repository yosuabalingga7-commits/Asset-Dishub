<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LaporanMasyarakat as Pengaduan; 
use Illuminate\Support\Facades\Auth;
// WAJIB: Pastikan 2 baris ini ada
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class PengaduanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil semua data asli untuk statistik dan dasar pagination
        $allData = Pengaduan::orderBy('created_at', 'desc')->get();

        // 2. Logika Manual Pagination
        $currentPage = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 10; 
        $currentItems = $allData->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        // 3. Bungkus menjadi LengthAwarePaginator agar fungsi ->total() dan ->links() tersedia
        $tickets = new LengthAwarePaginator(
            $currentItems, 
            $allData->count(), 
            $perPage, 
            $currentPage, 
            [
                'path' => $request->url(), 
                'query' => $request->query()
            ]
        );

        return view('admin.pengaduan.index', compact('tickets', 'allData'));
    }

    public function show($id)
    {
        $laporan = Pengaduan::find($id);

        if (!$laporan) {
            return "Gagal: Data dengan ID #{$id} tidak ditemukan di database.";
        }

        if (method_exists($laporan, 'logs')) {
            $laporan->load(['logs.user']);
        }

        return view('admin.pengaduan.detail', compact('laporan'));
    }

    public function validateLaporan(Request $request, $id)
    {
        $request->validate([
            'kepemilikan' => 'required|in:dishub,umum', 
            'catatan_admin' => 'nullable|string'
        ]);

        $laporan = Pengaduan::findOrFail($id);

        $laporan->update([
            'is_validated' => true,
            'kepemilikan' => $request->kepemilikan, 
            'status' => 'Proses Perbaikan', 
            'catatan_admin' => $request->catatan_admin ?? 'Validasi otomatis melalui pemilihan kategori.'
        ]);

        try {
            if (method_exists($laporan, 'logs')) {
                $laporan->logs()->create([
                    'aksi' => 'Laporan Divalidasi',
                    'keterangan' => "Laporan divalidasi sebagai aset: " . strtoupper($request->kepemilikan) . ". Catatan: " . $request->catatan_admin,
                    'user_id' => Auth::id()
                ]);
            }
        } catch (\Exception $e) { }

        $source = strtoupper($request->kepemilikan);

        return redirect()->route('admin.maintenance.create', [
            'report_id' => $laporan->id,
            'source' => $source
        ])->with('success', 'Laporan divalidasi sebagai ' . $source . '. Silakan buat tiket maintenance.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required',
            'catatan_admin' => 'required|string',
        ]);

        $laporan = Pengaduan::findOrFail($id);
        $statusLama = $laporan->status;

        $laporan->update([
            'status' => $request->status,
            'catatan_admin' => $request->catatan_admin,
        ]);

        try {
            if (method_exists($laporan, 'logs')) {
                $aksiLog = ($request->status == 'Ditolak') ? 'Laporan Ditolak' : 'Update Status';
                
                $laporan->logs()->create([
                    'aksi' => $aksiLog,
                    'keterangan' => "Status diubah dari $statusLama menjadi {$request->status}. Catatan: " . $request->catatan_admin,
                    'user_id' => Auth::id()
                ]);
            }
        } catch (\Exception $e) { }
        
        return redirect()->back()->with('success', 'Status laporan berhasil diperbarui.');
    }

    public function selesaikan($id)
    {
        $laporan = Pengaduan::findOrFail($id);
        
        $laporan->update([
            'status' => 'Baik',
            'catatan_admin' => 'Laporan diselesaikan oleh Admin.'
        ]);

        try {
            if (method_exists($laporan, 'logs')) {
                $laporan->logs()->create([
                    'aksi' => 'Laporan Selesai',
                    'keterangan' => 'Admin menandai laporan ini sudah diperbaiki/selesai.',
                    'user_id' => Auth::id()
                ]);
            }
        } catch (\Exception $e) { }

        return redirect()->back()->with('success', 'Laporan berhasil diselesaikan.');
    }
}