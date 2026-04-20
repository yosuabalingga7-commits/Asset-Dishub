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
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Petugas\TugasController;
use App\Http\Controllers\Admin\UserManagementController;

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
Route::get('/api/asets-map', [AsetApiController::class, 'getAllAssets'])->name('api.asets.map');


// --- AREA TERPROTEKSI (WAJIB LOGIN) ---
Route::middleware(['auth'])->group(function () {

    // --- AREA KHUSUS SUPER ADMIN ---
    Route::prefix('admin')->middleware('role:super_admin')->group(function () {
        
        // Dashboard & Profile Settings
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/settings', [ProfileController::class, 'index'])->name('settings.index');
        Route::put('/settings/update', [ProfileController::class, 'update'])->name('settings.update');
        
        // Map & GIS Settings
        Route::get('/gis', [ManajemenController::class, 'gis'])->name('gis.index');
        Route::get('/map-settings', [ManajemenController::class, 'mapSettings'])->name('admin.map.settings');
        Route::post('/map-settings/update', [ManajemenController::class, 'updateMapSettings'])->name('admin.map.settings.update');

        // --- MANAJEMEN USER (CRUD) ---
        Route::get('/users', [UserManagementController::class, 'index'])->name('admin.users.index');
        Route::post('/users/store', [UserManagementController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{id}/settings', [UserManagementController::class, 'settings'])->name('admin.users.settings');
        Route::put('/users/{id}', [UserManagementController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{id}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');
        Route::patch('/users/{id}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('admin.users.toggle');

        // --- MANAJEMEN ASSETS (CRUD) ---
        Route::get('/assets', [AssetController::class, 'index'])->name('assets.index');
        Route::get('/assets/export-excel', [AssetController::class, 'exportExcel'])->name('assets.export');
        Route::get('/assets/export-pdf', [AssetController::class, 'exportPdf'])->name('assets.pdf');
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
        Route::get('/pengaduan/detail/{id}', [PengaduanController::class, 'show'])->name('admin.pengaduan.show');
        Route::post('/pengaduan/validate/{id}', [PengaduanController::class, 'validateLaporan'])->name('admin.pengaduan.validate');
        Route::patch('/pengaduan/update-status/{id}', [PengaduanController::class, 'updateStatus'])->name('admin.pengaduan.update-status');
        Route::post('/pengaduan/selesaikan/{id}', [PengaduanController::class, 'selesaikan'])->name('admin.pengaduan.selesaikan');

        // --- MODUL PENUGASAN (TASK LOGS) ---
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
        Route::get('/maintenance/{id}/detail', [MaintenanceController::class, 'show'])->name('admin.maintenance.show');
        Route::patch('/maintenance/{id}/update-status', [MaintenanceController::class, 'updateStatus'])->name('admin.maintenance.updateStatus');
        
        // --- MODUL LAPORAN PETUGAS & ACTIVITY LOG ---
        Route::get('/laporan-petugas', [LaporanPetugasController::class, 'create'])->name('laporan.petugas.create');
        Route::post('/laporan-petugas/store', [LaporanPetugasController::class, 'store'])->name('laporan.petugas.store');
        Route::get('/activity', [ActivityController::class, 'index'])->name('admin.activity.index');
    });

    // --- AREA KHUSUS SEKSI / PETUGAS LAPANGAN ---
    // Update: Prefix diubah ke admin/petugas agar sinkron dengan yang diakses ketua Bos
    Route::prefix('admin/petugas')->middleware('role:seksi')->group(function () {
        Route::get('/tugas-tersedia', [TugasController::class, 'tersedia'])->name('petugas.tersedia');
        Route::get('/tugas-selesai', [TugasController::class, 'selesai'])->name('petugas.selesai');
        Route::post('/update-tugas', [TugasController::class, 'updateStatus'])->name('petugas.update-tugas');
    });
});