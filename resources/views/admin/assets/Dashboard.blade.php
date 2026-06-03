@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

{{-- CSS UNTUK CLUSTERING (ANGKA) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />

<style>
    /* Paksa semua elemen menggunakan Inter */
    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    }

    .no-scrollbar::-webkit-scrollbar { display: none; }
    
    /* Marker Style */
    .custom-div-icon {
        background: none !important;
        border: none !important;
    }

    .awesome-marker-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        border: 2px solid white;
        transition: transform 0.2s ease;
    }
    
    .awesome-marker-wrapper:hover {
        transform: scale(1.1);
        z-index: 1000;
    }
    
    .awesome-marker-wrapper i {
        font-size: 20px;
    }
    
    .awesome-marker-tail {
        position: absolute;
        bottom: -8px;
        width: 12px;
        height: 12px;
        background: inherit;
        transform: rotate(45deg);
        border-right: 2px solid white;
        border-bottom: 2px solid white;
        border-radius: 0 0 2px 0;
    }

    .marker-cluster-small { background-color: rgba(16, 185, 129, 0.6); cursor: pointer; }
    .marker-cluster-small div { background-color: rgba(16, 185, 129, 0.9); color: white; font-family: 'Inter'; font-weight: 900; }
    .marker-cluster-medium { background-color: rgba(245, 158, 11, 0.6); cursor: pointer; }
    .marker-cluster-medium div { background-color: rgba(245, 158, 11, 0.9); color: white; font-family: 'Inter'; font-weight: 900; }
    .marker-cluster-large { background-color: rgba(244, 63, 94, 0.6); cursor: pointer; }
    .marker-cluster-large div { background-color: rgba(244, 63, 94, 0.9); color: white; font-family: 'Inter'; font-weight: 900; }

    .top-filter-bar {
        position: relative;
        z-index: 1000 !important;
        pointer-events: auto !important;
        background-color: white;
    }

    .filter-dropdown {
        display: none;
        position: fixed;
        top: auto;
        left: auto;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        padding: 0.5rem;
        min-width: 200px;
        max-width: 280px;
        z-index: 999999 !important;
    }
    
    .filter-dropdown.active {
        display: block !important;
    }

    .map-container {
        overflow: visible !important;
    }

    #map { 
        z-index: 1 !important; 
        cursor: crosshair; 
        width: 100%; 
        height: 100%;
    }

    .custom-leaflet-popup .leaflet-popup-content-wrapper {
        padding: 0 !important;
        overflow: hidden;
        border-radius: 24px !important;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1) !important;
        width: 320px !important;
    }
    .custom-leaflet-popup .leaflet-popup-content {
        margin: 0 !important;
        width: 320px !important;
    }
    .custom-leaflet-popup .leaflet-popup-tip {
        box-shadow: none !important;
        background: white !important;
    }

    .leaflet-control-zoom {
        border: none !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        margin-right: 20px !important;
    }

    /* SIDEBAR TRANSITION & STYLE PREMIUM */
    .sidebar-container {
        transition: width 0.3s ease, opacity 0.3s ease, margin 0.3s ease;
        overflow: hidden;
        position: relative;
    }
    
    .sidebar-container.collapsed {
        width: 0 !important;
        min-width: 0 !important;
        opacity: 0;
        padding: 0;
        margin: 0;
        border: none;
        overflow: hidden;
    }

    .toggle-sidebar-desktop-btn {
        background: #f1f5f9;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 12px;
        cursor: pointer;
        color: #334155;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    
    .toggle-sidebar-desktop-btn:hover {
        background: #e2e8f0;
        transform: scale(1.05);
    }
    
    .toggle-sidebar-desktop-btn i {
        font-size: 14px;
    }

    /* ============================================ */
    /* RESPONSIVE MOBILE - PREMIUM DRAWER SYSTEM */
    /* ============================================ */
    @media (max-width: 768px) {
        .dashboard-layout {
            flex-direction: row !important;
        }
        
        /* SIDEBAR SEBAGAI DRAWER */
        .sidebar-container {
            width: 280px !important;
            height: 100% !important;
            position: fixed !important;
            left: 0;
            top: 0;
            z-index: 1000 !important;
            background: white;
            box-shadow: 2px 0 15px rgba(0,0,0,0.2);
            transform: translateX(0);
            transition: transform 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1) !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
        }
        
        /* SIDEBAR TERSEMBUNYI */
        .sidebar-container.collapsed {
            transform: translateX(-100%) !important;
            transition: transform 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1) !important;
        }
        
        /* SIDEBAR TERBUKA */
        .sidebar-container:not(.collapsed) {
            transform: translateX(0) !important;
        }
        
        /* MAP CONTAINER MENJADI FULLSCREEN */
        .map-container {
            flex: 1 !important;
            height: 100vh !important;
            width: 100% !important;
        }
        
        /* TOMBOL DI POJOK KIRI ATAS - DI DEPAN KOLOM PENCARIAN */
        .toggle-sidebar-mobile-btn {
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            background: #f1f5f9 !important;
            border: none !important;
            width: 40px !important;
            height: 40px !important;
            border-radius: 12px !important;
            cursor: pointer !important;
            color: #334155 !important;
            transition: all 0.2s !important;
            margin-right: 8px !important;
            flex-shrink: 0 !important;
        }
        
        .toggle-sidebar-mobile-btn:active {
            transform: scale(0.95);
        }
        
        .toggle-sidebar-mobile-btn i {
            font-size: 16px !important;
            color: #334155 !important;
        }
        
        /* Sembunyikan tombol desktop di mobile */
        .toggle-sidebar-desktop-btn {
            display: none !important;
        }
        
        /* Atur ulang layout filter bar untuk mobile - tombol di kiri */
        .top-filter-bar .flex {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            flex-wrap: wrap !important;
        }
        
        .top-filter-bar .flex .search-wrapper {
            flex: 1 !important;
            min-width: 0 !important;
        }
        
        /* OVERLAY YANG MENUTUP KONTEN UTAMA SAAT SIDEBAR AKTIF */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 999;
            transition: all 0.3s ease;
        }
        
        .sidebar-overlay.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; backdrop-filter: blur(0px); }
            to { opacity: 1; backdrop-filter: blur(4px); }
        }
        
        /* KONTEN SIDEBAR - PASTIKAN SEMUA FITUR TAMPIL LENGKAP */
        .sidebar-container .flex-1 {
            overflow-y: visible !important;
            padding: 20px !important;
            padding-top: 20px !important;
        }
        
        /* Header sidebar */
        .sidebar-container .p-6 {
            padding: 20px !important;
            position: sticky;
            top: 0;
            background: #1E293B;
            z-index: 10;
        }
        
        /* Total Aset - pastikan keliatan dengan baik */
        .sidebar-container .bg-white.p-6 {
            padding: 20px !important;
            margin-top: 8px !important;
            margin-bottom: 16px !important;
            background: #ffffff !important;
            border-radius: 16px !important;
            display: block !important;
        }
        
        .sidebar-container .text-5xl {
            font-size: 36px !important;
        }
        
        .sidebar-container .bg-white.p-6 .text-5xl {
            font-size: 40px !important;
        }
        
        .md\:absolute.md\:top-6.md\:right-6 {
            display: none !important;
        }
        
        /* Filter dropdown di mobile */
        .filter-dropdown {
            max-width: 260px !important;
        }
    }
    
    @media (min-width: 769px) {
        .sidebar-container {
            width: 320px !important;
        }
        .toggle-sidebar-mobile-btn {
            display: none !important;
        }
        .sidebar-overlay {
            display: none !important;
        }
    }
    
    .kat-icon {
        width: 24px;
        text-align: center;
        font-size: 14px;
    }

    .search-wrapper {
        max-width: 320px;
        width: 100%;
    }
    
    /* ACTIVE STATE UNTUK KONDISI ASET - BACKGROUND BIRU ROYAL DENGAN TEKS PUTIH */
    .status-item.status-active {
        background-color: #2563EB !important;
        border-color: #2563EB !important;
    }
    
    .status-item.status-active .status-text,
    .status-item.status-active .status-count,
    .status-item.status-active .status-pct {
        color: white !important;
    }
    
    .status-item.status-active .status-bullet {
        border-color: white !important;
    }
    
    /* ACTIVE STATE UNTUK KATEGORI - BACKGROUND BIRU ROYAL DENGAN TEKS PUTIH */
    .category-item.category-active {
        background-color: #2563EB !important;
        border-color: #2563EB !important;
    }
    
    .category-item.category-active .category-text,
    .category-item.category-active .category-count,
    .category-item.category-active .category-icon {
        color: white !important;
    }
    
    .filter-active {
        background-color: #EFF6FF !important;
        border-color: #3B82F6 !important;
        color: #3B82F6 !important;
    }
</style>

<div class="flex flex-row h-screen w-full bg-[#F8FAFC] text-slate-900 overflow-hidden dashboard-layout" id="dashboardLayout">
    
    {{-- OVERLAY UNTUK MENUTUP KONTEN SAAT SIDEBAR AKTIF --}}
    <div id="sidebarOverlay" class="sidebar-overlay" onclick="closeSidebarMobile()"></div>
    
    {{-- SIDEBAR DENGAN BACKGROUND NAVY #1E293B --}}
    <aside class="sidebar-container w-full md:w-[320px] bg-[#1E293B] border-r border-white/10 flex flex-col z-20 shrink-0 overflow-hidden collapsed" id="mainSidebar">
        <div class="p-6 bg-[#1E293B] text-white">
            <h3 class="text-lg font-bold tracking-tight text-white">Dashboard GIS</h3>
            <p class="text-[10px] text-slate-400 font-medium mt-1">Dishub Kab. Bandung Barat</p>
        </div>

        <div class="flex-1 p-5 flex flex-col overflow-y-auto no-scrollbar">
            {{-- TOTAL ASET - Teks Navy Gelap #1E293B --}}
            <div class="bg-white p-6 rounded-xl border border-slate-200 text-center shadow-md mb-4">
                <p class="text-[10px] font-bold text-slate-500 mb-1">Total Aset Terdaftar</p>
                <h2 class="text-5xl font-black text-[#1E293B] tracking-tighter" id="totalAssetLabel">0</h2>
            </div>

            <div class="mb-6 p-4 bg-white border border-slate-100 rounded-xl shadow-sm">
                <p class="text-[11px] font-bold text-slate-500 mb-3 text-center tracking-widest">Persentase Kondisi</p>
                <div class="h-40 relative">
                    <canvas id="conditionChart"></canvas>
                </div>
            </div>

            {{-- LIST KONDISI ASET --}}
            <div class="space-y-2 mb-6">
                <p class="text-[11px] font-bold text-slate-400 ml-1">Kondisi Aset</p>
                @foreach(['Baik' => '#16a34a', 'Proses Perbaikan' => '#3b82f6', 'Rusak' => '#fbbf24', 'Kritis' => '#dc2626'] as $st => $hex)
                <div onclick="filterByStatus('{{ $st }}')" id="btnStat{{ $st == 'Proses Perbaikan' ? 'Proses' : $st }}" 
                     class="status-item bg-[#1E293B] p-3 rounded-lg border border-white/10 flex items-center justify-between shadow-sm hover:bg-[#334155] transition-all cursor-pointer">
                    <div class="flex items-center gap-3">
                        <span class="status-bullet w-2.5 h-2.5 rounded-full" style="background-color: {{ $hex }}"></span>
                        <span class="status-text text-[11px] font-bold text-slate-300 tracking-wide">{{ $st == 'Proses Perbaikan' ? 'Proses' : $st }}</span>
                    </div>
                    <div class="text-right">
                        <span class="status-count text-xs font-black text-white sidebar-status-count" data-status="{{ $st }}">0</span>
                        <span class="status-pct text-[9px] font-bold text-slate-400 block sidebar-status-pct" data-status-pct="{{ $st }}">0%</span>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- LIST KATEGORI --}}
            <div class="space-y-1 pt-4 border-t border-white/10">
                <p class="text-[11px] font-bold text-slate-400 ml-1 mb-2">Kategori</p>
                @php
                    $kategoriIcons = [
                        'Penerangan Jalan Umum (PJU)' => 'fa-lightbulb',
                        'Perlengkapan Jalan' => 'fa-triangle-exclamation',
                        'Fasilitas Lalu Lintas' => 'fa-traffic-light',
                        'Pengendalian dan Pengawasan' => 'fa-video',
                        'Prasarana Transportasi' => 'fa-bus'
                    ];
                @endphp
                @foreach([
                    'Penerangan Jalan Umum (PJU)' => 'emerald',
                    'Perlengkapan Jalan' => 'rose',
                    'Fasilitas Lalu Lintas' => 'blue',
                    'Pengendalian dan Pengawasan' => 'amber',
                    'Prasarana Transportasi' => 'slate'
                ] as $name => $color)
                <div onclick="selectCategory('{{ $name }}')" 
                     class="category-item flex justify-between items-center py-2 px-3 bg-[#1E293B] rounded-md border border-white/10 hover:bg-[#334155] cursor-pointer transition-all" 
                     data-category="{{ $name }}">
                    <div class="flex items-center gap-2">
                        <i class="category-icon fas {{ $kategoriIcons[$name] ?? 'fa-map-marker-alt' }} text-{{ $color }}-400 kat-icon"></i>
                        <span class="category-text text-[10px] font-bold text-slate-300 tracking-tight">{{ $name }}</span>
                    </div>
                    <span class="category-count text-[11px] font-black text-white sidebar-kat-count" data-katname="{{ $name }}">0</span>
                </div>
                @endforeach
            </div>

            <div class="mt-auto pt-10 pb-6">
                <!-- {{-- TOMBOL TAMBAH ASET BARU - HANYA UNTUK ADMIN --}}
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('assets.create', ['from' => 'map']) }}" class="w-full flex items-center justify-center gap-2 py-4 bg-[#2563EB] hover:bg-[#1D4ED8] text-white rounded-xl text-sm font-semibold shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                    <span class="text-lg font-bold">+</span> Tambah Aset Baru
                </a> -->
                @endif
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 relative map-container" style="overflow: visible !important;">
        
        <div class="bg-white border-b border-slate-200 p-3 top-filter-bar shadow-sm shrink-0" style="z-index: 10000; position: relative;">
            <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                
                {{-- TOMBOL UNTUK DESKTOP --}}
                <button onclick="toggleSidebarDesktop()" id="toggleSidebarDesktopBtn" class="toggle-sidebar-desktop-btn" title="Sembunyikan/Perlihatkan Sidebar">
                    <i class="fas fa-chevron-left" id="toggleSidebarIconDesktop"></i>
                </button>
                
                {{-- TOMBOL UNTUK MOBILE (icon <) - letaknya di depan kolom pencarian, icon tetap tidak berubah --}}
                <button onclick="toggleSidebarMobile()" id="toggleSidebarMobileBtn" class="toggle-sidebar-mobile-btn" title="Tampilkan/Sembunyikan Sidebar">
                    <i class="fas fa-chevron-left" id="toggleSidebarIconMobile"></i>
                </button>
                
                <div class="search-wrapper">
                    <div class="flex items-center bg-slate-100 rounded-lg border-2 border-transparent focus-within:border-[#3B82F6] focus-within:bg-white transition-all overflow-hidden">
                        <div class="pl-3 py-2 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input type="text" id="searchInput" placeholder="Cari aset atau wilayah..." class="bg-transparent border-none py-2 px-2 text-[11px] w-full focus:ring-0 outline-none font-medium text-slate-700">
                        <button onclick="triggerSearch()" class="bg-[#3B82F6] hover:bg-[#2563EB] text-white text-[10px] font-black px-3 py-2 shrink-0">Cari</button>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 lg:pb-0" style="position: relative; z-index: 10000;">
                    <div class="relative">
                        <button id="btnStatusFilter" onclick="toggleDropdown('statusDropdown', this)" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-bold flex items-center gap-1.5 hover:bg-slate-50 whitespace-nowrap text-slate-700">
                            Status <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div id="statusDropdown" class="filter-dropdown" style="position: fixed; z-index: 999999;">
                            <div class="p-2 border-b border-slate-100 font-bold text-[10px] text-[#3B82F6]">Filter Status</div>
                            <label class="flex items-center gap-3 p-2 hover:bg-slate-50 cursor-pointer rounded">
                                <input type="radio" name="fStatusRadio" value="all" checked class="w-3.5 h-3.5" onchange="filterByStatusRadio('all')">
                                <span class="text-[10px] font-bold text-slate-700">Semua Status</span>
                            </label>
                            @foreach(['Baik', 'Proses Perbaikan', 'Rusak', 'Kritis'] as $s)
                            <label class="flex items-center gap-3 p-2 hover:bg-slate-50 cursor-pointer rounded">
                                <input type="radio" name="fStatusRadio" value="{{ $s }}" class="w-3.5 h-3.5" onchange="filterByStatusRadio('{{ $s }}')">
                                <span class="text-[10px] font-bold text-slate-700">{{ $s == 'Proses Perbaikan' ? 'Proses' : $s }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="relative">
                        <button id="btnKategoriFilter" onclick="toggleDropdown('kategoriDropdown', this)" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-bold flex items-center gap-1.5 hover:bg-slate-50 whitespace-nowrap text-slate-700">
                            Kategori <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div id="kategoriDropdown" class="filter-dropdown" style="position: fixed; z-index: 999999;">
                            <div class="p-2 border-b border-slate-100 font-bold text-[10px] text-[#3B82F6]">Pilih Kategori</div>
                            <div id="kategoriList"></div>
                        </div>
                    </div>

                    <div class="relative">
                        <button id="btnJenisFilter" onclick="toggleDropdown('jenisDropdown', this)" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-bold flex items-center gap-1.5 hover:bg-slate-50 whitespace-nowrap text-slate-700">
                            Jenis <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div id="jenisDropdown" class="filter-dropdown w-56 max-h-64 overflow-y-auto no-scrollbar" style="position: fixed; z-index: 999999;">
                            <div class="p-2 border-b border-slate-100 font-bold text-[10px] text-[#3B82F6]">Pilih Jenis</div>
                            <div id="jenisList"></div>
                        </div>
                    </div>

                    {{-- TOMBOL RESET FILTER (SPESIFIK) - Hanya reset filter pencarian, warna Slate Grey #64748B --}}
                    <button onclick="resetFiltersOnly()" class="text-[9px] font-bold text-[#64748B] hover:text-[#1E293B] px-2 py-2 whitespace-nowrap shrink-0 transition-colors">
                        Reset Filter
                    </button>

                    {{-- TOMBOL RESET GLOBAL / REFRESH - Ikon refresh dengan warna Navy Gelap #1E293B --}}
                    <button onclick="resetGlobal()" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 shrink-0 transition-all" title="Refresh Global">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-[#1E293B]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <main class="flex-1 relative" style="z-index: 1;">
            <div id="map" class="bg-slate-900" style="height: 100%; width: 100%;"></div>
        </main>
    </div>
</div>

{{-- SCROLL KE ATAS SIDEBAR UNTUK MODE HP --}}
<script>
    (function() {
        function forceScrollToTop() {
            if (window.innerWidth <= 768) {
                var sidebar = document.getElementById('mainSidebar');
                if (sidebar) {
                    sidebar.scrollTop = 0;
                    sidebar.scrollTo({ top: 0, behavior: 'auto' });
                    console.log('Sidebar scrolled to top');
                }
            }
        }
        
        // Panggil saat halaman dimuat
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', forceScrollToTop);
        } else {
            forceScrollToTop();
        }
        
        // Panggil juga setelah 500ms (untuk memastikan)
        setTimeout(forceScrollToTop, 500);
        
        // Override toggle function
        var originalToggle = window.toggleSidebarMobile;
        if (typeof originalToggle === 'function') {
            window.toggleSidebarMobile = function() {
                originalToggle();
                setTimeout(forceScrollToTop, 350);
            };
        }
    })();
</script>

@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>

<script src="{{ asset('js/asset-layer-logic.js') }}"></script>
<script src="{{ asset('js/polygon-layers.js') }}"></script>

<script>
function toggleSidebarDesktop() {
    const sidebar = document.getElementById('mainSidebar');
    const icon = document.getElementById('toggleSidebarIconDesktop');
    
    if (sidebar.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-left');
    } else {
        sidebar.classList.add('collapsed');
        icon.classList.remove('fa-chevron-left');
        icon.classList.add('fa-chevron-right');
    }
    
    setTimeout(() => {
        if (window.map) window.map.invalidateSize();
    }, 300);
}

function toggleSidebarMobile() {
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (sidebar.classList.contains('collapsed')) {
        // BUKA SIDEBAR - Tampilkan semua fitur (grafik, total aset, kondisi, input)
        sidebar.classList.remove('collapsed');
        if (overlay) overlay.classList.add('active');
        setTimeout(() => {
            if (window.map) window.map.invalidateSize();
        }, 100);
    } else {
        // TUTUP SIDEBAR - Sembunyikan fitur
        sidebar.classList.add('collapsed');
        if (overlay) overlay.classList.remove('active');
        setTimeout(() => {
            if (window.map) window.map.invalidateSize();
        }, 300);
    }
}

function closeSidebarMobile() {
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (sidebar && !sidebar.classList.contains('collapsed')) {
        sidebar.classList.add('collapsed');
        if (overlay) overlay.classList.remove('active');
        setTimeout(() => {
            if (window.map) window.map.invalidateSize();
        }, 300);
    }
}

function toggleDropdown(dropdownId, buttonElement) {
    const dropdown = document.getElementById(dropdownId);
    if (!dropdown) return;
    
    document.querySelectorAll('.filter-dropdown').forEach(d => {
        if (d.id !== dropdownId) d.classList.remove('active');
    });
    
    const isActive = dropdown.classList.contains('active');
    
    if (!isActive && buttonElement) {
        const rect = buttonElement.getBoundingClientRect();
        dropdown.style.position = 'fixed';
        dropdown.style.top = (rect.bottom + window.scrollY + 5) + 'px';
        dropdown.style.left = (rect.left + window.scrollX) + 'px';
    }
    
    dropdown.classList.toggle('active');
}

document.addEventListener('click', function(event) {
    if (!event.target.closest('.relative') && !event.target.closest('.filter-dropdown')) {
        document.querySelectorAll('.filter-dropdown').forEach(d => d.classList.remove('active'));
    }
});

document.addEventListener("DOMContentLoaded", function() {
    let conditionChart = null; 
    const statusColors = { 'Baik': '#16a34a', 'Rusak': '#fbbf24', 'Kritis': '#dc2626', 'Proses Perbaikan': '#3b82f6' };
    
    const kategoriIconMap = {
        'Penerangan Jalan Umum (PJU)': 'fa-lightbulb',
        'Perlengkapan Jalan': 'fa-triangle-exclamation',
        'Fasilitas Lalu Lintas': 'fa-traffic-light',
        'Pengendalian dan Pengawasan': 'fa-video',
        'Prasarana Transportasi': 'fa-bus'
    };

    const assetsRaw = @json($assets ?? []);
    const sidebarCats = @json($sidebar_categories ?? []);
    const jenisStats = @json($jenis_stats ?? []);
    
    const assets = assetsRaw.map(a => ({
        id: a.id,
        kategori: (a.kategori || 'Lainnya').trim(),
        jenis: (a.jenis || 'Lainnya').trim(),
        nama: a.nama || '-',
        alamat: a.alamat || '-',
        lat: parseFloat(a.lat),
        lng: parseFloat(a.lng),
        status: a.status || 'Baik',
        warna: statusColors[a.status] || '#64748b',
        foto: a.foto ? `/storage/${a.foto.replace('public/', '')}` : "{{ asset('img/generic.png') }}",
        tgl_buat: a.created_at ? new Date(a.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-',
        icon_marker: a.icon_marker || (kategoriIconMap[a.kategori] || 'fa-map-marker-alt'),
        kecamatan: a.kecamatan || '-',
        kabupaten: a.kabupaten || 'Bandung Barat'
    }));

    let currentKat = 'all';
    let currentJenis = 'all';
    let currentSta = 'all';
    let searchQuery = '';

    const mapConfig = @json($mapConfig ?? ['center' => [-6.8431, 107.4912], 'defaultZoom' => 12]);
    const defaultCenter = [mapConfig.center[0], mapConfig.center[1]];
    const defaultZoom = mapConfig.defaultZoom;

    // ============================================
    // 4 MODE PETA
    // ============================================
    const lightMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { maxZoom: 20 });
    const darkMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 20 });
    const googleRoadmap = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { maxZoom: 20 });
    const googleSatellite = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', { maxZoom: 20 });

    const map = L.map('map', { 
        zoomControl: true, 
        attributionControl: false, 
        layers: [lightMap], // DEFAULT: LIGHT MODE
        dragging: true,
        scrollWheelZoom: true,
        doubleClickZoom: true,
        boxZoom: true,
        touchZoom: true
    }).setView(defaultCenter, defaultZoom);
    
    window.map = map;

    if (typeof initPolygonLayers === 'function') initPolygonLayers(map);

    const baseMaps = { 
        " Light Mode": lightMap, 
        " Dark Mode": darkMap,
        " Google Roadmap": googleRoadmap,
        " Google Satellite": googleSatellite
    };
    L.control.layers(baseMaps, null, { position: 'topright' }).addTo(map);

    const markers = L.markerClusterGroup({
        showCoverageOnHover: false,
        zoomToBoundsOnClick: false,
        spiderfyOnMaxZoom: true,
        disableClusteringAtZoom: 13
    });
    map.addLayer(markers);

    const createMarkerIcon = (kategori, warna, iconClass) => {
        let bgColor = warna;
        const textColor = '#ffffff';
        const html = `<div class="awesome-marker-wrapper" style="background: ${bgColor}; border-color: ${bgColor};">
                        <i class="fas ${iconClass}" style="color: ${textColor}; font-size: 20px;"></i>
                        <div class="awesome-marker-tail" style="background: ${bgColor};"></div>
                    </div>`;
        return L.divIcon({ html: html, className: '', iconSize: [40, 40], iconAnchor: [20, 40], popupAnchor: [0, -40] });
    };

    const getStatusEmoji = (status) => {
        switch(status) {
            case 'Baik': return '🟢';
            case 'Rusak': return '🟡';
            case 'Kritis': return '🔴';
            case 'Proses Perbaikan': return '🔵';
            default: return '⚪';
        }
    };

    function populateKategoriDropdown() {
        const kategoris = [...new Set(assets.map(a => a.kategori))].sort();
        const kategoriList = document.getElementById('kategoriList');
        if (!kategoriList) return;
        
        let html = `<label class="flex items-center gap-3 p-2 hover:bg-blue-50 cursor-pointer rounded border-b border-slate-100">
                        <input type="radio" name="fKatRadio" value="all" checked class="w-3.5 h-3.5" onchange="selectCategoryRadio('all')">
                        <span class="text-[10px] font-bold text-[#3B82F6]">Semua Kategori</span>
                    </label>`;
        
        kategoris.forEach(kat => {
            html += `<label class="flex items-center gap-3 p-2 hover:bg-slate-50 cursor-pointer rounded">
                        <input type="radio" name="fKatRadio" value="${kat}" class="w-3.5 h-3.5" onchange="selectCategoryRadio('${kat}')">
                        <span class="text-[10px] font-bold text-slate-700">${kat}</span>
                    </label>`;
        });
        
        kategoriList.innerHTML = html;
    }
    
    function updateJenisDropdown() {
        let filteredJenis = [];
        if (currentKat === 'all') {
            filteredJenis = [...new Set(assets.map(a => a.jenis))].sort();
        } else {
            filteredJenis = [...new Set(assets.filter(a => a.kategori === currentKat).map(a => a.jenis))].sort();
        }
        
        const jenisList = document.getElementById('jenisList');
        if (!jenisList) return;
        
        let html = `<label class="flex items-center gap-3 p-2 hover:bg-blue-50 cursor-pointer rounded border-b border-slate-100">
                        <input type="radio" name="fJenRadio" value="all" checked class="w-3.5 h-3.5" onchange="selectJenisRadio('all')">
                        <span class="text-[10px] font-bold text-[#3B82F6]">Semua Jenis</span>
                    </label>`;
        
        filteredJenis.forEach(jen => {
            html += `<label class="flex items-center gap-3 p-2 hover:bg-slate-50 cursor-pointer rounded">
                        <input type="radio" name="fJenRadio" value="${jen}" class="w-3.5 h-3.5" onchange="selectJenisRadio('${jen}')">
                        <span class="text-[10px] font-bold text-slate-700">${jen}</span>
                    </label>`;
        });
        
        jenisList.innerHTML = html;
    }

    window.renderMap = function(kat = currentKat, jenis = currentJenis, sta = currentSta) {
        currentKat = kat;
        currentJenis = jenis;
        currentSta = sta;
        
        markers.clearLayers();
        let stats = { total: 0, baik: 0, rusak: 0, kritis: 0, proses: 0 };

        assets.forEach(aset => {
            const matchKat = (currentKat === 'all' || aset.kategori === currentKat);
            const matchJenis = (currentJenis === 'all' || aset.jenis === currentJenis);
            const matchSta = (currentSta === 'all' || aset.status === currentSta);
            const matchSearch = (searchQuery === '' || `${aset.nama} ${aset.alamat} ${aset.kecamatan}`.toLowerCase().includes(searchQuery.toLowerCase()));

            if (matchKat && matchJenis && matchSta && matchSearch) {
                if (aset.status === 'Baik') stats.baik++;
                else if (aset.status === 'Rusak') stats.rusak++;
                else if (aset.status === 'Kritis') stats.kritis++;
                else if (aset.status === 'Proses Perbaikan') stats.proses++;
                stats.total++;

                if (aset.lat && aset.lng && !isNaN(aset.lat) && !isNaN(aset.lng)) {
                    const m = L.marker([aset.lat, aset.lng], { icon: createMarkerIcon(aset.kategori, aset.warna, aset.icon_marker) });
                    
                    m.bindPopup(`
                        <div class="flex flex-col w-full bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100" style="min-width: 300px;">
                            <div class="relative h-44 overflow-hidden bg-gray-100">
                                <img src="${aset.foto}" class="w-full h-full object-cover" onerror="this.src='{{ asset('img/generic.png') }}'">
                            </div>
                            <div class="p-5 flex flex-col gap-4">
                                <div><div class="flex items-center gap-2 mb-1.5"><span class="text-[9px] font-black text-gray-400 tracking-wider">Nama Aset</span></div><h4 class="text-base font-black text-gray-900 leading-tight">${aset.nama}</h4></div>
                                <div><div class="flex items-center gap-2 mb-1.5"><i class="fas fa-tag text-indigo-400 text-[10px]"></i><span class="text-[9px] font-black text-gray-400 tracking-wider">Kategori & Jenis</span></div><div class="flex items-center gap-2 flex-wrap"><div class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 rounded-xl"><i class="fas ${aset.icon_marker} text-indigo-500 text-xs"></i><span class="text-[11px] font-bold text-indigo-700">${aset.kategori}</span></div><span class="text-gray-300 text-xs">›</span><span class="text-[11px] font-medium text-gray-600 bg-gray-100 px-2 py-1 rounded-lg">${aset.jenis}</span></div></div>
                                <div><div class="flex items-center gap-2 mb-1.5"><i class="fas fa-chart-line text-indigo-400 text-[10px]"></i><span class="text-[9px] font-black text-gray-400 tracking-wider">Status Kondisi</span></div><div class="px-3 py-1.5 rounded-xl border-2 inline-flex items-center gap-2" style="border-color: ${aset.warna}20; background: ${aset.warna}08"><span class="text-base">${getStatusEmoji(aset.status)}</span><span class="text-[11px] font-black tracking-tighter" style="color: ${aset.warna}">${aset.status}</span></div></div>
                                <div><div class="flex items-center gap-2 mb-1.5"><i class="fas fa-location-dot text-indigo-400 text-[10px]"></i><span class="text-[9px] font-black text-gray-400 tracking-wider">Lokasi Alamat</span></div><p class="text-[11px] font-medium text-gray-700 leading-relaxed bg-gray-50 p-2.5 rounded-xl">${aset.alamat}</p></div>
                                <div class="pt-1 border-t border-gray-100"><div class="flex items-center gap-2"><i class="fas fa-calendar-alt text-indigo-400 text-[10px]"></i><span class="text-[9px] font-black text-gray-400 tracking-wider">Tanggal Dibuat</span><span class="text-[10px] font-medium text-gray-600 ml-auto bg-gray-100 px-2 py-0.5 rounded-full">${aset.tgl_buat}</span></div></div>
                                <div class="pt-2 flex gap-2"><a href="/admin/assets/${aset.id}" class="flex items-center justify-center gap-2 flex-1 bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 text-xs font-bold py-2.5 rounded-xl transition-all duration-300 shadow-sm"><i class="fas fa-eye text-indigo-500 text-xs"></i> Lihat Detail</a><a href="/admin/assets/${aset.id}/edit" class="flex items-center justify-center gap-2 flex-1 bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 hover:border-gray-300 text-xs font-bold py-2.5 rounded-xl transition-all duration-300 shadow-sm"><i class="fas fa-edit text-indigo-500 text-xs"></i> Edit</a></div>
                            </div>
                        </div>
                    `, { className: 'custom-leaflet-popup' });
                    markers.addLayer(m);
                }
            }
        });

        const totalLabel = document.getElementById('totalAssetLabel');
        if (totalLabel) totalLabel.innerText = stats.total;
        
        document.querySelectorAll('.sidebar-status-count').forEach(el => {
            const st = el.dataset.status;
            let count = 0;
            if (st === 'Baik') count = stats.baik;
            else if (st === 'Rusak') count = stats.rusak;
            else if (st === 'Kritis') count = stats.kritis;
            else if (st === 'Proses Perbaikan') count = stats.proses;
            el.innerText = count;
            const pctEl = document.querySelector(`[data-status-pct="${st}"]`);
            if (pctEl) pctEl.innerText = (stats.total > 0 ? ((count / stats.total) * 100).toFixed(1) : 0) + '%';
        });
        
        document.querySelectorAll('.sidebar-kat-count').forEach(el => {
            const katName = el.dataset.katname;
            const count = assets.filter(a => a.kategori === katName && 
                (currentJenis === 'all' || a.jenis === currentJenis) &&
                (currentSta === 'all' || a.status === currentSta) &&
                (searchQuery === '' || `${a.nama} ${a.alamat} ${a.kecamatan}`.toLowerCase().includes(searchQuery.toLowerCase()))).length;
            el.innerText = count;
        });

        updateChart({ 'Baik': stats.baik, 'Proses Perbaikan': stats.proses, 'Rusak': stats.rusak, 'Kritis': stats.kritis });
    };

    function updateChart(counts) {
        const canvas = document.getElementById('conditionChart');
        if(!canvas) return;
        const ctx = canvas.getContext('2d');
        
        // Hitung total untuk persentase
        const total = counts['Baik'] + counts['Proses Perbaikan'] + counts['Rusak'] + counts['Kritis'];
        
        const data = {
            labels: ['Baik', 'Proses', 'Rusak', 'Kritis'],
            datasets: [{
                data: [counts['Baik'], counts['Proses Perbaikan'], counts['Rusak'], counts['Kritis']],
                backgroundColor: [statusColors['Baik'], statusColors['Proses Perbaikan'], statusColors['Rusak'], statusColors['Kritis']],
                borderWidth: 0
            }]
        };
        
        const options = {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        };
        
        if (conditionChart) {
            conditionChart.data = data;
            conditionChart.options = options;
            conditionChart.update();
        } else {
            conditionChart = new Chart(ctx, { type: 'doughnut', data: data, options: options });
        }
    }

    window.selectCategory = function(catName) {
        currentKat = catName;
        currentJenis = 'all';
        const radioKat = document.querySelector(`input[name="fKatRadio"][value="${catName}"]`);
        if (radioKat) radioKat.checked = true;
        updateJenisDropdown();
        renderMap(currentKat, currentJenis, currentSta);
        
        // UPDATE ACTIVE STATE UNTUK KATEGORI
        document.querySelectorAll('.category-item').forEach(el => {
            if (el.dataset.category === catName) {
                el.classList.add('category-active');
            } else {
                el.classList.remove('category-active');
            }
        });
        
        const btnKategori = document.getElementById('btnKategoriFilter');
        if (catName !== 'all') {
            btnKategori.innerHTML = `Kategori: ${catName.substring(0, 15)} <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
            btnKategori.classList.add('filter-active');
        } else {
            btnKategori.innerHTML = `Kategori <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
            btnKategori.classList.remove('filter-active');
        }
        document.getElementById('kategoriDropdown')?.classList.remove('active');
    };
    
    window.selectCategoryRadio = function(catName) { selectCategory(catName); };
    window.selectJenisRadio = function(jenisName) {
        currentJenis = jenisName;
        renderMap(currentKat, currentJenis, currentSta);
        const btnJenis = document.getElementById('btnJenisFilter');
        if (jenisName !== 'all') {
            btnJenis.innerHTML = `Jenis: ${jenisName.substring(0, 15)} <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
            btnJenis.classList.add('filter-active');
        } else {
            btnJenis.innerHTML = `Jenis <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
            btnJenis.classList.remove('filter-active');
        }
        document.getElementById('jenisDropdown')?.classList.remove('active');
    };

    window.filterByStatus = function(status) {
        if(currentSta === status) {
            currentSta = 'all';
            // HAPUS ACTIVE STATE DARI SEMUA STATUS
            document.querySelectorAll('.status-item').forEach(item => item.classList.remove('status-active'));
        } else {
            currentSta = status;
            // HAPUS ACTIVE STATE DARI SEMUA STATUS, LALU TAMBAHKAN KE YANG DIPILIH
            document.querySelectorAll('.status-item').forEach(item => item.classList.remove('status-active'));
            const activeBtnId = { 'Baik': 'btnStatBaik', 'Rusak': 'btnStatRusak', 'Kritis': 'btnStatKritis', 'Proses Perbaikan': 'btnStatProses' }[status];
            const activeBtn = document.getElementById(activeBtnId);
            if(activeBtn) activeBtn.classList.add('status-active');
        }
        const radioValue = currentSta === 'all' ? 'all' : currentSta;
        const radio = document.querySelector(`input[name="fStatusRadio"][value="${radioValue}"]`);
        if(radio) radio.checked = true;
        const btnStatus = document.getElementById('btnStatusFilter');
        if (currentSta !== 'all') {
            const statusText = currentSta === 'Proses Perbaikan' ? 'Proses' : currentSta;
            btnStatus.innerHTML = `Status: ${statusText} <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
            btnStatus.classList.add('filter-active');
        } else {
            btnStatus.innerHTML = `Status <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
            btnStatus.classList.remove('filter-active');
        }
        renderMap(currentKat, currentJenis, currentSta);
        document.getElementById('statusDropdown')?.classList.remove('active');
    };
    
    window.filterByStatusRadio = function(status) { filterByStatus(status); };
    window.triggerSearch = function() { searchQuery = document.getElementById('searchInput').value; renderMap(); };
    
    // TOMBOL RESET MAP VIEW (POSISI PETA SAJA)
    window.resetMapView = function() { 
        map.setView(defaultCenter, defaultZoom, { animate: true }); 
    };
    
    // RESET FILTER SAJA (Spesifik - Tidak mengubah posisi peta)
    window.resetFiltersOnly = function() {
        searchQuery = '';
        document.getElementById('searchInput').value = '';
        currentSta = 'all';
        currentKat = 'all';
        currentJenis = 'all';
        
        // RESET ACTIVE STATE UNTUK STATUS
        document.querySelectorAll('.status-item').forEach(item => item.classList.remove('status-active'));
        
        // RESET ACTIVE STATE UNTUK KATEGORI
        document.querySelectorAll('.category-item').forEach(item => item.classList.remove('category-active'));
        
        const radioAllStatus = document.querySelector('input[name="fStatusRadio"][value="all"]');
        if (radioAllStatus) radioAllStatus.checked = true;
        const radioAllKat = document.querySelector('input[name="fKatRadio"][value="all"]');
        if (radioAllKat) radioAllKat.checked = true;
        const radioAllJen = document.querySelector('input[name="fJenRadio"][value="all"]');
        if (radioAllJen) radioAllJen.checked = true;
        const btnStatus = document.getElementById('btnStatusFilter');
        btnStatus.innerHTML = `Status <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
        btnStatus.classList.remove('filter-active');
        const btnKategori = document.getElementById('btnKategoriFilter');
        btnKategori.innerHTML = `Kategori <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
        btnKategori.classList.remove('filter-active');
        const btnJenis = document.getElementById('btnJenisFilter');
        btnJenis.innerHTML = `Jenis <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>`;
        btnJenis.classList.remove('filter-active');
        updateJenisDropdown();
        renderMap('all', 'all', 'all');
        document.querySelectorAll('.filter-dropdown').forEach(d => d.classList.remove('active'));
    };
    
    // RESET GLOBAL (Kembali ke kondisi awal termasuk posisi peta)
    window.resetGlobal = function() {
        resetFiltersOnly();
        resetMapView();
    };
    
    window.resetAllFilters = resetFiltersOnly;

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(() => {
                searchQuery = searchInput.value;
                renderMap();
            }, 500);
        });
    }

    // Inisialisasi sidebar dalam mode collapsed untuk mobile
    if (window.innerWidth <= 768) {
        const sidebar = document.getElementById('mainSidebar');
        if (sidebar && !sidebar.classList.contains('collapsed')) {
            sidebar.classList.add('collapsed');
        }
    }

    populateKategoriDropdown();
    updateJenisDropdown();
    renderMap();
});
</script>
@endpush