@extends('layouts.app')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

{{-- UPDATE: Library Marker Cluster Wajib Ada --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

<style>
    /* Mengaktifkan Font Inter untuk Seluruh Dashboard */
    * {
        font-family: 'Inter', sans-serif !important;
    }

    /* CSS UNTUK BULATAN WARNA KATEGORI */
    .cat-bullet {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
        border: 1px solid rgba(0,0,0,0.1);
    }

    /* CUSTOM LEAFLET CONTROLS POSITIONS */
    .leaflet-bottom.leaflet-right {
        display: flex !important;
        flex-direction: row-reverse !important; 
        align-items: flex-end !important;
        gap: 12px !important;
        margin-right: 20px !important;
        margin-bottom: 24px !important;
    }

    /* Penyesuaian Kontrol Map di Mobile agar tidak tertutup panel */
    @media (max-width: 768px) {
        .leaflet-bottom.leaflet-right {
            margin-bottom: 110px !important;
            margin-right: 10px !important;
            scale: 0.85;
        }
    }

    /* CUSTOM STYLE UNTUK LAYER CONTROL */
    .leaflet-control-layers {
        border: none !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        border-radius: 16px !important;
        padding: 0 !important;
        overflow: hidden;
        background: white !important;
    }

    .leaflet-control-layers-toggle {
        width: 48px !important;
        height: 48px !important;
        background-size: 24px 24px !important;
        background-color: white !important;
        border-radius: 12px !important;
    }

    .leaflet-control-layers-expanded {
        padding: 12px 16px !important;
        background: white !important;
        color: #374151 !important;
        min-width: 160px !important;
    }

    .leaflet-control-layers-base label {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        padding: 8px 0 !important;
        cursor: pointer !important;
        transition: all 0.2s;
    }

    .leaflet-control-layers-base label:hover {
        color: #4f46e5 !important;
    }

    /* ZOOM CONTROL CUSTOM */
    .leaflet-control-zoom {
        margin: 0 !important;
        border: none !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        border-radius: 12px !important;
        overflow: hidden;
    }
    
    .leaflet-control-zoom-in, .leaflet-control-zoom-out {
        background: white !important;
        color: #374151 !important;
        border: none !important;
        width: 36px !important;
        height: 36px !important;
        line-height: 36px !important;
        font-weight: bold !important;
    }

    .custom-div-icon { background: none; border: none; }
    
    #map { background: #f8fafc !important; min-height: 400px; }

    .font-black {
        font-weight: 900 !important;
        letter-spacing: -0.02em;
    }

    /* Scrollbar Style */
    #listContent::-webkit-scrollbar { width: 4px; }
    #listContent::-webkit-scrollbar-track { background: transparent; }
    #listContent::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

    .btn-reset-zoom {
        background: white;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        color: #374151;
        transition: all 0.2s;
    }
    .btn-reset-zoom:hover { color: #4f46e5; }

    /* Custom Style untuk Tombol Export */
    .btn-excel-custom {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background-color: #107c41;
        color: white !important;
        padding: 10px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all 0.3s ease;
        text-decoration: none !important;
        border-left: 1px solid rgba(255,255,255,0.2);
    }
    .btn-excel-custom:hover { background-color: #0d6334; }

    .btn-pdf-custom {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background-color: #e11d48;
        color: white !important;
        padding: 10px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all 0.3s ease;
        text-decoration: none !important;
    }
    .btn-pdf-custom:hover { background-color: #be123c; }

    /* Hover effect for status buttons */
    .status-active {
        outline: 2px solid #4f46e5;
        background-color: white !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    /* UPDATE: CUSTOM POPUP STYLING UNTUK TAMPILAN DETAIL DI ATAS ICON */
    .leaflet-popup-content-wrapper {
        padding: 0 !important;
        overflow: hidden !important;
        border-radius: 24px !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    }
    .leaflet-popup-content {
        margin: 0 !important;
        width: 280px !important;
    }
    @media (max-width: 768px) {
        .leaflet-popup-content { width: 240px !important; }
    }
    .leaflet-popup-tip-container {
        display: block !important;
    }
    .leaflet-popup-close-button {
        color: white !important;
        padding: 8px !important;
        font-size: 16px !important;
    }
</style>

<div class="relative flex flex-col md:block h-screen w-full overflow-hidden bg-gray-100">

    {{-- SISI KIRI: SEARCH & MONITORING FILTER --}}
    <div class="relative md:absolute top-0 md:top-4 left-0 md:left-4 z-[1001] w-full md:w-80 flex flex-col gap-3 p-4 md:p-0">
        {{-- SEARCH BOX --}}
        <div class="bg-white rounded-xl shadow-xl border-none p-2">
            <div class="relative flex items-center">
                <div class="absolute left-2.5">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" id="searchInput" placeholder="Cari Aset atau Wilayah..." 
                    class="w-full pl-9 pr-12 py-2 bg-gray-100 border-none rounded-lg text-xs focus:ring-2 focus:ring-indigo-500">
                <button onclick="triggerSearch()" class="absolute right-1.5 bg-indigo-600 hover:bg-indigo-700 text-white p-1.5 rounded-md transition-colors shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- MONITORING PANEL --}}
        <div class="bg-white rounded-2xl shadow-xl border-none overflow-hidden flex flex-col transition-all duration-300">
            <div id="btnToggleList" class="bg-indigo-700 p-4 text-white cursor-pointer hover:bg-indigo-800 transition-all flex justify-between items-center">
                <div>
                    <div id="statLabel" class="text-[10px] font-bold uppercase tracking-widest opacity-80 text-white/80">Total Seluruh Aset</div>
                    <div id="statTotalGlobal" class="text-3xl font-black mt-1">{{ $total_aset }}</div>
                </div>
                <div id="arrowIcon" class="transition-transform duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                </div>
            </div>
            
            <div id="assetListContainer" class="max-h-0 overflow-hidden transition-all duration-500 bg-gray-50">
                {{-- Breadcrumb --}}
                <div id="listBreadcrumb" class="hidden p-3 bg-indigo-50 border-b border-indigo-100 flex items-center gap-3 cursor-pointer hover:bg-indigo-100" onclick="resetToCategories()">
                    <div class="bg-indigo-600 text-white rounded-full p-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" /></svg></div>
                    <div class="flex flex-col">
                        <span class="text-[9px] text-indigo-400 font-bold uppercase leading-none">Kembali</span>
                        <span id="currentCategoryTitle" class="text-[11px] font-black text-indigo-800 uppercase truncate">Kategori</span>
                    </div>
                </div>

                <div id="listContent" class="p-2 space-y-1 max-h-[250px] md:max-h-[300px] overflow-y-auto">
                    {{-- Content --}}
                </div>
                
                <div class="flex flex-col">
                    {{-- TOMBOL EXPORT GRUP --}}
                    <div class="grid grid-cols-2">
                        <a href="{{ route('assets.pdf') }}" class="btn-pdf-custom" style="border-radius: 0 0 0 16px;">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            PDF
                        </a>
                        <a href="{{ route('assets.export') }}" class="btn-excel-custom" style="border-radius: 0 0 16px 0;">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16h-8v-2h8v2zm0-4h-8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                            EXCEL
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- STATUS FILTER PANEL --}}
        <div class="bg-white rounded-2xl shadow-lg border-none p-4">
            <div class="flex justify-between items-center mb-3">
                <span id="statusFilterTitle" class="text-[10px] font-black uppercase text-gray-400 tracking-wider">Kondisi Seluruh Aset</span>
            </div>
            <div class="grid grid-cols-2 gap-2 md:grid-cols-2 overflow-x-auto pb-1">
                <div onclick="filterByStatus('Baik')" id="btnStatBaik" class="min-w-[100px] group flex justify-between items-center text-[10px] font-bold p-2 bg-gray-50 rounded-lg cursor-pointer hover:bg-green-50 transition-all">
                    <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-green-500"></span><span class="text-gray-600 uppercase">Baik</span></div>
                    <span id="statBaik" class="text-gray-900 font-black">0</span>
                </div>
                <div onclick="filterByStatus('Rusak')" id="btnStatRusak" class="min-w-[100px] group flex justify-between items-center text-[10px] font-bold p-2 bg-gray-50 rounded-lg cursor-pointer hover:bg-orange-50 transition-all">
                    <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-orange-400"></span><span class="text-gray-600 uppercase">Rusak</span></div>
                    <span id="statRusak" class="text-gray-900 font-black">0</span>
                </div>
                <div onclick="filterByStatus('Kritis')" id="btnStatKritis" class="min-w-[100px] group flex justify-between items-center text-[10px] font-bold p-2 bg-gray-50 rounded-lg cursor-pointer hover:bg-red-50 transition-all">
                    <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-red-600"></span><span class="text-gray-600 uppercase">Kritis</span></div>
                    <span id="statKritis" class="text-gray-900 font-black">0</span>
                </div>
                <div onclick="filterByStatus('Proses Perbaikan')" id="btnStatProses" class="min-w-[100px] group flex justify-between items-center text-[10px] font-bold p-2 bg-gray-50 rounded-lg cursor-pointer hover:bg-blue-50 transition-all">
                    <div class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-blue-500"></span><span class="text-gray-600 uppercase">Proses</span></div>
                    <span id="statProses" class="text-gray-900 font-black">0</span>
                </div>
            </div>
        </div>
    </div>

    {{-- MAP CONTAINER (Wajib Full Height di Background) --}}
    <div id="map" class="absolute inset-0 z-0"></div>

    {{-- SISI KANAN: STATUS & RESET BUTTON --}}
    <div class="absolute bottom-6 md:top-4 right-4 z-[1001] flex flex-col gap-3 items-end pointer-events-none">
        {{-- TOMBOL REFRESH DASHBOARD --}}
        <button onclick="window.location.reload()" 
                class="bg-white p-3 rounded-xl shadow-xl border-none hover:bg-indigo-50 group transition-all pointer-events-auto" 
                title="Refresh Halaman">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 group-hover:rotate-180 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>

        {{-- Statistik Ringkas (Bawah kanan di HP, Atas kanan di Desktop) --}}
        <div class="w-72 md:w-72 flex flex-col gap-2 md:gap-3 pointer-events-auto">
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border-none p-3 md:p-4 flex items-center justify-between">
                <div>
                    <span class="text-[8px] md:text-[10px] font-black uppercase text-gray-400 tracking-wider block leading-none mb-1">Pengaduan</span>
                    <span class="text-lg md:text-2xl font-black text-orange-600 leading-tight">{{ $total_laporan }}</span>
                </div>
                <div class="bg-orange-100 p-2 md:p-2.5 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77-1.333.192 3 1.732 3z" />
                    </svg>
                </div>
            </div>

            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border-none p-3 md:p-4 flex items-center justify-between">
                <div>
                    <span class="text-[8px] md:text-[10px] font-black uppercase text-gray-400 tracking-wider block leading-none mb-1">Petugas Aktif</span>
                    <span class="text-lg md:text-2xl font-black text-blue-600 leading-tight">{{ $petugas_aktif }}</span>
                </div>
                <div class="bg-blue-100 p-2 md:p-2.5 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- DETAIL DASHBOARD --}}
<div id="detailDashboard" class="hidden"></div>

<script src="{{ asset('js/polygon-layers.js') }}"></script>
<script src="{{ asset('js/asset-layer-logic.js') }}?v={{ time() }}"></script>

<script>
    const MAP_SETTINGS = {
        latitude: {{ $mapConfig['center'][0] }},
        longitude: {{ $mapConfig['center'][1] }},
        defaultZoom: {{ $mapConfig['defaultZoom'] }},
        detailZoom: 18,
        lightMap: "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png",
        darkMap: "https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png",
        googleRoadmap: "https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}",
        googleSatellite: "https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}"
    };

    document.addEventListener("DOMContentLoaded", function () {
        const rawAssets = @json($assets);
        const sidebarCats = @json($sidebar_categories);
        const jenisStats = @json($jenis_stats);
        const statusColors = { 'Baik': '#16a34a', 'Rusak': '#fbbf24', 'Kritis': '#dc2626', 'Proses Perbaikan': '#3b82f6' };

        const asetDishub = rawAssets.map(a => ({
            id: a.id,
            kategori: a.kategori || "Umum",
            jenis: a.jenis || "",
            nama: a.nama || "",
            alamat: a.alamat || "",
            lat: parseFloat(a.lat),
            lng: parseFloat(a.lng),
            status: a.status,
            warna: statusColors[a.status] || '#64748b',
            foto: a.foto ? `/storage/${a.foto}` : "{{ asset('img/generic.png') }}",
            icon: a.icon_marker || '📍',
            edit_url: `/assets/${a.id}/edit`
        }));

        let currentKat = 'all';
        let currentJenis = 'all';
        let currentSta = 'all';
        let searchQuery = '';

        const map = L.map('map', { 
            zoomControl: false, 
            attributionControl: false, 
            maxZoom: 20 
        }).setView([MAP_SETTINGS.latitude, MAP_SETTINGS.longitude], MAP_SETTINGS.defaultZoom);

        if (typeof initPolygonLayers === 'function') {
            initPolygonLayers(map);
        }

        const lightGroup = L.tileLayer(MAP_SETTINGS.lightMap, { maxZoom: 20 }).addTo(map);
        const darkGroup = L.tileLayer(MAP_SETTINGS.darkMap, { maxZoom: 20 });
        const googleRoadmap = L.tileLayer(MAP_SETTINGS.googleRoadmap, { maxZoom: 20 });
        const satelliteLayer = L.tileLayer(MAP_SETTINGS.googleSatellite, { maxZoom: 22 });
        
        const markers = L.markerClusterGroup({
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            spiderfyOnMaxZoom: true,
            removeOutsideVisibleBounds: false,
            disableClusteringAtZoom: 13,
            maxClusterRadius: 130
        });
        map.addLayer(markers);

        map.on('click', function(e) {
            L.DomEvent.stop(e);
            resetMapView();
        });

        const baseMaps = {
            "<span class='text-[11px] font-bold uppercase'>⚪ Light Mode</span>": lightGroup,
            "<span class='text-[11px] font-bold uppercase'>🌑 Dark Mode</span>": darkGroup,
            "<span class='text-[11px] font-bold uppercase'>🗺️ Google Roadmap</span>": googleRoadmap,
            "<span class='text-[11px] font-bold uppercase'>🛰️ Satelit</span>": satelliteLayer
        };

        L.control.layers(baseMaps, null, { 
            position: 'bottomright', 
            collapsed: true 
        }).addTo(map);
        
        const zoomControl = L.control({ position: 'bottomright' });
        zoomControl.onAdd = function() {
            const div = L.DomUtil.create('div', 'flex flex-col gap-2');
            div.innerHTML = `
                <button onclick="resetMapView()" class="btn-reset-zoom" title="Reset Kamera">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                </button>
                <div class="leaflet-control-zoom leaflet-bar">
                    <a class="leaflet-control-zoom-in" href="#" title="Zoom in">+</a>
                    <a class="leaflet-control-zoom-out" href="#" title="Zoom out">−</a>
                </div>
            `;
            L.DomEvent.disableClickPropagation(div);
            return div;
        };
        zoomControl.addTo(map);

        window.resetMapView = function() {
            map.flyTo([MAP_SETTINGS.latitude, MAP_SETTINGS.longitude], MAP_SETTINGS.defaultZoom);
        };

        window.renderMap = function(kat = currentKat, jenis = currentJenis, sta = currentSta) {
            currentKat = kat; currentJenis = jenis; currentSta = sta;
            markers.clearLayers();
            let stats = { total: 0, baik: 0, rusak: 0, kritis: 0, proses: 0 };

            asetDishub.forEach(aset => {
                const matchKat = (currentKat === 'all' || aset.kategori === currentKat);
                const matchJenis = (currentJenis === 'all' || aset.jenis === currentJenis);
                const matchSta = (currentSta === 'all' || aset.status === currentSta);
                
                const s = searchQuery.toLowerCase().trim();
                let matchSearch = (s === '' || `${aset.nama} ${aset.alamat} ${aset.kategori}`.toLowerCase().includes(s));

                if (matchKat && matchJenis) {
                    if (aset.status === 'Baik') stats.baik++;
                    else if (aset.status === 'Rusak') stats.rusak++;
                    else if (aset.status === 'Kritis') stats.kritis++;
                    else if (aset.status === 'Proses Perbaikan') stats.proses++;
                    stats.total++;

                    if (matchSta && matchSearch) {
                        const marker = L.marker([aset.lat, aset.lng], { 
                            icon: createMarkerIcon(aset.kategori, aset.warna, aset.icon) 
                        });

                        const popupContent = `
                            <div class="bg-white overflow-hidden shadow-2xl">
                                <div class="relative h-32 bg-gray-200">
                                    <img src="${aset.foto}" class="w-full h-full object-cover">
                                    <div class="absolute top-2 right-2 px-2 py-0.5 rounded-full text-[8px] font-black uppercase text-white shadow-md border border-white/20" style="background-color: ${aset.warna}">
                                        ${aset.status}
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-black text-gray-800 uppercase text-[12px] leading-tight">${aset.nama}</h3>
                                    <div class="mt-1 flex items-start gap-1.5">
                                        <svg class="w-3 h-3 text-indigo-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                        <p class="text-[9px] text-gray-500 font-medium italic">${aset.alamat || 'Alamat tidak tersedia'}</p>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-gray-50">
                                        <div class="bg-gray-50 p-1.5 rounded-lg text-center">
                                            <span class="text-[7px] text-gray-400 font-bold uppercase block">Kategori</span>
                                            <span class="text-[9px] font-black text-gray-700 uppercase">${aset.kategori}</span>
                                        </div>
                                        <div class="bg-gray-50 p-1.5 rounded-lg text-center">
                                            <span class="text-[7px] text-gray-400 font-bold uppercase block">Jenis</span>
                                            <span class="text-[9px] font-black text-gray-700 uppercase">${aset.jenis}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        marker.bindPopup(popupContent, {
                            offset: L.point(0, -15),
                            className: 'custom-leaflet-popup'
                        });

                        marker.on('click', (e) => {
                            L.DomEvent.stopPropagation(e);
                            map.flyTo([aset.lat, aset.lng], MAP_SETTINGS.detailZoom);
                        });

                        markers.addLayer(marker);
                    }
                }
            });

            document.getElementById('statTotalGlobal').innerText = stats.total;
            document.getElementById('statBaik').innerText = stats.baik;
            document.getElementById('statRusak').innerText = stats.rusak;
            document.getElementById('statKritis').innerText = stats.kritis;
            document.getElementById('statProses').innerText = stats.proses;
        };

        function renderCategories() {
            const container = document.getElementById('listContent');
            document.getElementById('listBreadcrumb').classList.add('hidden');
            document.getElementById('statusFilterTitle').innerText = "Kondisi Seluruh Aset";

            container.innerHTML = sidebarCats.map(cat => {
                const color = getCategoryColor(cat.kategori);
                return `
                <div onclick="selectCategory('${cat.kategori}')" class="flex justify-between items-center p-3 hover:bg-white rounded-xl cursor-pointer transition-all group hover:shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="cat-bullet" style="background-color: ${color}"></span>
                        <div class="flex flex-col">
                            <span class="text-[11px] font-black text-gray-700 group-hover:text-indigo-600 uppercase leading-none">${cat.kategori}</span>
                            <span class="text-[9px] text-gray-400 font-bold mt-1">${cat.total_jenis} Jenis Aset</span>
                        </div>
                    </div>
                    <svg class="w-3 h-3 text-gray-300 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"></path></svg>
                </div>
            `}).join('');
        }

        window.selectCategory = (catName) => {
            currentKat = catName; currentJenis = 'all';
            document.getElementById('statLabel').innerText = catName;
            document.getElementById('currentCategoryTitle').innerText = catName;
            document.getElementById('listBreadcrumb').classList.remove('hidden');
            document.getElementById('statusFilterTitle').innerText = `Kondisi ${catName}`;
            
            const filteredJenis = jenisStats.filter(j => j.kategori === catName);
            const container = document.getElementById('listContent');
            container.innerHTML = filteredJenis.map(j => `
                <div onclick="selectJenis('${j.jenis}')" class="flex justify-between items-center p-3 hover:bg-white rounded-xl cursor-pointer transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-400 group-hover:scale-150 transition-all"></div>
                        <span class="text-[11px] font-bold text-gray-600 group-hover:text-indigo-600 uppercase">${j.jenis}</span>
                    </div>
                    <span class="bg-gray-100 group-hover:bg-indigo-600 group-hover:text-white px-2 py-0.5 rounded text-[10px] font-black text-gray-500">${j.total_titik}</span>
                </div>
            `).join('');
            renderMap(catName, 'all', currentSta);
        };

        window.selectJenis = (jenisName) => {
            currentJenis = jenisName;
            document.getElementById('statusFilterTitle').innerText = `Kondisi ${jenisName}`;
            renderMap(currentKat, jenisName, currentSta);
        };

        window.resetToCategories = () => {
            currentKat = 'all'; currentJenis = 'all';
            document.getElementById('statLabel').innerText = "Total Seluruh Aset";
            renderCategories();
            renderMap('all', 'all', currentSta);
        };

        window.triggerSearch = () => { 
            searchQuery = document.getElementById('searchInput').value; 
            renderMap(); 
        };
        
        window.filterByStatus = (status) => {
            currentSta = (currentSta === status) ? 'all' : status;
            
            ['Baik', 'Rusak', 'Kritis', 'Proses'].forEach(s => {
                const btnId = s === 'Proses' ? 'btnStatProses' : `btnStat${s}`;
                const btn = document.getElementById(btnId);
                if(btn) btn.classList.remove('status-active');
            });

            if(currentSta !== 'all') {
                const activeBtnId = status === 'Proses Perbaikan' ? 'btnStatProses' : `btnStat${status}`;
                const activeBtn = document.getElementById(activeBtnId);
                if(activeBtn) activeBtn.classList.add('status-active');
            }

            renderMap(currentKat, currentJenis, currentSta);
        };

        window.resetHalamanTotal = () => {
            searchQuery = '';
            document.getElementById('searchInput').value = '';
            currentSta = 'all';
            ['btnStatBaik', 'btnStatRusak', 'btnStatKritis', 'btnStatProses'].forEach(id => {
                const btn = document.getElementById(id);
                if(btn) btn.classList.remove('status-active');
            });
            currentKat = 'all';
            currentJenis = 'all';
            document.getElementById('statLabel').innerText = "Total Seluruh Aset";
            document.getElementById('statusFilterTitle').innerText = "Kondisi Seluruh Aset";
            renderCategories(); 
            resetMapView(); 
            renderMap('all', 'all', 'all'); 
        };

        document.getElementById('btnToggleList').addEventListener('click', () => {
            const container = document.getElementById('assetListContainer');
            const arrow = document.getElementById('arrowIcon');
            const isOpen = container.style.maxHeight !== '0px' && container.style.maxHeight;
            container.style.maxHeight = isOpen ? '0px' : '400px';
            arrow.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
        });

        renderCategories();
        renderMap();
    });
</script>
@endsection