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

    @media (max-width: 768px) {
        .leaflet-bottom.leaflet-right {
            margin-bottom: 20px !important;
            margin-right: 10px !important;
            scale: 0.85;
        }

        /* Logic: Sembunyikan UI Dashboard jika sidebar sedang terbuka di HP */
        .mobile-hide-on-sidebar {
            opacity: 0 !important;
            pointer-events: none !important;
            transform: translateX(-10px);
        }
    }

    /* CUSTOM STYLE UNTUK LAYER CONTROL */
    .leaflet-control-layers {
        border: none !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        border-radius: 16px !important;
        background: white !important;
    }

    .leaflet-control-layers-toggle {
        width: 48px !important;
        height: 48px !important;
        background-size: 24px 24px !important;
        background-color: white !important;
        border-radius: 12px !important;
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

    #map {
        background: #f8fafc !important;
    }

    .font-black {
        font-weight: 900 !important;
        letter-spacing: -0.02em;
    }

    #listContent::-webkit-scrollbar {
        width: 4px;
    }

    #listContent::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }

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
    }

    /* Excel & PDF Styles */
    .btn-excel-custom {
        background-color: #107c41;
        color: white !important;
        padding: 10px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        text-align: center;
        text-decoration: none !important;
    }

    .btn-pdf-custom {
        background-color: #e11d48;
        color: white !important;
        padding: 10px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        text-align: center;
        text-decoration: none !important;
    }
    
    .status-active {
        outline: 2px solid #4f46e5;
        background-color: white !important;
    }

    /* Popup Styling Fix */
    .leaflet-popup-content-wrapper {
        border-radius: 24px !important;
        padding: 0 !important;
        overflow: hidden !important;
    }

    .leaflet-popup-content {
        margin: 0 !important;
        width: 280px !important;
    }
</style>

<div class="relative flex flex-col h-full w-full overflow-hidden bg-gray-100">
    
    {{-- MAP CONTAINER --}}
    <div id="map" class="absolute inset-0 z-0"></div>

    {{-- INTERFACE LAYER --}}
    {{-- UPDATE: Z-index diatur z-[1000] agar di bawah navbar/sidebar --}}
    {{-- UPDATE: x-bind class untuk menyembunyikan UI jika sidebar dibuka di mobile --}}
    <div class="absolute inset-0 z-[1000] pointer-events-none flex flex-col p-4 md:p-6 transition-all duration-300"
         :class="sidebarOpen ? 'mobile-hide-on-sidebar' : ''">
        
        {{-- SISI KIRI: SEARCH & MONITORING FILTER --}}
        <div class="w-full md:w-80 flex flex-col gap-3 pointer-events-auto">
            
            {{-- SEARCH BOX --}}
            <div class="bg-white rounded-xl shadow-2xl border-none p-2 w-full">
                <div class="relative flex items-center">
                    <div class="absolute left-2.5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="searchInput" placeholder="Cari Aset atau Wilayah..." 
                        class="w-full pl-9 pr-12 py-2 bg-gray-100 border-none rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 placeholder:text-gray-400">
                    <button onclick="triggerSearch()" class="absolute right-1.5 bg-indigo-600 hover:bg-indigo-700 text-white p-1.5 rounded-md transition-all shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- MONITORING PANEL --}}
            <div class="bg-white rounded-2xl shadow-2xl border-none overflow-hidden flex flex-col transition-all duration-500">
                <div id="btnToggleList" class="bg-indigo-700 p-4 text-white cursor-pointer hover:bg-indigo-800 transition-all flex justify-between items-center group">
                    <div>
                        <div id="statLabel" class="text-[10px] font-bold uppercase tracking-widest opacity-80 group-hover:opacity-100">Total Seluruh Aset</div>
                        <div id="statTotalGlobal" class="text-3xl font-black mt-1 leading-none">{{ $total_aset }}</div>
                    </div>
                    <div id="arrowIcon" class="bg-white/20 p-1 rounded-lg transition-transform duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                
                <div id="assetListContainer" class="max-h-0 overflow-hidden transition-all duration-500 bg-gray-50/50">
                    <div id="listBreadcrumb" class="hidden p-3 bg-indigo-50 border-b border-indigo-100 flex items-center gap-3 cursor-pointer hover:bg-indigo-100 transition-colors" onclick="resetToCategories()">
                        <div class="bg-indigo-600 text-white rounded-full p-1 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" /></svg>
                        </div>
                        <div class="flex flex-col text-left">
                            <span class="text-[9px] text-indigo-400 font-bold uppercase leading-none">Kembali</span>
                            <span id="currentCategoryTitle" class="text-[11px] font-black text-indigo-800 uppercase leading-tight">Kategori</span>
                        </div>
                    </div>

                    <div id="listContent" class="p-2 space-y-1 max-h-[200px] md:max-h-[300px] overflow-y-auto custom-scrollbar">
                        {{-- Data Kategori/Jenis akan dirender di sini via JS --}}
                    </div>
                    
                    <div class="grid grid-cols-2 border-t border-gray-100">
                        <a href="{{ route('assets.pdf') }}" class="btn-pdf-custom" style="border-radius: 0 0 0 16px;">PDF</a>
                        <a href="{{ route('assets.export') }}" class="btn-excel-custom" style="border-radius: 0 0 16px 0;">EXCEL</a>
                    </div>
                </div>
            </div>

            {{-- STATUS FILTER PANEL --}}
            <div class="bg-white rounded-2xl shadow-2xl border-none p-4 w-full">
                <div class="flex items-center justify-between mb-4">
                    <span id="statusFilterTitle" class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Kondisi Seluruh Aset</span>
                    <button onclick="resetHalamanTotal()" class="text-[9px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md hover:bg-indigo-100 transition-colors uppercase">Reset</button>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div onclick="filterByStatus('Baik')" id="btnStatBaik" class="flex justify-between items-center text-[10px] font-bold p-2.5 bg-gray-50 rounded-xl cursor-pointer hover:bg-green-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500 shadow-sm shadow-green-200"></span>
                            <span class="text-gray-600 uppercase">Baik</span>
                        </div>
                        <span id="statBaik" class="text-gray-900 font-black">0</span>
                    </div>
                    <div onclick="filterByStatus('Rusak')" id="btnStatRusak" class="flex justify-between items-center text-[10px] font-bold p-2.5 bg-gray-50 rounded-xl cursor-pointer hover:bg-orange-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-orange-400 shadow-sm shadow-orange-200"></span>
                            <span class="text-gray-600 uppercase">Rusak</span>
                        </div>
                        <span id="statRusak" class="text-gray-900 font-black">0</span>
                    </div>
                    <div onclick="filterByStatus('Kritis')" id="btnStatKritis" class="flex justify-between items-center text-[10px] font-bold p-2.5 bg-gray-50 rounded-xl cursor-pointer hover:bg-red-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-red-600 shadow-sm shadow-red-200"></span>
                            <span class="text-gray-600 uppercase">Kritis</span>
                        </div>
                        <span id="statKritis" class="text-gray-900 font-black">0</span>
                    </div>
                    <div onclick="filterByStatus('Proses Perbaikan')" id="btnStatProses" class="flex justify-between items-center text-[10px] font-bold p-2.5 bg-gray-50 rounded-xl cursor-pointer hover:bg-blue-50 transition-colors">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shadow-sm shadow-blue-200"></span>
                            <span class="text-gray-600 uppercase">Proses</span>
                        </div>
                        <span id="statProses" class="text-gray-900 font-black">0</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- SISI KANAN: FLOATING STATS --}}
        <div class="mt-auto md:mt-0 md:absolute md:top-6 md:right-6 flex flex-col gap-3 items-end pointer-events-auto">
            <button onclick="window.location.reload()" class="bg-white p-3 rounded-xl shadow-2xl hover:bg-gray-50 transition-all text-indigo-600 transform hover:scale-110 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>

            <div class="w-full md:w-64 flex flex-col gap-2">
                <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl p-3 md:p-4 flex items-center justify-between border-none">
                    <div>
                        <span class="text-[8px] md:text-[10px] font-black uppercase text-gray-400 block mb-0.5 tracking-widest">Total Pengaduan</span>
                        <span class="text-xl md:text-2xl font-black text-orange-600 leading-none">{{ $total_laporan }}</span>
                    </div>
                    <div class="bg-orange-100 p-2.5 rounded-xl text-orange-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77-1.333.192 3 1.732 3z" /></svg>
                    </div>
                </div>
                <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl p-3 md:p-4 flex items-center justify-between border-none">
                    <div>
                        <span class="text-[8px] md:text-[10px] font-black uppercase text-gray-400 block mb-0.5 tracking-widest">Petugas Aktif</span>
                        <span class="text-xl md:text-2xl font-black text-blue-600 leading-none">{{ $petugas_aktif }}</span>
                    </div>
                    <div class="bg-blue-100 p-2.5 rounded-xl text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SCRIPT ASLI --}}
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
        
        const statusColors = {
            'Baik': '#16a34a',
            'Rusak': '#fbbf24',
            'Kritis': '#dc2626',
            'Proses Perbaikan': '#3b82f6'
        };

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
            icon: a.icon_marker || '📍'
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

        const baseLayers = {
            "Light Mode": L.tileLayer(MAP_SETTINGS.lightMap, { maxZoom: 20 }),
            "Dark Mode": L.tileLayer(MAP_SETTINGS.darkMap, { maxZoom: 20 }),
            "Google Roadmap": L.tileLayer(MAP_SETTINGS.googleRoadmap, { maxZoom: 20 }),
            "Google Satellite": L.tileLayer(MAP_SETTINGS.googleSatellite, { maxZoom: 20 })
        };
        baseLayers["Light Mode"].addTo(map);

        L.control.layers(baseLayers, null, { position: 'bottomright' }).addTo(map);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        const markers = L.markerClusterGroup({
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true,
            spiderfyOnMaxZoom: true,
            disableClusteringAtZoom: 13
        });
        map.addLayer(markers);

        const createMarkerIcon = (kat, warna, icon) => {
            return L.divIcon({
                html: `<div class="relative flex items-center justify-center w-10 h-10 bg-white rounded-full shadow-lg border-2 transition-transform hover:scale-110" style="border-color: ${warna}">
                        <span class="text-lg">${icon}</span>
                        <div class="absolute -bottom-1 w-3 h-3 rotate-45 border-r-2 border-b-2 bg-white" style="border-color: ${warna}"></div>
                       </div>`,
                className: '',
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            });
        };

        window.renderMap = function(kat = currentKat, jenis = currentJenis, sta = currentSta) {
            currentKat = kat;
            currentJenis = jenis;
            currentSta = sta;
            
            markers.clearLayers();
            let stats = { total: 0, baik: 0, rusak: 0, kritis: 0, proses: 0 };

            asetDishub.forEach(aset => {
                const matchKat = (currentKat === 'all' || aset.kategori === currentKat);
                const matchJenis = (currentJenis === 'all' || aset.jenis === currentJenis);
                const matchSta = (currentSta === 'all' || aset.status === currentSta);
                const matchSearch = (searchQuery === '' || `${aset.nama} ${aset.alamat}`.toLowerCase().includes(searchQuery.toLowerCase()));

                if (matchKat && matchJenis) {
                    if (aset.status === 'Baik') stats.baik++;
                    else if (aset.status === 'Rusak') stats.rusak++;
                    else if (aset.status === 'Kritis') stats.kritis++;
                    else if (aset.status === 'Proses Perbaikan') stats.proses++;
                    stats.total++;

                    if (matchSta && matchSearch) {
                        const m = L.marker([aset.lat, aset.lng], { icon: createMarkerIcon(aset.kategori, aset.warna, aset.icon) });
                        m.bindPopup(`
                            <div class="flex flex-col w-full bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
                                <div class=\"relative h-44 overflow-hidden\">
                                    <img src=\"${aset.foto}\" class=\"w-full h-full object-cover\">
                                    <div class=\"absolute top-4 right-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full shadow-sm\">
                                        <span class=\"text-[10px] font-black text-indigo-600 uppercase tracking-tighter\">${aset.kategori}</span>
                                    </div>
                                </div>
                                <div class=\"p-5 flex flex-col gap-3\">
                                    <div>
                                        <h4 class=\"text-sm font-black text-gray-900 uppercase leading-tight\">${aset.nama}</h4>
                                        <p class=\"text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter leading-relaxed\">${aset.alamat}</p>
                                    </div>
                                    <div class=\"flex items-center gap-2\">
                                        <div class=\"px-3 py-1.5 rounded-xl border-2 flex items-center gap-2\" style=\"border-color: ${aset.warna}20; background: ${aset.warna}08\">
                                            <span class=\"w-2 h-2 rounded-full\" style=\"background: ${aset.warna}\"></span>
                                            <span class=\"text-[10px] font-black uppercase tracking-tighter\" style=\"color: ${aset.warna}\">${aset.status}</span>
                                        </div>
                                    </div>
                                    <div class=\"pt-3 border-t border-gray-50 flex gap-2\">
                                        <a href=\"/assets/${aset.id}\" class=\"flex-1 bg-indigo-600 text-white text-[10px] font-black py-2.5 rounded-xl text-center uppercase tracking-tighter hover:bg-indigo-700 transition-all\">Lihat Detail</a>
                                    </div>
                                </div>
                            </div>`);
                        markers.addLayer(m);
                    }
                }
            });

            document.getElementById('statTotalGlobal').innerText = stats.total;
            document.getElementById('statBaik').innerText = stats.baik;
            document.getElementById('statRusak').innerText = stats.rusak;
            document.getElementById('statKritis').innerText = stats.kritis;
            document.getElementById('statProses').innerText = stats.proses;
        };

        window.renderCategories = function() {
            const container = document.getElementById('listContent');
            document.getElementById('listBreadcrumb').classList.add('hidden');
            container.innerHTML = sidebarCats.map(cat => `
                <div onclick=\"selectCategory('${cat.kategori}')\" class=\"flex justify-between items-center p-3 hover:bg-white rounded-xl cursor-pointer transition-all border border-transparent hover:border-indigo-100 hover:shadow-sm group\">
                    <div class=\"flex items-center gap-3\">
                        <span class=\"cat-bullet\" style=\"background-color: ${cat.warna || '#cbd5e1'}\"></span>
                        <span class=\"text-[11px] font-black uppercase text-gray-700 group-hover:text-indigo-600 transition-colors\">${cat.kategori}</span>
                    </div>
                    <span class=\"text-[9px] font-bold text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full\">${cat.total_jenis} Jenis</span>
                </div>
            `).join('');
        };

        window.selectCategory = (catName) => {
            currentKat = catName;
            document.getElementById('statLabel').innerText = catName;
            document.getElementById('listBreadcrumb').classList.remove('hidden');
            document.getElementById('currentCategoryTitle').innerText = catName;
            
            const filtered = jenisStats.filter(j => j.kategori === catName);
            document.getElementById('listContent').innerHTML = filtered.map(j => `
                <div onclick=\"selectJenis('${j.jenis}')\" class=\"flex justify-between items-center p-3 hover:bg-white rounded-xl cursor-pointer transition-all border border-transparent hover:border-indigo-100\">
                    <span class=\"text-[11px] font-bold text-gray-600 uppercase\">${j.jenis}</span>
                    <span class=\"bg-gray-100 px-2 py-0.5 rounded text-[10px] font-black text-gray-900\">${j.total_titik}</span>
                </div>
            `).join('');
            
            renderMap(catName, 'all', currentSta);
        };

        window.selectJenis = (jenisName) => {
            currentJenis = jenisName;
            renderMap(currentKat, jenisName, currentSta);
        };

        window.resetToCategories = () => {
            currentKat = 'all';
            currentJenis = 'all';
            document.getElementById('statLabel').innerText = "Total Seluruh Aset";
            renderCategories();
            renderMap('all', 'all', currentSta);
        };

        window.triggerSearch = () => {
            searchQuery = document.getElementById('searchInput').value;
            renderMap();
        };

        window.filterByStatus = (status) => {
            if(currentSta === status) {
                currentSta = 'all';
                ['btnStatBaik', 'btnStatRusak', 'btnStatKritis', 'btnStatProses'].forEach(id => {
                    const btn = document.getElementById(id);
                    if(btn) btn.classList.remove('status-active');
                });
                document.getElementById('statusFilterTitle').innerText = "Kondisi Seluruh Aset";
            } else {
                currentSta = status;
                ['btnStatBaik', 'btnStatRusak', 'btnStatKritis', 'btnStatProses'].forEach(id => {
                    const btn = document.getElementById(id);
                    if(btn) btn.classList.remove('status-active');
                });
                
                document.getElementById('statusFilterTitle').innerText = "Filter: " + status;
                const activeBtnId = {
                    'Baik': 'btnStatBaik',
                    'Rusak': 'btnStatRusak',
                    'Kritis': 'btnStatKritis',
                    'Proses Perbaikan': 'btnStatProses'
                }[status];
                
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
            if (typeof resetMapView === 'function') resetMapView(); 
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