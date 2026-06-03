<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\MaintenanceTicket;
use App\Models\LaporanMasyarakat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Exports\AssetExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetController extends Controller
{
    /**
     * Fungsi Tambahan: Memastikan data lama otomatis sinkron dengan format baru
     */
    private function sinkronisasiDataLama()
    {
        // 1. Perbaiki kategori Pengendalian (Konsistensi dengan &)
        DB::table('assets')->where('kategori', 'LIKE', '%dan%')->update([
            'kategori' => 'Pengendalian & Pengawasan'
        ]);

        // 2. Perbaiki kategori Prasarana
        DB::table('assets')->where('kategori', 'Prasarana Pelayanan Transportasi')->update([
            'kategori' => 'Prasarana Transportasi'
        ]);

        // 3. Normalisasi spasi
        DB::statement("UPDATE assets SET kategori = TRIM(kategori), jenis = TRIM(jenis)");
    }

    public function index(Request $request)
    {
        // Jalankan sinkronisasi otomatis
        $this->sinkronisasiDataLama();

        // 1. Ambil data aset (Siapkan query untuk filter jika diperlukan di masa depan via request)
        $query = Asset::with('category');

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori', $request->kategori);
        }

        $assets = $query->get();

        // 2. LOGIKA SIDEBAR KATEGORI (Menghitung jumlah aset per kategori untuk statistik sidebar)
        $sidebar_categories = Asset::select('kategori', DB::raw('count(*) as total_aset'), DB::raw('count(distinct jenis) as total_jenis'))
            ->groupBy('kategori')
            ->get();

        // 3. LOGIKA DRILL-DOWN JENIS
        $jenis_stats = Asset::select('kategori', 'jenis', DB::raw('count(*) as total_titik'))
            ->groupBy('kategori', 'jenis')
            ->get();

        // 4. LOGIKA STATISTIK KONDISI (Untuk Chart & Sidebar List)
        // Memastikan semua status (Baik, Proses, Rusak, Kritis) terhitung meskipun 0
        $status_counts = Asset::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();

        $default_statuses = ['Baik' => 0, 'Proses Perbaikan' => 0, 'Rusak' => 0, 'Kritis' => 0];
        $final_status_stats = array_merge($default_statuses, $status_counts);

        // 5. Hitung Total Keseluruhan & Laporan
        $total_aset = Asset::count();
        $total_laporan = class_exists(LaporanMasyarakat::class) 
            ? LaporanMasyarakat::whereIn('status', ['masuk', 'pending'])->count() 
            : 0;

        // 6. Hitung Petugas Aktif HARI INI
        $petugas_aktif = class_exists(MaintenanceTicket::class)
            ? MaintenanceTicket::whereDate('created_at', date('Y-m-d'))
                ->distinct('user_id')
                ->count('user_id')
            : 0;
        
        $mapConfig = [
            'center' => [-6.8431, 107.4912],
            'defaultZoom' => 12,
            'geojsonPath' => asset('shp/administrasi_desa.json')
        ];

        $statuses = ['Baik', 'Proses Perbaikan', 'Rusak', 'Kritis'];
        
        // Sesuaikan view target ke admin.assets.Dashboard sesuai struktur folder Anda
        return view('admin.assets.Dashboard', compact(
            'assets', 
            'mapConfig', 
            'sidebar_categories', 
            'jenis_stats', 
            'total_aset',
            'total_laporan', 
            'petugas_aktif',
            'statuses',
            'final_status_stats'
        ));
    }

    /**
     * Menampilkan detail aset tertentu
     */
    public function show($id)
    {
        // Cari aset berdasarkan ID, jika tidak ada akan otomatis 404
        $asset = Asset::with('creator')->findOrFail($id);
        
        // Sinkronisasi data lama jika diperlukan
        $this->sinkronisasiDataLama();
        
        // Konfigurasi peta untuk halaman detail
        $mapConfig = [
            'center' => [$asset->lat, $asset->lng],
            'defaultZoom' => 17
        ];
        
        return view('admin.assets.show', compact('asset', 'mapConfig'));
    }

    private function getPilihanData()
    {
        return [
            'Penerangan Jalan Umum (PJU)' => ['Tiang PJU Galvanis', 'Tiang PJU Dekoratif', 'Lampu LED', 'Lampu Solar Cell', 'Panel Box PJU'],
            'Perlengkapan Jalan' => ['Rambu Parkir Motor', 'Rambu Parkir Mobil', 'Rambu Larangan', 'Rambu Peringatan', 'Rambu Petunjuk', 'Rambu Perintah', 'Cermin Tikungan', 'Guardrail'],
            'Fasilitas Lalu Lintas' => ['APILL (Traffic Light)', 'Warning Light', 'Marka Zebra Cross', 'Marka RHK', 'Paku Jalan', 'Water Barrier'],
            'Pengendalian & Pengawasan' => ['CCTV Surveilans', 'CCTV E-TLE', 'VMS (Papan Digital)', 'ATCS Controller', 'CCTV Survey'],
            'Prasarana Transportasi' => ['Halte Bus', 'Terminal', 'Gedung PKB', 'Jembatan Penyeberangan (JPO)'],
        ];
    }

    public function create(Request $request)
    {
        $pilihan_data = $this->getPilihanData();
        $statuses = ['Baik', 'Proses Perbaikan', 'Rusak', 'Kritis'];
        $categories = Category::all();

        $mapConfig = [
            'center' => [-6.8431, 107.4912],
            'defaultZoom' => 13
        ];

        // Ambil parameter from untuk menentukan redirect
        $from = $request->query('from', 'map'); // default ke map

        return view('admin.assets.create', compact('mapConfig', 'pilihan_data', 'statuses', 'categories', 'from'));
    }

    /**
     * Fungsi store dengan pembuatan ID Aset otomatis
     * Format ID: [KodeKategori][KodeJenis]-[Tahun]-[NomorUrut]
     * Contoh: PTT-2026-001 (PT=Prasarana Transportasi, T=Terminal, 2026=tahun, 001=nomor urut)
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'nama' => 'required|string|max:255',
            'kategori' => 'required', // ini berisi kode kategori (PJ, PL, FL, PP, PT)
            'kategori_nama' => 'required|string', // nama lengkap kategori
            'jenis' => 'required', // ini berisi nama jenis
            'jenis_kode' => 'required|string|size:1', // kode jenis 1 karakter (huruf pertama kata pertama)
            'icon_marker' => 'required',
            'status' => 'required|in:Baik,Proses Perbaikan,Rusak,Kritis',
            'alamat' => 'required|string', 
            'lat' => 'required',
            'lng' => 'required',
            'foto' => 'required|file|mimes:jpeg,png,jpg,webp|max:5120', 
        ]);

        try {
            // ========== PEMBUATAN ID ASET OTOMATIS ==========
            // 1. Tangkap kode kategori dan kode jenis
            $kodeKategori = $request->input('kategori'); // PJ, PL, FL, PP, PT
            $kodeJenis = $request->input('jenis_kode'); // Huruf pertama dari kata pertama jenis (contoh: Terminal -> T)
            
            // 2. Ambil tahun saat ini
            $tahunSekarang = date('Y');
            
            // 3. Gabungkan prefix awal
            $prefix = $kodeKategori . $kodeJenis . '-' . $tahunSekarang . '-';
            // Contoh: PTT-2026- atau PLR-2026-
            
            // 4. Cari nomor urut terbesar dengan prefix yang sama
            $lastAsset = Asset::where('id_asset', 'LIKE', $prefix . '%')
                ->orderBy('id_asset', 'desc')
                ->first();
            
            if ($lastAsset) {
                // Ekstrak 3 digit terakhir dari ID
                $lastNumber = (int) substr($lastAsset->id_asset, -3);
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }
            
            // Format nomor urut menjadi 3 digit (001, 002, dst)
            $nomorUrut = str_pad($newNumber, 3, '0', STR_PAD_LEFT);
            
            // 5. Buat ID aset lengkap
            $idAset = $prefix . $nomorUrut;
            // ========== END PEMBUATAN ID ASET ==========
            
            // Siapkan data untuk disimpan
            $data = $request->except('foto', 'kategori_nama', 'jenis_kode');
            
            // Gunakan kategori_nama sebagai nilai kategori
            $data['kategori'] = $request->input('kategori_nama');
            
            // Gunakan ID aset yang sudah dibuat
            $data['id_asset'] = $idAset;

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = time() . '_' . Str::slug($request->nama) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('assets', $filename, 'public');
                $data['foto'] = $path;
            }

            Asset::create($data);

            // Redirect berdasarkan parameter from
            $from = $request->input('from', 'map');
            if ($from == 'list') {
                return redirect()->route('assets.list')->with('success', 'Aset ' . $request->nama . ' berhasil didaftarkan! (ID: ' . $idAset . ')');
            } else {
                return redirect()->route('assets.index')->with('success', 'Aset ' . $request->nama . ' berhasil didaftarkan! (ID: ' . $idAset . ')');
            }

        } catch (\Exception $e) {
            \Log::error('Error Simpan Aset: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal Simpan Data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $asset = Asset::findOrFail($id);
        $pilihan_data = $this->getPilihanData();
        $statuses = ['Baik', 'Proses Perbaikan', 'Rusak', 'Kritis'];
        $categories = Category::all();

        $mapConfig = [
            'center' => [$asset->lat, $asset->lng],
            'defaultZoom' => 15
        ];

        return view('admin.assets.edit', compact('asset', 'mapConfig', 'pilihan_data', 'statuses', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);

        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'nama' => 'required|string|max:255',
            'kategori' => 'required',
            'jenis' => 'required',
            'status' => 'required|in:Baik,Proses Perbaikan,Rusak,Kritis',
            'alamat' => 'required|string', 
            'lat' => 'required',
            'lng' => 'required',
            'icon_marker' => 'required',
            'foto' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:5120', 
        ]);

        try {
            $data = $request->except('foto');

            if ($request->hasFile('foto')) {
                if ($asset->foto && Storage::disk('public')->exists($asset->foto)) {
                    Storage::disk('public')->delete($asset->foto);
                }
                
                $file = $request->file('foto');
                $filename = time() . '_' . Str::slug($request->nama) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('assets', $filename, 'public');
                $data['foto'] = $path;
            }

            $asset->update($data);
            Artisan::call('view:clear');

            return redirect()->route('assets.list')->with('success', 'Data aset ' . $asset->nama . ' berhasil diperbarui!');
            
        } catch (\Exception $e) {
            \Log::error('Error Update Aset: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $asset = Asset::findOrFail($id);
        try {
            if ($asset->foto && Storage::disk('public')->exists($asset->foto)) {
                Storage::disk('public')->delete($asset->foto);
            }
            
            $asset->delete();
            return redirect()->route('assets.list')->with('success', 'Aset berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function exportExcel()
    {
        return Excel::download(new AssetExport, 'daftar-aset-dishub-kbb.xlsx');
    }

    public function exportPdf()
    {
        $assets = Asset::all();
        $pdf = Pdf::loadView('admin.assets.pdf', compact('assets'))->setPaper('a4', 'landscape');
        return $pdf->download('laporan-aset-dishub-kbb.pdf');
    }

    /**
     * Menampilkan daftar aset dalam bentuk tabel (list view)
     * Digunakan untuk halaman manajemen aset yang lebih terstruktur
     */
    public function list(Request $request)
    {
        // Jalankan sinkronisasi otomatis
        $this->sinkronisasiDataLama();

        // Query dasar dengan relasi category
        $query = Asset::with('category');

        // Filter berdasarkan status
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan kategori
        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori', $request->kategori);
        }

        // Filter berdasarkan jenis
        if ($request->has('jenis') && $request->jenis != '') {
            $query->where('jenis', $request->jenis);
        }

        // Pencarian berdasarkan nama, id_asset, atau alamat (SATU KOLOM)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('id_asset', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        // Urutkan berdasarkan created_at terbaru, dengan pagination 15 data per halaman
        $assets = $query->orderBy('created_at', 'desc')->paginate(15);

        // Data untuk filter dropdown
        $statuses = Asset::distinct()->pluck('status')->filter();
        $categories = Asset::distinct()->pluck('kategori')->filter();
        $jenis_list = Asset::distinct()->pluck('jenis')->filter();

        return view('admin.assets.daftar_aset', compact('assets', 'statuses', 'categories', 'jenis_list'));
    }

    // ============================================
    // API METHODS UNTUK DASHBOARD
    // ============================================

    /**
     * API: Mendapatkan 5 aset dengan status Kritis terbaru untuk prioritas perbaikan
     */
    public function getPriorityAssets()
    {
        $assets = Asset::where('status', 'Kritis')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get(['id', 'id_asset', 'nama', 'kategori', 'alamat']);
        
        return response()->json(['assets' => $assets]);
    }
}