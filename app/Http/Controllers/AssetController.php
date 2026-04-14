<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category; // TAMBAHAN: Import Model Category
use App\Models\MaintenanceTicket; // PERBAIKAN: Menggunakan MaintenanceTicket, bukan Ticket
use App\Models\LaporanMasyarakat; 
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
// TAMBAHAN UNTUK EXPORT EXCEL
use App\Exports\AssetExport;
use Maatwebsite\Excel\Facades\Excel;
// TAMBAHAN UNTUK EXPORT PDF
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

    public function index()
    {
        // Jalankan sinkronisasi otomatis
        $this->sinkronisasiDataLama();

        // 1. Ambil semua data aset dengan relasi kategori (PERBAIKAN)
        $assets = Asset::with('category')->get();

        // 2. LOGIKA SIDEBAR KATEGORI
        $sidebar_categories = Asset::select('kategori', DB::raw('count(distinct jenis) as total_jenis'))
            ->groupBy('kategori')
            ->get();

        // 3. LOGIKA DRILL-DOWN JENIS
        $jenis_stats = Asset::select('kategori', 'jenis', DB::raw('count(*) as total_titik'))
            ->groupBy('kategori', 'jenis')
            ->get();

        // 4. Hitung Total Keseluruhan
        $total_aset = $assets->count();

        // 5. Hitung Laporan Masyarakat
        $total_laporan = class_exists(LaporanMasyarakat::class) 
            ? LaporanMasyarakat::whereIn('status', ['masuk', 'pending'])->count() 
            : 0;

        // 6. Hitung Petugas Aktif HARI INI (PERBAIKAN: Menggunakan MaintenanceTicket dan user_id)
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
        
        return view('partials.Dashboard', compact(
            'assets', 
            'mapConfig', 
            'sidebar_categories', 
            'jenis_stats', 
            'total_aset',
            'total_laporan', 
            'petugas_aktif',
            'statuses'
        ));
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

    public function create()
    {
        $pilihan_data = $this->getPilihanData();
        $statuses = ['Baik', 'Proses Perbaikan', 'Rusak', 'Kritis'];
        $categories = Category::all(); // TAMBAHAN: Ambil data kategori untuk dropdown

        $mapConfig = [
            'center' => [-6.8431, 107.4912],
            'defaultZoom' => 13
        ];

        return view('admin.assets.create', compact('mapConfig', 'pilihan_data', 'statuses', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id', // TAMBAHAN: Validasi ID Kategori
            'nama' => 'required|string|max:255',
            'kategori' => 'required',
            'jenis' => 'required',
            'icon_marker' => 'required',
            'status' => 'required|in:Baik,Proses Perbaikan,Rusak,Kritis',
            'alamat' => 'required|string', 
            'lat' => 'required',
            'lng' => 'required',
            'foto' => 'required|file|mimes:jpeg,png,jpg,webp|max:5120', 
            'id_asset' => 'nullable|unique:assets,id_asset' 
        ]);

        try {
            $data = $request->except('foto');

            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $filename = time() . '_' . Str::slug($request->nama) . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('assets', $filename, 'public');
                $data['foto'] = $path;
            }

            if (empty($request->id_asset)) {
                do {
                    $newId = 'AST-' . strtoupper(Str::random(6));
                } while (Asset::where('id_asset', $newId)->exists());
                $data['id_asset'] = $newId;
            }

            Asset::create($data);

            return redirect()->route('assets.index')->with('success', 'Aset ' . $request->nama . ' berhasil didaftarkan!');

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
        $categories = Category::all(); // TAMBAHAN: Ambil data kategori

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
            'category_id' => 'nullable|exists:categories,id', // TAMBAHAN: Validasi ID Kategori
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

            return redirect()->route('assets.index')->with('success', 'Data aset ' . $asset->nama . ' berhasil diperbarui!');
            
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
            return redirect()->route('assets.index')->with('success', 'Aset berhasil dihapus.');
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
}