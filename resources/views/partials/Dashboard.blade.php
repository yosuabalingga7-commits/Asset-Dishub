@extends('layouts.app')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    * {
        font-family: 'Inter', sans-serif !important;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes fadeInScale {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    
    .stat-card {
        transition: all 0.3s ease;
        border: 1px solid #e2e8f0;
        animation: fadeInUp 0.4s ease forwards;
        opacity: 0;
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #3B82F6, #60A5FA, #93C5FD);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .stat-card:hover::before {
        opacity: 1;
    }
    
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.10s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .stat-card:nth-child(4) { animation-delay: 0.20s; }
    .stat-card:nth-child(5) { animation-delay: 0.25s; }
    .stat-card:nth-child(6) { animation-delay: 0.30s; }
    .stat-card:nth-child(7) { animation-delay: 0.35s; }
    .stat-card:nth-child(8) { animation-delay: 0.40s; }
    .stat-card:nth-child(9) { animation-delay: 0.45s; }
    .stat-card:nth-child(10) { animation-delay: 0.50s; }
    
    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 25px 30px -12px rgba(0, 0, 0, 0.15);
    }
    
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .category-item {
        transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        cursor: pointer;
        border: 1px solid #e2e8f0;
        animation: fadeInUp 0.4s ease forwards;
        opacity: 0;
        background: #f8fafc;
    }
    
    .category-item:nth-child(1) { animation-delay: 0.1s; }
    .category-item:nth-child(2) { animation-delay: 0.15s; }
    .category-item:nth-child(3) { animation-delay: 0.2s; }
    .category-item:nth-child(4) { animation-delay: 0.25s; }
    .category-item:nth-child(5) { animation-delay: 0.3s; }
    
    .category-item:hover {
        background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
        border-color: #3B82F6;
        transform: translateX(6px) scale(1.01);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
    }
    
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #e2e8f0;
        border-top-color: #3B82F6;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        transition: all 0.2s ease;
    }
    
    .status-badge:hover {
        transform: scale(1.05);
    }
    
    .priority-table {
        width: 100%;
        font-size: 11px;
    }
    
    .priority-table th {
        text-align: left;
        padding: 10px 8px;
        color: #64748b;
        font-weight: 700;
        font-size: 9px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        background: #f8fafc;
    }
    
    .priority-table td {
        padding: 10px 8px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s ease;
    }
    
    .priority-table tr:hover td {
        background: #f8fafc;
    }
    
    .priority-table tr:last-child td {
        border-bottom: none;
    }
    
    @media (max-width: 768px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .stat-card {
            padding: 1rem !important;
        }
        
        .stat-card .text-3xl {
            font-size: 1.5rem !important;
        }
    }
    
    .chart-container {
        position: relative;
        height: 280px;
        margin-bottom: 0.5rem;
    }
    
    .chart-card {
        transition: all 0.3s ease;
        animation: fadeInScale 0.5s ease forwards;
        opacity: 0;
        background: white;
    }
    
    .chart-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 30px -15px rgba(0, 0, 0, 0.1);
    }
    
    /* Warna dot gradient untuk stat-card */
    .stat-card-value {
        background: linear-gradient(135deg, #1e293b, #0f172a);
        -webkit-background-clip: text;
        background-clip: text;
    }
    
    /* Animasi refresh button */
    .refresh-btn:active {
        transform: scale(0.96);
    }
    
    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Gradient text */
    .gradient-text {
        background: linear-gradient(135deg, #3B82F6, #2563EB);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-[#F8FAFC] to-[#F1F5F9] py-6 px-4 md:px-8">
    
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-[#1E293B] tracking-tight">
                    Dashboard <span class="gradient-text">Administrator</span>
                </h1>
                <p class="text-slate-500 text-sm mt-1 flex items-center gap-2">
                    <span class="inline-block w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span>
                    Ringkasan data aset, laporan, dan kinerja petugas
                </p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="refreshAllData()" id="refreshBtn" class="refresh-btn bg-white border border-slate-200 text-slate-600 px-4 py-2 rounded-xl text-xs font-bold hover:bg-slate-50 hover:border-blue-300 transition-all duration-300 flex items-center gap-2 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh Data
                </button>
                <div class="text-right bg-white/60 backdrop-blur-sm px-4 py-2 rounded-xl shadow-sm">
                    <p class="text-[10px] text-slate-400 font-bold">TERAKHIR UPDATE</p>
                    <p class="text-[11px] font-bold text-slate-600" id="lastUpdateTime">{{ now()->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== BARIS 1: 3 GRAFIK ATAS ==================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        {{-- GRAFIK 1: BAR CHART - KATEGORI ASET --}}
        <div class="chart-card bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-[10px] font-bold text-blue-600 tracking-wider">DISTRIBUSI KATEGORI</p>
                    <h3 class="text-base font-black text-[#1E293B] mt-1">Per Kategori Aset</h3>
                </div>
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="kategoriBarChart"></canvas>
            </div>
        </div>

        {{-- GRAFIK 2: BAR CHART - PERBANDINGAN LAPORAN MASYARAKAT VS PETUGAS --}}
        <div class="chart-card bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-[10px] font-bold text-purple-600 tracking-wider">PERBANDINGAN LAPORAN</p>
                    <h3 class="text-base font-black text-[#1E293B] mt-1">Masyarakat vs Petugas</h3>
                </div>
                <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13v-1m4 1v-3m4 3V8M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="perbandinganLaporanChart"></canvas>
            </div>
        </div>

        {{-- GRAFIK 3: LINE CHART - TREN LAPORAN --}}
        <div class="chart-card bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-[10px] font-bold text-emerald-600 tracking-wider">TREN LAPORAN</p>
                    <h3 class="text-base font-black text-[#1E293B] mt-1">Laporan Masuk (7 Hari Terakhir)</h3>
                </div>
                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="laporanLineChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ==================== BARIS 2: 3 KONTEN BAWAH (KATEGORI LIST, DONAT, LAPORAN LIST) ==================== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        {{-- KOLOM KIRI: KATEGORI ASET LIST --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-[10px] font-bold text-indigo-600 tracking-wider">DISTRIBUSI KATEGORI</p>
                    <h3 class="text-base font-black text-[#1E293B] mt-1">Kategori Aset</h3>
                </div>
                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 01.586 1.414V19a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    </svg>
                </div>
            </div>
            <div class="space-y-3 max-h-[320px] overflow-y-auto pr-2">
                @php
                    $warnaList = ['blue', 'emerald', 'purple', 'amber', 'cyan'];
                @endphp
                @forelse($sidebar_categories as $index => $cat)
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl category-item" onclick="window.location.href='{{ route('assets.list', ['kategori' => $cat->kategori]) }}'">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300" style="background: linear-gradient(135deg, #EFF6FF, #DBEAFE);">
                            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-slate-800">{{ $cat->kategori }}</p>
                            <p class="text-[9px] text-slate-400">{{ $cat->total_jenis }} Jenis</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-black text-[#1E293B]">{{ number_format($cat->total_aset ?? 0) }}</p>
                        <p class="text-[9px] text-slate-400">Aset</p>
                    </div>
                </div>
                @empty
                <p class="text-center text-slate-400 text-xs py-8">Tidak ada data kategori</p>
                @endforelse
            </div>
        </div>

        {{-- KOLOM TENGAH: CHART PIE / DONAT STATUS ASET --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-[10px] font-bold text-rose-600 tracking-wider">DISTRIBUSI STATUS</p>
                    <h3 class="text-base font-black text-[#1E293B] mt-1">Kondisi Aset</h3>
                </div>
                <div class="w-10 h-10 bg-rose-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                </div>
            </div>
            <div class="relative" style="height: 260px;">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-3">
                <div class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                    <span class="text-[10px] font-bold text-slate-600">Baik</span>
                    <span class="text-[10px] font-black text-slate-800 ml-auto" id="legendBaik">0</span>
                </div>
                <div class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                    <span class="text-[10px] font-bold text-slate-600">Rusak</span>
                    <span class="text-[10px] font-black text-slate-800 ml-auto" id="legendRusak">0</span>
                </div>
                <div class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    <span class="text-[10px] font-bold text-slate-600">Kritis</span>
                    <span class="text-[10px] font-black text-slate-800 ml-auto" id="legendKritis">0</span>
                </div>
                <div class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                    <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                    <span class="text-[10px] font-bold text-slate-600">Proses</span>
                    <span class="text-[10px] font-black text-slate-800 ml-auto" id="legendProses">0</span>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: LAPORAN TERBARU --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-[10px] font-bold text-orange-600 tracking-wider">AKTIVITAS TERBARU</p>
                    <h3 class="text-base font-black text-[#1E293B] mt-1">Laporan Masuk</h3>
                </div>
                <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="space-y-3 max-h-[320px] overflow-y-auto pr-2" id="recentReports">
                <div class="text-center py-8">
                    <div class="loading-spinner"></div>
                    <p class="text-[10px] text-slate-400 mt-2">Memuat data laporan...</p>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100">
                <a href="{{ route('admin.pengaduan.index') }}" class="text-[10px] font-bold text-blue-600 hover:text-blue-700 flex items-center justify-center gap-1 transition-all hover:gap-2">
                    Kelola Semua Laporan
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- ==================== BARIS 3: KPI CARDS (10 KARTU) ==================== --}}
    <div class="dashboard-grid mb-8">
        <div class="stat-card bg-white rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider mb-1">TOTAL ASET</p>
                    <p class="text-3xl md:text-4xl font-black text-[#1E293B]" id="totalAset">{{ number_format($total_aset) }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <p class="text-[9px] text-slate-400">Total aset terdaftar dalam sistem</p>
            </div>
        </div>

        <div class="stat-card bg-white rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider mb-1">ASET BAIK</p>
                    <p class="text-3xl md:text-4xl font-black text-emerald-600" id="asetBaik">{{ number_format($final_status_stats['Baik'] ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <p class="text-[9px] text-slate-400">Aset dalam kondisi baik dan berfungsi normal</p>
            </div>
        </div>

        <div class="stat-card bg-white rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider mb-1">ASET RUSAK</p>
                    <p class="text-3xl md:text-4xl font-black text-amber-600" id="asetRusak">{{ number_format($final_status_stats['Rusak'] ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <p class="text-[9px] text-slate-400">Aset rusak yang perlu perbaikan segera</p>
            </div>
        </div>

        <div class="stat-card bg-white rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider mb-1">ASET KRITIS</p>
                    <p class="text-3xl md:text-4xl font-black text-red-600" id="asetKritis">{{ number_format($final_status_stats['Kritis'] ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center animate-pulse">
                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <p class="text-[9px] text-slate-400">Prioritas penanganan utama!</p>
            </div>
        </div>

        <div class="stat-card bg-white rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider mb-1">ASET PROSES</p>
                    <p class="text-3xl md:text-4xl font-black text-blue-600" id="asetProses">{{ number_format($final_status_stats['Proses Perbaikan'] ?? 0) }}</p>
                </div>
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <p class="text-[9px] text-slate-400">Aset sedang dalam proses perbaikan</p>
            </div>
        </div>

        <div class="stat-card bg-white rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider mb-1">TOTAL PENGADUAN</p>
                    <p class="text-3xl md:text-4xl font-black text-orange-600" id="totalPengaduan">{{ number_format($total_laporan) }}</p>
                </div>
                <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-orange-500"></div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <p class="text-[9px] text-slate-400">Total laporan dari masyarakat & petugas</p>
            </div>
        </div>

        <div class="stat-card bg-white rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider mb-1">LAPORAN MASYARAKAT</p>
                    <p class="text-3xl md:text-4xl font-black text-indigo-600" id="laporanMasyarakat">0</p>
                </div>
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <p class="text-[9px] text-slate-400">Laporan yang masuk dari masyarakat umum</p>
            </div>
        </div>

        <div class="stat-card bg-white rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider mb-1">LAPORAN PETUGAS</p>
                    <p class="text-3xl md:text-4xl font-black text-cyan-600" id="laporanPetugas">0</p>
                </div>
                <div class="w-10 h-10 bg-cyan-100 rounded-xl flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-cyan-500"></div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <p class="text-[9px] text-slate-400">Laporan teknis dari petugas lapangan</p>
            </div>
        </div>

        <div class="stat-card bg-white rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider mb-1">PETUGAS AKTIF</p>
                    <p class="text-3xl md:text-4xl font-black text-[#1E293B]" id="petugasAktif">{{ number_format($petugas_aktif) }}</p>
                </div>
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-slate-500"></div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <p class="text-[9px] text-slate-400">Petugas yang aktif bekerja hari ini</p>
            </div>
        </div>

        <div class="stat-card bg-white rounded-2xl p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 tracking-wider mb-1">TOTAL PEGAWAI</p>
                    <p class="text-3xl md:text-4xl font-black text-purple-600" id="totalPegawai">0</p>
                </div>
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                    <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100">
                <p class="text-[9px] text-slate-400">Total pegawai terdaftar di aplikasi</p>
            </div>
        </div>
    </div>

    {{-- ==================== BARIS 4: PRIORITAS PERBAIKAN TABLE ==================== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-[10px] font-bold text-red-500 tracking-wider">PRIORITAS TINDAK LANJUT</p>
                <h3 class="text-base font-black text-[#1E293B] mt-1">Aset Status Kritis Terbaru</h3>
            </div>
            <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center animate-pulse">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="priority-table w-full">
                <thead>
                    <tr>
                        <th>ID ASET</th>
                        <th>NAMA ASET</th>
                        <th>KATEGORI</th>
                        <th>LOKASI</th>
                        <th>STATUS</th>
                        <th class="text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody id="priorityAssetsTable">
                    <tr>
                        <td colspan="6" class="text-center py-8">
                            <div class="loading-spinner"></div>
                            <p class="text-[10px] text-slate-400 mt-2">Memuat data prioritas...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const statusStats = @json($final_status_stats);
    const totalAset = {{ $total_aset }};
    const totalLaporan = {{ $total_laporan }};
    const petugasAktif = {{ $petugas_aktif }};
    const sidebarCats = @json($sidebar_categories);
    
    let statusChart = null;
    let kategoriBarChart = null;
    let laporanLineChart = null;
    let perbandinganLaporanChart = null;
    
    async function loadPriorityAssets() {
        const container = document.getElementById('priorityAssetsTable');
        try {
            const response = await fetch('/api/priority-assets');
            const data = await response.json();
            
            if (data.assets && data.assets.length > 0) {
                container.innerHTML = data.assets.map(asset => `
                    <tr>
                        <td class="font-mono text-[10px] font-bold text-slate-600">${asset.id_asset}</td>
                        <td class="text-[11px] font-bold text-slate-800">${asset.nama}</td>
                        <td class="text-[10px] text-slate-600">${asset.kategori}</td>
                        <td class="text-[10px] text-slate-500">${asset.alamat.substring(0, 30)}${asset.alamat.length > 30 ? '...' : ''}</td>
                        <td><span class="status-badge" style="background: #EF444420; color: #EF4444;"><span class="w-1.5 h-1.5 rounded-full" style="background: #EF4444;"></span>Kritis</span></td>
                        <td class="text-right"><a href="/admin/assets/${asset.id}" class="text-blue-600 hover:text-blue-800 text-[10px] font-bold transition-all hover:mr-1">Detail →</a></td>
                    </tr>
                `).join('');
            } else {
                container.innerHTML = '<tr><td colspan="6" class="text-center py-8"><p class="text-[10px] text-slate-400">Tidak ada aset dengan status kritis</p></td></tr>';
            }
        } catch (error) {
            console.error('Gagal memuat prioritas aset:', error);
            container.innerHTML = '<tr><td colspan="6" class="text-center py-8"><p class="text-[10px] text-red-500">Gagal memuat data</p></td></tr>';
        }
    }
    
    async function loadReportTrend() {
        try {
            const response = await fetch('/api/report-trend');
            const data = await response.json();
            
            if (laporanLineChart) {
                laporanLineChart.data.datasets[0].data = data.counts;
                laporanLineChart.update();
            } else {
                initLineChart(data.labels, data.counts);
            }
        } catch (error) {
            console.error('Gagal memuat tren laporan:', error);
        }
    }
    
    async function loadRecentReports() {
        const container = document.getElementById('recentReports');
        try {
            const response = await fetch('/api/recent-reports');
            const data = await response.json();
            
            if (data.reports && data.reports.length > 0) {
                container.innerHTML = data.reports.map(report => `
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl hover:bg-white transition-all cursor-pointer" onclick="window.location.href='/admin/pengaduan/detail/${report.id}'">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center ${report.sumber === 'masyarakat' ? 'bg-green-100' : 'bg-blue-100'}">
                                <div class="w-2 h-2 rounded-full ${report.sumber === 'masyarakat' ? 'bg-green-500' : 'bg-blue-500'}"></div>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-slate-800">${report.judul_laporan.substring(0, 40)}${report.judul_laporan.length > 40 ? '...' : ''}</p>
                                <p class="text-[9px] text-slate-400">${report.nama_pelapor} • ${report.tanggal}</p>
                            </div>
                        </div>
                        <div class="status-badge" style="background: ${getStatusColor(report.status)}20; color: ${getStatusColor(report.status)};">
                            <span class="w-1.5 h-1.5 rounded-full" style="background: ${getStatusColor(report.status)};"></span>
                            ${getStatusText(report.status)}
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<div class="text-center py-8"><p class="text-[10px] text-slate-400">Belum ada laporan terbaru</p></div>';
            }
        } catch (error) {
            console.error('Gagal memuat laporan terbaru:', error);
            container.innerHTML = '<div class="text-center py-8"><p class="text-[10px] text-red-500">Gagal memuat data</p></div>';
        }
    }
    
    async function loadTotalPegawai() {
        try {
            const response = await fetch('/api/total-pegawai');
            const data = await response.json();
            document.getElementById('totalPegawai').innerText = data.total.toLocaleString();
        } catch (error) {
            console.error('Gagal memuat total pegawai:', error);
            document.getElementById('totalPegawai').innerText = '0';
        }
    }
    
    async function loadLaporanDetails() {
        try {
            const response = await fetch('/api/laporan-details');
            const data = await response.json();
            document.getElementById('laporanMasyarakat').innerText = data.laporan_masyarakat.toLocaleString();
            document.getElementById('laporanPetugas').innerText = data.laporan_petugas.toLocaleString();
            
            if (perbandinganLaporanChart) {
                perbandinganLaporanChart.data.datasets[0].data = [data.laporan_masyarakat, data.laporan_petugas];
                perbandinganLaporanChart.update();
            }
        } catch (error) {
            console.error('Gagal memuat detail laporan:', error);
            document.getElementById('laporanMasyarakat').innerText = '0';
            document.getElementById('laporanPetugas').innerText = '0';
        }
    }
    
    function getStatusColor(status) {
        const colors = {
            'masuk': '#3B82F6',
            'Proses Perbaikan': '#3B82F6',
            'proses': '#3B82F6',
            'selesai': '#10B981',
            'baik': '#10B981',
            'Baik': '#10B981',
            'ditolak': '#EF4444'
        };
        return colors[status] || '#64748B';
    }
    
    function getStatusText(status) {
        const texts = {
            'masuk': 'Masuk',
            'Proses Perbaikan': 'Proses',
            'proses': 'Proses',
            'selesai': 'Selesai',
            'baik': 'Selesai',
            'Baik': 'Selesai',
            'ditolak': 'Ditolak'
        };
        return texts[status] || status;
    }
    
    function initBarChart() {
        const ctx = document.getElementById('kategoriBarChart').getContext('2d');
        const categories = sidebarCats.map(cat => cat.kategori);
        const totals = sidebarCats.map(cat => cat.total_aset || 0);
        
        kategoriBarChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: categories,
                datasets: [{
                    label: 'Jumlah Aset',
                    data: totals,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderRadius: 8,
                    barPercentage: 0.7,
                    categoryPercentage: 0.8,
                    hoverBackgroundColor: '#3B82F6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                animation: { duration: 1000, easing: 'easeOutQuart' },
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                    x: { ticks: { font: { size: 9 }, maxRotation: 45, minRotation: 45 }, grid: { display: false } }
                }
            }
        });
    }
    
    function initPerbandinganLaporanChart(laporanMasyarakat = 0, laporanPetugas = 0) {
        const ctx = document.getElementById('perbandinganLaporanChart').getContext('2d');
        
        perbandinganLaporanChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Laporan Masuk'],
                datasets: [
                    { label: 'Masyarakat', data: [laporanMasyarakat], backgroundColor: '#8B5CF6', borderRadius: 8, barPercentage: 0.5, categoryPercentage: 0.8 },
                    { label: 'Petugas', data: [laporanPetugas], backgroundColor: '#06B6D4', borderRadius: 8, barPercentage: 0.5, categoryPercentage: 0.8 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                animation: { duration: 1000, easing: 'easeOutQuart' },
                plugins: { legend: { position: 'top', labels: { font: { size: 10, weight: 'bold' }, usePointStyle: true, boxWidth: 8 } } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: '#f1f5f9' }, title: { display: true, text: 'Jumlah Laporan', font: { size: 9 } } },
                    x: { ticks: { font: { size: 11, weight: 'bold' } }, grid: { display: false } }
                }
            }
        });
    }
    
    function initLineChart(labels = [], counts = []) {
        const ctx = document.getElementById('laporanLineChart').getContext('2d');
        const defaultLabels = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
        const defaultCounts = [0, 0, 0, 0, 0, 0, 0];
        
        laporanLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length > 0 ? labels : defaultLabels,
                datasets: [{
                    label: 'Jumlah Laporan',
                    data: counts.length > 0 ? counts : defaultCounts,
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.05)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3B82F6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#2563EB'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                animation: { duration: 1000, easing: 'easeOutQuart' },
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: '#f1f5f9' } },
                    x: { ticks: { font: { size: 10 } }, grid: { display: false } }
                }
            }
        });
    }
    
    function initStatusChart() {
        const ctx = document.getElementById('statusChart').getContext('2d');
        const data = {
            labels: ['Baik', 'Rusak', 'Kritis', 'Proses Perbaikan'],
            datasets: [{
                data: [
                    statusStats['Baik'] || 0,
                    statusStats['Rusak'] || 0,
                    statusStats['Kritis'] || 0,
                    statusStats['Proses Perbaikan'] || 0
                ],
                backgroundColor: ['#10B981', '#F59E0B', '#EF4444', '#3B82F6'],
                borderWidth: 0,
                hoverOffset: 8,
                hoverBorderWidth: 2,
                hoverBorderColor: '#fff'
            }]
        };
        
        const options = {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '65%',
            animation: { duration: 1000, easing: 'easeOutQuart' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = (statusStats['Baik'] || 0) + (statusStats['Rusak'] || 0) + (statusStats['Kritis'] || 0) + (statusStats['Proses Perbaikan'] || 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value.toLocaleString()} (${percentage}%)`;
                        }
                    }
                }
            }
        };
        
        if (statusChart) statusChart.destroy();
        statusChart = new Chart(ctx, { type: 'doughnut', data: data, options: options });
        
        document.getElementById('legendBaik').innerText = (statusStats['Baik'] || 0).toLocaleString();
        document.getElementById('legendRusak').innerText = (statusStats['Rusak'] || 0).toLocaleString();
        document.getElementById('legendKritis').innerText = (statusStats['Kritis'] || 0).toLocaleString();
        document.getElementById('legendProses').innerText = (statusStats['Proses Perbaikan'] || 0).toLocaleString();
    }
    
    async function refreshAllData() {
        const btn = document.getElementById('refreshBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="loading-spinner" style="width: 14px; height: 14px;"></span> Memuat...';
        
        try {
            await Promise.all([
                loadRecentReports(),
                loadTotalPegawai(),
                loadLaporanDetails(),
                loadPriorityAssets(),
                loadReportTrend()
            ]);
            document.getElementById('lastUpdateTime').innerText = new Date().toLocaleString('id-ID');
        } catch (error) {
            console.error('Refresh gagal:', error);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg> Refresh Data';
        }
    }
    
    let autoRefreshInterval = null;
    
    function startAutoRefresh() {
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
        autoRefreshInterval = setInterval(() => { refreshAllData(); }, 30000);
    }
    
    function stopAutoRefresh() {
        if (autoRefreshInterval) { clearInterval(autoRefreshInterval); autoRefreshInterval = null; }
    }
    
    document.addEventListener('DOMContentLoaded', async function() {
        let laporanMasyarakat = 0, laporanPetugas = 0;
        
        try {
            const response = await fetch('/api/laporan-details');
            const data = await response.json();
            laporanMasyarakat = data.laporan_masyarakat;
            laporanPetugas = data.laporan_petugas;
            document.getElementById('laporanMasyarakat').innerText = laporanMasyarakat.toLocaleString();
            document.getElementById('laporanPetugas').innerText = laporanPetugas.toLocaleString();
        } catch (error) { console.error('Gagal memuat detail laporan:', error); }
        
        initStatusChart();
        initBarChart();
        initPerbandinganLaporanChart(laporanMasyarakat, laporanPetugas);
        loadRecentReports();
        loadTotalPegawai();
        loadPriorityAssets();
        loadReportTrend();
        startAutoRefresh();
        
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) { stopAutoRefresh(); }
            else { startAutoRefresh(); refreshAllData(); }
        });
    });
</script>
@endsection