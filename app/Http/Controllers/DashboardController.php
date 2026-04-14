<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Asset; 
use App\Models\Category; 
use Illuminate\Support\Facades\Schema; 

class DashboardController extends Controller
{
    /**
     * Halaman Dashboard Utama
     */
    public function index()
    {
        // 1. Ambil Setting Peta (Cek apakah tabel ada)
        $setting = Schema::hasTable('map_settings') ? DB::table('map_settings')->first() : null;

        /**
         * 2. Ambil SEMUA Aset untuk ditampilkan di Map
         * UPDATE FOKUS: Menggunakan with('category') agar data detail dari relasi
         * kategori (seperti emoji/warna) ikut terbawa ke popup di atas emoji.
         */
        $assets = Asset::with('category')->get();

        // 3. Ambil Kategori untuk Sidebar (Sesuai kebutuhan di Blade)
        $sidebar_categories = DB::table('assets')
            ->select('kategori', DB::raw('count(distinct jenis) as total_jenis'))
            ->groupBy('kategori')
            ->get();

        // 4. Ambil Statistik Jenis Aset
        $jenis_stats = DB::table('assets')
            ->select('kategori', 'jenis', DB::raw('count(*) as total_titik'))
            ->groupBy('kategori', 'jenis')
            ->get();

        // 5. Data Tambahan (Laporan & Petugas)
        $total_laporan = Schema::hasTable('laporan_masyarakats') 
            ? DB::table('laporan_masyarakats')->count() 
            : 0;
            
        $petugas_aktif = Schema::hasTable('users') 
            ? DB::table('users')->where('role', 'petugas')->count() 
            : 0;

        // 6. Konfigurasi Map
        $mapConfig = [
            'center'      => [
                $setting->latitude ?? -6.8431, 
                $setting->longitude ?? 107.4912
            ], 
            'defaultZoom' => $setting->zoom ?? 15, 
            'detailZoom'  => 19,
        ];

        // 7. Siapkan variabel yang dipanggil di Blade Dashboard
        $total_aset = $assets->count();

        // Return view dengan semua variabel yang dibutuhkan Blade
        return view('partials.Dashboard', compact(
            'mapConfig', 
            'assets', 
            'sidebar_categories', 
            'jenis_stats', 
            'total_aset', 
            'total_laporan', 
            'petugas_aktif'
        ));
    }
}