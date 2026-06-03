<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengumumanController extends Controller
{
    /**
     * Constructor - atur middleware (di Laravel 12, middleware diatur di routes)
     */
    public function __construct()
    {
        // Middleware sudah diatur di routes/web.php
        // Tidak perlu $this->middleware('auth') di sini
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Filter parameter
        $filterStatus = $request->get('status', '');
        $filterTarget = $request->get('target', '');
        $filterJenis = $request->get('jenis', '');
        
        // Query pengumuman
        $query = Pengumuman::with('pembuat');
        
        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }
        
        if ($filterTarget) {
            $query->where('target', $filterTarget);
        }
        
        if ($filterJenis) {
            $query->where('jenis', $filterJenis);
        }
        
        // Urutkan: penting dulu, lalu terbaru
        $pengumuman = $query->orderBy('penting', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(10)
                            ->withQueryString();
        
        // Data untuk filter dropdown
        $statuses = ['aktif', 'arsip'];
        $targets = ['semua', 'petugas_lapangan', 'kepala_seksi', 'admin'];
        $jenisList = [
            'perubahan_layanan' => 'Perubahan Layanan',
            'info_operasional' => 'Info Operasional',
            'kebijakan_baru' => 'Kebijakan Baru',
            'instruksi_petugas' => 'Instruksi Petugas',
            'info_proyek' => 'Info Proyek',
            'surat_edaran' => 'Surat Edaran'
        ];
        
        return view('admin.Kadis.pengumuman', compact('pengumuman', 'statuses', 'targets', 'jenisList', 'filterStatus', 'filterTarget', 'filterJenis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Cek role (sesuaikan dengan role di sistem Anda)
        if (!in_array(Auth::user()->role, ['kadis', 'admin'])) {
            abort(403, 'Anda tidak memiliki izin untuk membuat pengumuman.');
        }
        
        $jenisList = [
            'perubahan_layanan' => '📋 Perubahan Layanan',
            'info_operasional' => '🚌 Info Operasional',
            'kebijakan_baru' => '📜 Kebijakan Baru',
            'instruksi_petugas' => '⚠️ Instruksi Petugas',
            'info_proyek' => '🏗️ Info Proyek',
            'surat_edaran' => '📧 Surat Edaran'
        ];
        
        $targets = [
            'semua' => '👥 Semua (Kepala Dinas, Admin, Kepala Seksi, Petugas)',
            'petugas_lapangan' => '👮 Petugas Lapangan',
            'kepala_seksi' => '👔 Kepala Seksi',
            'admin' => '🖥️ Admin'
        ];
        
        return view('admin.Kadis.pengumuman_create', compact('jenisList', 'targets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Cek role
        if (!in_array(Auth::user()->role, ['kadis', 'admin'])) {
            abort(403, 'Anda tidak memiliki izin untuk membuat pengumuman.');
        }
        
        // Validasi input
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'status' => 'required|in:aktif,arsip',
            'target' => 'required|in:semua,petugas_lapangan,kepala_seksi,admin',
            'penting' => 'sometimes|boolean',
            'jenis' => 'required|in:perubahan_layanan,info_operasional,kebijakan_baru,instruksi_petugas,info_proyek,surat_edaran',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
        ]);
        
        // Handle upload lampiran
        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('lampiran_pengumuman', 'public');
        }
        
        // Simpan ke database
        Pengumuman::create([
            'judul' => $request->judul,
            'isi' => $request->isi,
            'status' => $request->status,
            'target' => $request->target,
            'penting' => $request->has('penting') ? true : false,
            'jenis' => $request->jenis,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'lampiran' => $lampiranPath,
            'created_by' => Auth::id()
        ]);
        
        return redirect()->route('kadis.pengumuman.index')
                         ->with('success', 'Pengumuman berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pengumuman = Pengumuman::with('pembuat')->findOrFail($id);
        return view('admin.Kadis.pengumuman_show', compact('pengumuman'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Cek role
        if (!in_array(Auth::user()->role, ['kadis', 'admin'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit pengumuman.');
        }
        
        $pengumuman = Pengumuman::findOrFail($id);
        
        $jenisList = [
            'perubahan_layanan' => '📋 Perubahan Layanan',
            'info_operasional' => '🚌 Info Operasional',
            'kebijakan_baru' => '📜 Kebijakan Baru',
            'instruksi_petugas' => '⚠️ Instruksi Petugas',
            'info_proyek' => '🏗️ Info Proyek',
            'surat_edaran' => '📧 Surat Edaran'
        ];
        
        $targets = [
            'semua' => '👥 Semua (Kepala Dinas, Admin, Kepala Seksi, Petugas)',
            'petugas_lapangan' => '👮 Petugas Lapangan',
            'kepala_seksi' => '👔 Kepala Seksi',
            'admin' => '🖥️ Admin'
        ];
        
        return view('admin.Kadis.pengumuman_edit', compact('pengumuman', 'jenisList', 'targets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Cek role
        if (!in_array(Auth::user()->role, ['kadis', 'admin'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit pengumuman.');
        }
        
        $pengumuman = Pengumuman::findOrFail($id);
        
        // Validasi input
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'status' => 'required|in:aktif,arsip',
            'target' => 'required|in:semua,petugas_lapangan,kepala_seksi,admin',
            'penting' => 'sometimes|boolean',
            'jenis' => 'required|in:perubahan_layanan,info_operasional,kebijakan_baru,instruksi_petugas,info_proyek,surat_edaran',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx|max:2048'
        ]);
        
        // Handle upload lampiran baru
        if ($request->hasFile('lampiran')) {
            // Hapus lampiran lama jika ada
            if ($pengumuman->lampiran && \Storage::disk('public')->exists($pengumuman->lampiran)) {
                \Storage::disk('public')->delete($pengumuman->lampiran);
            }
            $lampiranPath = $request->file('lampiran')->store('lampiran_pengumuman', 'public');
            $pengumuman->lampiran = $lampiranPath;
        }
        
        // Update data
        $pengumuman->update([
            'judul' => $request->judul,
            'isi' => $request->isi,
            'status' => $request->status,
            'target' => $request->target,
            'penting' => $request->has('penting') ? true : false,
            'jenis' => $request->jenis,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
        ]);
        
        return redirect()->route('kadis.pengumuman.index')
                         ->with('success', 'Pengumuman berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Cek role (hanya Kepala Dinas yang bisa hapus)
        if (Auth::user()->role != 'kadis') {
            abort(403, 'Anda tidak memiliki izin untuk menghapus pengumuman.');
        }
        
        $pengumuman = Pengumuman::findOrFail($id);
        
        // Hapus lampiran jika ada
        if ($pengumuman->lampiran && \Storage::disk('public')->exists($pengumuman->lampiran)) {
            \Storage::disk('public')->delete($pengumuman->lampiran);
        }
        
        $pengumuman->delete();
        
        return redirect()->route('kadis.pengumuman.index')
                         ->with('success', 'Pengumuman berhasil dihapus!');
    }
}