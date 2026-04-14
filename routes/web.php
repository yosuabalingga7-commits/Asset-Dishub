<?php

use Illuminate\Support\Facades\Route;
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
// --- IMPORT CONTROLLER BARU ---
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes - Dishub KBB
|--------------------------------------------------------------------------
*/

// --- HALAMAN DEPAN (GUEST/UMUM) ---
Route::get('/lapor', [LaporanController::class, 'index'])->name('lapor.index');
Route::post('/lapor/store', [LaporanController::class, 'store'])->name('lapor.store');

// DASHBOARD UTAMA (Peta Full Screen)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// API untuk Peta (Data JSON untuk marker di map)
Route::get('/api/asets-map', [AsetApiController::class, 'getAllAssets'])->name('api.asets.map');


// --- AREA ADMIN (DASHBOARD MANAJEMEN) ---
Route::prefix('admin')->group(function () {
    
    // --- MODUL PENGATURAN PROFIL & AKUN (BARU) ---
    Route::get('/settings', [ProfileController::class, 'index'])->name('settings.index');
    Route::put('/settings/update', [ProfileController::class, 'update'])->name('settings.update');

    // --- TAMBAHAN KHUSUS LAPORAN PETUGAS ---
    Route::get('/laporan-petugas', [LaporanPetugasController::class, 'create'])->name('laporan.petugas.create');
    Route::post('/laporan-petugas/store', [LaporanPetugasController::class, 'store'])->name('laporan.petugas.store');
    
    // Halaman Manajemen GIS (Peta Manajemen)
    Route::get('/gis', [ManajemenController::class, 'gis'])->name('gis.index');

    // MODUL MAP SETTINGS
    Route::get('/map-settings', [ManajemenController::class, 'mapSettings'])->name('admin.map.settings');
    // Perbaikan: Pastikan route POST map-settings/update ada jika dibutuhkan
    Route::post('/map-settings/update', [ManajemenController::class, 'updateMapSettings'])->name('admin.map.settings.update');

    // CRUD ASSETS
    Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
    
    // ROUTE EXPORT
    Route::get('/assets/export-excel', [AssetController::class, 'exportExcel'])->name('assets.export');
    Route::get('/assets/export-pdf', [AssetController::class, 'exportPdf'])->name('assets.pdf');
    
    // CRUD OPERATION
    Route::get('/assets/create', [AssetController::class, 'create'])->name('assets.create');
    // Perbaikan: Tambahkan route store jika belum ada
    Route::post('/assets/store', [AssetController::class, 'store'])->name('assets.store');
    
    Route::get('/assets/{id}/edit', [AssetController::class, 'edit'])->name('assets.edit');
    Route::put('/assets/{id}', [AssetController::class, 'update'])->name('assets.update');
    Route::delete('/assets/{id}', [AssetController::class, 'destroy'])->name('assets.destroy');

    // MANAJEMEN KATEGORI & TIPE
    Route::get('/categories', [ManajemenController::class, 'categories'])->name('categories.index');
    Route::get('/assets-types', [ManajemenController::class, 'assetTypes'])->name('assets-types.index');

    // MODUL TIKET & LAPORAN
    Route::get('/laporan', [LaporanController::class, 'adminIndex'])->name('admin.laporan.index');
    // FIX: Arahkan /tiket ke PengaduanController agar pagination jalan
    Route::get('/tiket', [PengaduanController::class, 'index'])->name('admin.tiket.index');

    // --- MODUL PENGADUAN MASYARAKAT ---
    Route::get('/pengaduan', [PengaduanController::class, 'index'])->name('admin.pengaduan.index');
    
    // Detail Pengaduan
    Route::get('/pengaduan/detail/{id}', [PengaduanController::class, 'show'])->name('admin.pengaduan.show');

    // --- UPDATE: Route Validasi Laporan & Kategori ---
    Route::post('/pengaduan/validate/{id}', [PengaduanController::class, 'validateLaporan'])->name('admin.pengaduan.validate');

    Route::patch('/pengaduan/update-status/{id}', [PengaduanController::class, 'updateStatus'])->name('admin.pengaduan.update-status');
    Route::post('/pengaduan/selesaikan/{id}', [PengaduanController::class, 'selesaikan'])->name('admin.pengaduan.selesaikan');

    // MODUL PENUGASAN (TASK LOGS) - Memastikan Name Route sesuai dengan Blade
    Route::get('/penugasan/{id}', [PenugasanController::class, 'index'])->name('admin.penugasan.index');
    // Perbaikan typo assignTask agar konsisten dengan controller
    Route::post('/penugasan/{id}/assign', [PenugasanController::class, 'assignTask'])->name('admin.penugasan.assign');
    // Route update progress penugasan
    Route::post('/penugasan/{id}/update', [PenugasanController::class, 'updateProgress'])->name('admin.penugasan.update');

    // --- MODUL MAINTENANCE & MONITORING ---
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('admin.maintenance');
    Route::get('/maintenance/index', [MaintenanceController::class, 'index'])->name('admin.maintenance.index');
    Route::get('/maintenance/create', [MaintenanceController::class, 'create'])->name('admin.maintenance.create');
    Route::post('/maintenance/store', [MaintenanceController::class, 'store'])->name('admin.maintenance.store');
    
    // --- ROUTE EDIT, UPDATE & SHOW MAINTENANCE ---
    Route::get('/maintenance/{id}/edit', [MaintenanceController::class, 'edit'])->name('admin.maintenance.edit');
    Route::put('/maintenance/{id}', [MaintenanceController::class, 'update'])->name('admin.maintenance.update');
    
    // Penyesuaian Route Detail agar konsisten dengan penamaan Admin
    Route::get('/maintenance/{id}', [MaintenanceController::class, 'show'])->name('admin.maintenance.show');
    
    // UPDATE STATUS MAINTENANCE (FITUR SELESAIKAN TUGAS)
    // Pastikan parameter {id} konsisten
    Route::patch('/maintenance/{id}/update-status', [MaintenanceController::class, 'updateStatus'])->name('admin.maintenance.updateStatus');
    
    // LOG ACTIVITY
    Route::get('/activity', [ActivityController::class, 'index'])->name('admin.activity.index');
});