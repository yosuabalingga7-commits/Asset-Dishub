<?php

/*
|--------------------------------------------------------------------------
| API Routes — LINTAS Asset-Dishub (Stateless REST API)
|--------------------------------------------------------------------------
|
| All routes here are prefixed with /api and wrapped in Sanctum's
| stateful middleware for SPA cookie-based authentication.
|
| Guard: sanctum (stateful via CSRF cookie — same domain SPA)
| Auth:  POST /api/auth/login  → issues session cookie (no token in LS)
|        POST /api/auth/logout → destroys session
|        GET  /api/auth/me    → returns authenticated user payload
|
| Route Grouping follows BOUNDED CONTEXTS (Domain Workflows), NOT CRUD.
*/

use Illuminate\Support\Facades\Route;

// ── Controller imports (ALL at file top — PHP requires this) ──
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AsetApiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanPetugasController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PenugasanController;
use App\Http\Controllers\Petugas\TugasController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\EksekutifController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ManajemenController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\Api\V1\ReportApiController;
use App\Http\Controllers\Api\V1\WorkflowApiController;
use App\Http\Controllers\Api\V1\TicketApiController;
use App\Http\Controllers\Api\V1\TelemetryApiController;
use App\Http\Controllers\Api\V1\SystemAdminApiController;

// ============================================================================
// [VERSION 1 API ENDPOINTS]
// ============================================================================
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Public spatial assets lookup
    Route::get('/assets/nearest', [ReportApiController::class, 'nearestAssets'])->name('assets.nearest');
    // Public citizen reporting form submission
    Route::post('/reports/public', [ReportApiController::class, 'storePublic'])->name('reports.public');

    // Protected API Endpoints (Sanctum SPA Session Guarded)
    Route::middleware('auth:sanctum')->group(function () {
        // Triage and validations
        Route::get('/reports', [WorkflowApiController::class, 'index'])->name('reports.index');
        Route::patch('/reports/{id}/validate', [WorkflowApiController::class, 'validateReport'])->name('reports.validate');
        // Protected officer reporting form submission
        Route::post('/reports/officer', [ReportApiController::class, 'storeOfficer'])->name('reports.officer');

        // Maintenance ticketing workflow
        Route::get('/maintenance-tickets', [TicketApiController::class, 'index'])->name('tickets.index');
        Route::post('/maintenance-tickets', [TicketApiController::class, 'store'])->name('tickets.store');
        Route::patch('/maintenance-tickets/{id}/complete', [TicketApiController::class, 'complete'])->name('tickets.complete');

        // Executive telemetry aggregations
        Route::get('/telemetry/summary', [TelemetryApiController::class, 'summary'])->name('telemetry.summary');
        Route::get('/telemetry/charts', [TelemetryApiController::class, 'charts'])->name('telemetry.charts');

        // System Administration CRUD & Config
        Route::apiResource('/users', SystemAdminApiController::class)->except(['show']);
        Route::get('/audit-logs', [SystemAdminApiController::class, 'auditLogs'])->name('audit-logs');
        Route::get('/system/map-settings', [SystemAdminApiController::class, 'getMapSettings'])->name('system.map-settings.show');
        Route::put('/system/map-settings', [SystemAdminApiController::class, 'updateMapSettings'])->name('system.map-settings.update');
    });
});

// ============================================================================
// [BOUNDED CONTEXT: AUTHENTICATION]
// ============================================================================
Route::prefix('auth')->name('api.auth.')->group(function () {
    Route::post('/login',  [LoginController::class, 'apiLogin'])->name('login');
    Route::post('/logout', [LoginController::class, 'apiLogout'])->middleware('auth:sanctum')->name('logout');
    Route::get('/me',      [LoginController::class, 'me'])->middleware('auth:sanctum')->name('me');
});

// ============================================================================
// [PUBLIC CONTEXT: SPATIAL & INTAKE — No auth required]
// Endpoints consumed by the public-facing intake form and GIS map widget.
// ============================================================================
Route::prefix('public')->name('api.public.')->group(function () {
    // Spatial asset feed (GIS map markers + PostGIS clustering)
    Route::get('/assets-map',             [AsetApiController::class, 'getAllAssets'])->name('assets.map');
    // Next auto-increment asset ID (used by admin create form)
    Route::get('/next-asset-number',      [AsetApiController::class, 'nextAssetNumber'])->name('assets.next-number');
    // Nearest asset geofencing (used by public report form)
    Route::get('/nearest-assets',         [LaporanController::class, 'nearestAssets'])->name('nearest-assets');
    // Nearest asset geofencing for officer report form
    Route::get('/nearest-assets-officer', [LaporanPetugasController::class, 'nearestAssets'])->name('nearest-assets.officer');
});

// ============================================================================
// [PROTECTED CONTEXT: All routes below require Sanctum SPA auth cookie]
// ============================================================================
Route::middleware('auth:sanctum')->group(function () {

    // ========================================================================
    // [BC-1: INTAKE WORKFLOW] Public Reports & Officer Field Reports
    // ========================================================================
    Route::prefix('intake')->name('api.intake.')->group(function () {
        Route::post('/reports',                [PengaduanController::class, 'store'])->name('reports.store');
        Route::get('/reports',                 [PengaduanController::class, 'index'])->name('reports.index');
        Route::get('/reports/{id}',            [PengaduanController::class, 'show'])->name('reports.show');
        Route::patch('/reports/{id}/validate', [PengaduanController::class, 'validateLaporan'])->name('reports.validate');
        Route::patch('/reports/{id}/status',   [PengaduanController::class, 'updateStatus'])->name('reports.status');
        Route::patch('/reports/{id}/resolve',  [PengaduanController::class, 'selesaikan'])->name('reports.resolve');
        Route::get('/reports-recent',          [PengaduanController::class, 'getRecentReports'])->name('reports.recent');
        Route::get('/reports-stats',           [PengaduanController::class, 'getLaporanDetails'])->name('reports.stats');
        Route::get('/reports-trend',           [PengaduanController::class, 'getReportTrend'])->name('reports.trend');
        Route::post('/officer-reports',        [LaporanPetugasController::class, 'store'])->name('officer-reports.store');
    });

    // ========================================================================
    // [BC-2: WORKFLOW & TICKETING] Triage, Assignment & Maintenance
    // ========================================================================
    Route::prefix('workflow')->name('api.workflow.')->group(function () {
        Route::get('/tickets',                [MaintenanceController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{id}',           [MaintenanceController::class, 'show'])->name('tickets.show');
        Route::post('/tickets',               [MaintenanceController::class, 'store'])->name('tickets.store');
        Route::put('/tickets/{id}',           [MaintenanceController::class, 'update'])->name('tickets.update');
        Route::patch('/tickets/{id}/status',  [MaintenanceController::class, 'updateStatus'])->name('tickets.status');

        Route::get('/assignments/{reportId}',          [PenugasanController::class, 'index'])->name('assignments.index');
        Route::post('/assignments/{reportId}/assign',  [PenugasanController::class, 'assignTask'])->name('assignments.assign');
        Route::post('/assignments/{reportId}/update',  [PenugasanController::class, 'updateProgress'])->name('assignments.progress');

        Route::get('/tasks/available',     [TugasController::class, 'tersedia'])->name('tasks.available');
        Route::get('/tasks/completed',     [TugasController::class, 'selesai'])->name('tasks.completed');
        Route::patch('/tasks/{id}/status', [TugasController::class, 'updateStatus'])->name('tasks.status');
    });

    // ========================================================================
    // [BC-3: ASSET GOVERNANCE] Asset CRUD & Catalog
    // ========================================================================
    Route::prefix('assets')->name('api.assets.')->group(function () {
        Route::get('/',             [AssetController::class, 'list'])->name('index');
        Route::get('/dashboard',    [AssetController::class, 'index'])->name('dashboard');
        Route::get('/priority',     [AssetController::class, 'getPriorityAssets'])->name('priority');
        Route::get('/export/excel', [AssetController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf',   [AssetController::class, 'exportPdf'])->name('export.pdf');
        Route::post('/',            [AssetController::class, 'store'])->name('store');
        Route::get('/{id}',         [AssetController::class, 'show'])->name('show');
        Route::post('/{id}',        [AssetController::class, 'update'])->name('update'); // POST for multipart/file
        Route::delete('/{id}',      [AssetController::class, 'destroy'])->name('destroy');
    });

    // ========================================================================
    // [BC-4: EXECUTIVE TELEMETRY] KPI Dashboard (Kadis / Admin)
    // ========================================================================
    Route::prefix('telemetry')->name('api.telemetry.')->group(function () {
        Route::get('/summary',      [DashboardController::class, 'apiSummary'])->name('summary');
        Route::get('/executive',    [EksekutifController::class, 'apiIndex'])->name('executive');
        Route::get('/export/pdf',   [EksekutifController::class, 'exportPDF'])->name('export.pdf');
        Route::get('/export/excel', [EksekutifController::class, 'exportExcel'])->name('export.excel');
    });

    // ========================================================================
    // [BC-5: GOVERNANCE & CONFIGURATION] Users, Roles, Map Settings, Logs
    // ========================================================================
    Route::prefix('governance')->name('api.governance.')->group(function () {
        // User management
        Route::get('/users',                      [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users',                     [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/total-staff',          [UserManagementController::class, 'getTotalPegawai'])->name('users.total');
        Route::get('/users/{id}',                 [UserManagementController::class, 'settings'])->name('users.show');
        Route::put('/users/{id}',                 [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}',              [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{id}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('users.toggle');

        // Map settings
        Route::get('/map-settings',  [ManajemenController::class, 'apiMapSettings'])->name('map-settings.show');
        Route::put('/map-settings',  [ManajemenController::class, 'updateMapSettings'])->name('map-settings.update');

        // Categories & Asset Types
        Route::get('/categories',    [ManajemenController::class, 'apiCategories'])->name('categories.index');
        Route::get('/asset-types',   [ManajemenController::class, 'apiAssetTypes'])->name('asset-types.index');

        // Activity log
        Route::get('/activity',      [ActivityController::class, 'index'])->name('activity.index');

        // Profile
        Route::put('/profile',       [ProfileController::class, 'update'])->name('profile.update');
        Route::post('/profile/photo', [ProfileController::class, 'updateFoto'])->name('profile.photo');

        // Announcements (Pengumuman Kadis) — RESTful resource
        Route::apiResource('/announcements', PengumumanController::class)->names([
            'index'   => 'announcements.index',
            'store'   => 'announcements.store',
            'show'    => 'announcements.show',
            'update'  => 'announcements.update',
            'destroy' => 'announcements.destroy',
        ]);
    });
});
