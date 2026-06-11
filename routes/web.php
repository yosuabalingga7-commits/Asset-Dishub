<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManajemenController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\PenugasanController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AsetApiController;
use App\Http\Controllers\LaporanPetugasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Petugas\TugasController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\EksekutifController;
use App\Http\Controllers\PengumumanController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes - Dishub KBB (LINTAS)
|--------------------------------------------------------------------------
*/

// --- HALAMAN DEPAN LINTAS (GUEST/UMUM) ---
Route::get('/', [LaporanController::class, 'indexLanding'])->name('landing');
Route::get('/lapor-kerusakan', [LaporanController::class, 'createPublic'])->name('lapor.public');
Route::post('/lapor-kerusakan/simpan', [LaporanController::class, 'storePublic'])->name('lapor.store_public');

// --- HALAMAN LOGIN & LOGOUT ---
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- HALAMAN LAMA / LEGACY (OPSIONAL) ---
Route::get('/lapor', [LaporanController::class, 'index'])->name('lapor.index');
Route::post('/lapor/store', [LaporanController::class, 'store'])->name('lapor.store');

// --- API & MAP VIEW ---
Route::get('/map-view', [DashboardController::class, 'index'])->name('dashboard');

/**
 * API UTAMA SPASIAL (PostGIS Integrated)
 * Mendukung pemfilteran dinamis berbasis batas wilayah pandang (Bounding Box/BBOX)
 * Parameter opsional (via Query String): minLat, minLng, maxLat, maxLng, zoom
 */
Route::get('/api/asets-map', [AsetApiController::class, 'getAllAssets'])->name('api.asets.map');

// --- API untuk mendapatkan nomor urut aset berikutnya (digunakan oleh AJAX di form create aset) ---
Route::get('/api/next-asset-number', function (Request $request) {
    $prefix = $request->query('prefix');

    if (!$prefix) {
        return response()->json(['nextNumber' => '001']);
    }

    // Cari aset dengan ID terbesar yang memiliki prefix yang sama
    $lastAsset = Asset::where('id_asset', 'LIKE', $prefix . '%')
        ->orderBy('id_asset', 'desc')
        ->first();

    if ($lastAsset) {
        // Ambil 3 digit terakhir dari ID, ubah ke integer, lalu +1
        $lastNumber = (int) substr($lastAsset->id_asset, -3);
        $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $nextNumber = '001';
    }

    return response()->json(['nextNumber' => $nextNumber]);
})->name('api.next.asset.number');

// --- API untuk mencari aset terdekat (digunakan oleh form laporan masyarakat) ---
Route::get('/laporan/aset-terdekat', [LaporanController::class, 'nearestAssets'])->name('laporan.aset-terdekat');

// --- API untuk mencari aset terdekat (digunakan oleh form laporan petugas) ---
Route::get('/laporan/aset-terdekat-petugas', [LaporanPetugasController::class, 'nearestAssets'])->name('laporan.aset-terdekat-petugas');

// ============================================
// API ROUTES UNTUK DASHBOARD DINAMIS
// ============================================
Route::get('/api/recent-reports', [PengaduanController::class, 'getRecentReports'])->name('api.recent-reports');
Route::get('/api/total-pegawai', [UserManagementController::class, 'getTotalPegawai'])->name('api.total-pegawai');
Route::get('/api/laporan-details', [PengaduanController::class, 'getLaporanDetails'])->name('api.laporan-details');
Route::get('/api/priority-assets', [AssetController::class, 'getPriorityAssets'])->name('api.priority-assets');
Route::get('/api/report-trend', [PengaduanController::class, 'getReportTrend'])->name('api.report-trend');

// --- AREA TERPROTEKSI (WAJIB LOGIN) ---
Route::middleware(['auth'])->group(function () {

    // Redireksi Dinamis untuk URL /dashboard umum
    Route::get('/dashboard', function () {
        if (Auth::user()->role === 'kadis') return redirect()->route('kadis.dashboard');
        if (Auth::user()->role === 'seksi') return redirect()->route('petugas.tersedia');
        if (Auth::user()->role === 'petugas_lapangan') return redirect()->route('petugas.lapangan.dashboard');
        return redirect()->route('admin.dashboard');
    });

    // --- SISTEM NOTIFIKASI (Global untuk semua user login) ---
    // Diperbaiki menggunakan deklarasi anotasi PHPDoc untuk mengeliminasi peringatan Intelephense
    Route::get('/notifications/{id}/read', function ($id) {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user) {
            $notification = $user->notifications()->findOrFail($id);
            $notification->markAsRead();
            return redirect($notification->data['url'] ?? route('dashboard'));
        }

        return redirect()->route('login');
    })->name('notifications.read');

    // Route untuk Pengaturan Profil (Bisa diakses Admin & Petugas)
    Route::get('/admin/settings', [ProfileController::class, 'index'])->name('settings.index');
    Route::put('/admin/settings/update', [ProfileController::class, 'update'])->name('settings.update');
    Route::post('/admin/settings/update-foto', [ProfileController::class, 'updateFoto'])->name('settings.updateFoto');

    // Route Detail Maintenance (Akses Admin & Seksi)
    Route::get('/admin/maintenance/{id}/detail', [MaintenanceController::class, 'show'])
        ->middleware('role:admin,seksi')
        ->name('admin.maintenance.show');

    // --- FITUR BERSAMA: ADMIN & KADIS ---
    Route::middleware('role:admin,kadis')->group(function () {
        // GIS Monitoring
        Route::get('/admin/gis', [ManajemenController::class, 'gis'])->name('gis.index');

        // List Aset & Export
        Route::get('/admin/assets/list', [AssetController::class, 'list'])->name('assets.list');
        Route::get('/admin/assets/export-excel', [AssetController::class, 'exportExcel'])->name('assets.export');
        Route::get('/admin/assets/export-pdf', [AssetController::class, 'exportPdf'])->name('assets.pdf');
        Route::get('/admin/assets/{id}', [AssetController::class, 'show'])
            ->whereNumber('id')
            ->name('assets.show');
    });

    // --- AREA KHUSUS SUPER ADMIN ---
    Route::prefix('admin')->middleware('role:admin')->group(function () {

        // Dashboard Admin
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Map Settings
        Route::get('/map-settings', [ManajemenController::class, 'mapSettings'])->name('admin.map.settings');
        Route::post('/map-settings/update', [ManajemenController::class, 'updateMapSettings'])->name('admin.map.settings.update');

        // --- MANAJEMEN USER (CRUD) ---
        Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users.index');
        Route::post('/users/store', [UserManagementController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{id}/settings', [UserManagementController::class, 'settings'])->name('admin.users.settings');
        Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('admin.users.update');
        Route::get('/users/{id}/edit', [UserManagementController::class, 'edit'])->name('admin.users.edit');
        Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
        Route::patch('/users/{id}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('admin.users.toggle');

        // --- MANAJEMEN ASSETS (Hanya Admin yang bisa Tambah/Edit/Hapus) ---
        Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
        Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
        Route::post('/assets/store', [AssetController::class, 'store'])->name('assets.store');
        Route::get('/assets/{id}/edit', [AssetController::class, 'edit'])->name('assets.edit');
        Route::put('/assets/{id}', [AssetController::class, 'update'])->name('assets.update');
        Route::delete('/assets/{id}', [AssetController::class, 'destroy'])->name('assets.destroy');

        // --- KATEGORI & TIPE ASSET ---
        Route::get('/categories', [ManajemenController::class, 'categories'])->name('categories.index');
        Route::get('/assets-types', [ManajemenController::class, 'assetTypes'])->name('assets-types.index');

        // --- MODUL TIKET, PENGADUAN & VALIDASI ---
        Route::get('/laporan', [LaporanController::class, 'adminIndex'])->name('admin.laporan.index');
        Route::get('/tiket', [PengaduanController::class, 'index'])->name('admin.tiket.index');
        Route::get('/pengaduan', [PengaduanController::class, 'index'])->name('admin.pengaduan.index');
        Route::get('/pengaduan/petugas', [PengaduanController::class, 'indexPetugas'])->name('admin.pengaduan.petugas');
        Route::get('/pengaduan/detail/{id}', [PengaduanController::class, 'show'])->name('admin.pengaduan.show');
        Route::post('/pengaduan/validate/{id}', [PengaduanController::class, 'validateLaporan'])->name('admin.pengaduan.validate');
        Route::patch('/pengaduan/update-status/{id}', [PengaduanController::class, 'updateStatus'])->name('admin.pengaduan.update-status');
        Route::post('/pengaduan/selesaikan/{id}', [PengaduanController::class, 'selesaikan'])->name('admin.pengaduan.selesaikan');

        // --- MODUL PENUGASAN ---
        Route::get('/penugasan/{id}', [PenugasanController::class, 'index'])->name('admin.penugasan.index');
        Route::post('/penugasan/{id}/assign', [PenugasanController::class, 'assignTask'])->name('admin.penugasan.assign');
        Route::post('/penugasan/{id}/update', [PenugasanController::class, 'updateProgress'])->name('admin.penugasan.update');

        // --- MODUL MAINTENANCE ---
        Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('admin.maintenance');
        Route::get('/maintenance/list', [MaintenanceController::class, 'index'])->name('admin.maintenance.index');
        Route::get('/maintenance/create', [MaintenanceController::class, 'create'])->name('admin.maintenance.create');
        Route::post('/maintenance/store', [MaintenanceController::class, 'store'])->name('admin.maintenance.store');
        Route::get('/maintenance/{id}/edit', [MaintenanceController::class, 'edit'])->name('admin.maintenance.edit');
        Route::put('/maintenance/{id}', [MaintenanceController::class, 'update'])->name('admin.maintenance.update');
        Route::patch('/maintenance/{id}/update-status', [MaintenanceController::class, 'updateStatus'])->name('admin.maintenance.updateStatus');

        // --- MODUL LAPORAN PETUGAS & ACTIVITY LOG ---
        Route::get('/laporan-petugas', [LaporanPetugasController::class, 'create'])->name('laporan.petugas.create');
        Route::post('/laporan-petugas', [LaporanPetugasController::class, 'store'])->name('laporan.petugas.store');
        Route::get('/activity', [ActivityController::class, 'index'])->name('admin.activity.index');
    });

    // --- AREA KHUSUS SEKSI / PETUGAS LAPANGAN ---
    Route::prefix('admin/petugas')->middleware('role:seksi')->group(function () {
        Route::get('/tugas-tersedia', [TugasController::class, 'tersedia'])->name('petugas.tersedia');
        Route::get('/tugas-selesai', [TugasController::class, 'selesai'])->name('petugas.selesai');
        Route::patch('/update-tugas/{id}', [TugasController::class, 'updateStatus'])->name('petugas.tugas.updateStatus');
    });

    // --- AREA KHUSUS PETUGAS LAPANGAN (Monitoring Temuan) ---
    Route::prefix('petugas/lapangan')->middleware('role:petugas_lapangan')->group(function () {
        Route::get('/dashboard', [LaporanPetugasController::class, 'create'])->name('petugas.lapangan.dashboard');
        Route::post('/laporan/store', [LaporanPetugasController::class, 'store'])->name('petugas.lapangan.store');
    });

    // --- AREA KHUSUS KEPALA DINAS (EKSEKUTIF) ---
    Route::prefix('kadis')->middleware('role:kadis')->group(function () {
        Route::get('/dashboard', [EksekutifController::class, 'index'])->name('kadis.dashboard');
        Route::get('/export-pdf', [EksekutifController::class, 'exportPDF'])->name('eksekutif.export.pdf');
        Route::get('/export-excel', [EksekutifController::class, 'exportExcel'])->name('eksekutif.export.excel');

        // ========== ROUTE PENGUMUMAN KEPALA DINAS ==========
        Route::prefix('pengumuman')->name('kadis.pengumuman.')->group(function () {
            Route::get('/', [PengumumanController::class, 'index'])->name('index');
            Route::get('/create', [PengumumanController::class, 'create'])->name('create');
            Route::post('/store', [PengumumanController::class, 'store'])->name('store');
            Route::get('/{id}', [PengumumanController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [PengumumanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PengumumanController::class, 'update'])->name('update');
            Route::delete('/{id}', [PengumumanController::class, 'destroy'])->name('destroy');
        });
    });
});
