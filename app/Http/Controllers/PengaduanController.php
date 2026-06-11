<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report as Pengaduan; 
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PengaduanController extends Controller
{
    /**
     * Menampilkan halaman form pengaduan masyarakat
     */
    public function create()
    {
        return view('masyarakat.lapor');
    }

    /**
     * Menyimpan laporan pengaduan masyarakat dan mengupdate status aset secara real-time
     */
    public function store(Request $request)
    {
        // Validasi input termasuk id_asset harus ada di tabel assets
        $request->validate([
            'id_asset' => 'required|exists:assets,id_asset',
            'nama_pelapor' => 'required|string|max:255',
            'kontak_pelapor' => 'required|string|max:20',
            'judul_laporan' => 'required|string|max:255',
            'kondisi_aset' => 'required|in:rusak,hilang,pindah,fast,others',
            'foto' => 'required|file|mimes:jpeg,png,jpg,webp|max:5120',
            'lat' => 'nullable',
            'lng' => 'nullable',
            'alamat' => 'nullable|string',
            'lokasi_koordinat' => 'nullable|string',
            'status' => 'nullable|string'
        ]);

        try {
            // ========== 1. SIMPAN DATA LAPORAN PENGADUAN ==========
            $data = $request->except('foto');
            
            // Proses upload foto
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = time() . '_' . Str::slug($request->nama_pelapor) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('pengaduan', $filename, 'public');
                $data['foto'] = $path;
            }
            
            // Set default status jika tidak ada
            if (empty($data['status'])) {
                $data['status'] = 'masuk';
            }
            
            $data['source'] = 'masyarakat';

            // Simpan ke database
            $pengaduan = Pengaduan::create($data);
            
            // ========== 2. LOGIKA SINKRONISASI STATUS ASET (REAL-TIME) ==========
            $idAset = $request->input('id_asset');
            $kondisi = $request->input('kondisi_aset');
            
            // Cari aset berdasarkan ID
            $aset = Asset::where('id_asset', $idAset)->first();
            
            if ($aset) {
                if (in_array($kondisi, ['hilang', 'lainnya'])) {
                    $statusBaru = 'Kritis';
                } elseif (in_array($kondisi, ['rusak', 'pindah'])) {
                    $statusBaru = 'Rusak';
                } else {
                    $statusBaru = 'Rusak'; // default fallback
                }
                
                // Update status aset
                $aset->update([
                    'status' => $statusBaru
                ]);
                
                \Log::info('Status aset diperbarui', [
                    'id_asset' => $idAset,
                    'kondisi_laporan' => $kondisi,
                    'status_baru' => $statusBaru,
                    'id_pengaduan' => $pengaduan->id
                ]);
            } else {
                \Log::warning('Aset tidak ditemukan saat update status', [
                    'id_asset' => $idAset,
                    'id_pengaduan' => $pengaduan->id
                ]);
            }
            
            // ========== 3. REDIRECT DENGAN PESAN SUKSES ==========
            return redirect()->route('lapor.create')->with('success', 'Terima kasih! Laporan Anda telah kami terima. Status aset akan segera kami perbarui.');
            
        } catch (\Exception $e) {
            \Log::error('Error Simpan Pengaduan: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal mengirim laporan: ' . $e->getMessage());
        }
    }

    /**
     * Halaman daftar laporan masyarakat
     */
    public function index(Request $request)
    {
        // Ambil data laporan masyarakat (filter by source 'masyarakat')
        $laporanMasyarakat = Pengaduan::where('source', 'masyarakat')->orderBy('created_at', 'desc')->paginate(10);
        
        // Ambil data laporan petugas (filter by source 'petugas')
        $laporanPetugas = Pengaduan::where('source', 'petugas')->orderBy('created_at', 'desc')->paginate(10);
        
        // Untuk statistik cards
        $allDataMasyarakat = Pengaduan::where('source', 'masyarakat')->get();

        return view('admin.pengaduan.index', compact('laporanMasyarakat', 'laporanPetugas', 'allDataMasyarakat'));
    }

    /**
     * Halaman daftar laporan petugas
     */
    public function indexPetugas(Request $request)
    {
        // Ambil data laporan petugas (filter by source 'petugas')
        $laporanPetugas = Pengaduan::where('source', 'petugas')->orderBy('created_at', 'desc')->paginate(10);
        
        return view('admin.pengaduan.petugas', compact('laporanPetugas'));
    }

    public function show($id)
    {
        // UPDATE: Tambahkan eager loading 'tiket' untuk mengecek apakah tiket sudah dibuat
        $laporan = Pengaduan::with(['tiket'])->find($id);

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

        // ========== UPDATE STATUS ASET MENJADI PROSES PERBAIKAN ==========
        $idAset = $laporan->id_asset;
        if ($idAset) {
            $aset = Asset::where('id_asset', $idAset)->first();
            if ($aset) {
                $aset->update([
                    'status' => 'Proses Perbaikan'
                ]);
                \Log::info('Status aset diperbarui menjadi Proses Perbaikan', [
                    'id_asset' => $idAset,
                    'id_pengaduan' => $laporan->id,
                    'kepemilikan' => $request->kepemilikan
                ]);
            }
        }
        // ========== AKHIR UPDATE STATUS ASET ==========

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
                    'keterangan' => "Laporan divalidasi sebagai aset: " . strtoupper($request->kepemilikan) . ". Status aset diubah menjadi Proses Perbaikan. Catatan: " . $request->catatan_admin,
                    'user_id' => Auth::id()
                ]);
            }
        } catch (\Exception $e) { }

        $source = strtoupper($request->kepemilikan);

        return redirect()->route('admin.maintenance.create', [
            'report_id' => $laporan->id,
            'source' => $source
        ])->with('success', 'Laporan divalidasi sebagai ' . $source . '. Status aset telah diubah menjadi Proses Perbaikan. Silakan buat tiket maintenance.');
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

        // ========== UPDATE STATUS ASET MENJADI BAIK ==========
        $idAset = $laporan->id_asset;
        if ($idAset) {
            $aset = Asset::where('id_asset', $idAset)->first();
            if ($aset) {
                $aset->update([
                    'status' => 'Baik'
                ]);
                \Log::info('Status aset diperbarui menjadi Baik', [
                    'id_asset' => $idAset,
                    'id_pengaduan' => $laporan->id
                ]);
            }
        }
        // ========== AKHIR UPDATE STATUS ASET ==========

        try {
            if (method_exists($laporan, 'logs')) {
                $laporan->logs()->create([
                    'aksi' => 'Laporan Selesai',
                    'keterangan' => 'Admin menandai laporan ini sudah diperbaiki/selesai. Status aset diubah menjadi Baik.',
                    'user_id' => Auth::id()
                ]);
            }
        } catch (\Exception $e) { }

        return redirect()->back()->with('success', 'Laporan berhasil diselesaikan. Status aset telah diubah menjadi Baik.');
    }

    // ============================================
    // API METHODS UNTUK DASHBOARD DINAMIS
    // ============================================

    /**
     * API: Mendapatkan 10 laporan terbaru (gabungan masyarakat & petugas)
     */
    public function getRecentReports()
    {
        $laporanMasyarakat = Pengaduan::where('source', 'masyarakat')->latest()->take(10)->get()->map(function($item) {
            return [
                'id' => $item->id,
                'judul_laporan' => $item->judul_laporan,
                'nama_pelapor' => $item->nama_pelapor,
                'status' => $item->status,
                'sumber' => 'masyarakat',
                'tanggal' => $item->created_at->diffForHumans()
            ];
        });
        
        $laporanPetugas = Pengaduan::where('source', 'petugas')->latest()->take(10)->get()->map(function($item) {
            return [
                'id' => $item->id,
                'judul_laporan' => $item->judul_laporan,
                'nama_pelapor' => $item->nama_petugas,
                'status' => $item->status,
                'sumber' => 'petugas',
                'tanggal' => $item->created_at->diffForHumans()
            ];
        });
        
        $reports = $laporanMasyarakat->concat($laporanPetugas)->sortByDesc(function($item) {
            return $item['tanggal'];
        })->take(10)->values();
        
        return response()->json(['reports' => $reports]);
    }

    /**
     * API: Mendapatkan detail jumlah laporan masyarakat & petugas
     */
    public function getLaporanDetails()
    {
        $laporanMasyarakat = Pengaduan::where('source', 'masyarakat')->count();
        $laporanPetugas = Pengaduan::where('source', 'petugas')->count();
        
        return response()->json([
            'laporan_masyarakat' => $laporanMasyarakat,
            'laporan_petugas' => $laporanPetugas
        ]);
    }

    /**
     * API: Mendapatkan tren laporan 7 hari terakhir untuk line chart
     */
    public function getReportTrend()
    {
        $labels = [];
        $counts = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->locale('id')->isoFormat('dddd');
            
            $count = Pengaduan::where('source', 'masyarakat')->whereDate('created_at', $date->toDateString())->count();
            $count += Pengaduan::where('source', 'petugas')->whereDate('created_at', $date->toDateString())->count();
            $counts[] = $count;
        }
        
        return response()->json(['labels' => $labels, 'counts' => $counts]);
    }
}