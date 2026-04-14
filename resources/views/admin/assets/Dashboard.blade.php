@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

{{-- CSS UNTUK CLUSTERING (ANGKA) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />

<style>
    /* Paksa semua elemen menggunakan Inter */
    html, body, div, span, h1, h2, h3, h4, p, a, input, button, label {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    }

    .no-scrollbar::-webkit-scrollbar { display: none; }
    
    /* Marker Style */
    .custom-div-icon {
        background: none !important;
        border: none !important;
    }

    /* CUSTOM STYLE UNTUK CLUSTER ANGKA */
    .marker-cluster-small { background-color: rgba(16, 185, 129, 0.6); }
    .marker-cluster-small div { background-color: rgba(16, 185, 129, 0.9); color: white; font-family: 'Inter'; font-weight: 900; }
    .marker-cluster-medium { background-color: rgba(245, 158, 11, 0.6); }
    .marker-cluster-medium div { background-color: rgba(245, 158, 11, 0.9); color: white; font-family: 'Inter'; font-weight: 900; }
    .marker-cluster-large { background-color: rgba(244, 63, 94, 0.6); }
    .marker-cluster-large div { background-color: rgba(244, 63, 94, 0.9); color: white; font-family: 'Inter'; font-weight: 900; }

    /* FIX FILTER & TOPBAR */
    .top-filter-bar {
        position: relative;
        z-index: 5000 !important;
        pointer-events: auto !important;
    }

    .filter-dropdown {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        padding: 0.5rem;
        min-width: 250px;
        z-index: 6000 !important;
    }
    
    .group:hover .filter-dropdown { 
        display: block !important; 
    }

    #map { z-index: 1 !important; cursor: crosshair; }

    /* STYLE KHUSUS POPUP CLOUD (INFO DI ATAS EMOJI) */
    .custom-leaflet-popup .leaflet-popup-content-wrapper {
        padding: 0 !important;
        overflow: hidden;
        border-radius: 16px !important;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1) !important;
        width: 300px !important; /* Lebar ditambah sedikit agar box kategori tidak sempit */
    }
    .custom-leaflet-popup .leaflet-popup-content {
        margin: 0 !important;
        width: 300px !important;
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
    .leaflet-control-zoom-in, .leaflet-control-zoom-out {
        background: white !important;
        color: #1e293b !important;
        font-weight: 800 !important;
        border: 1px solid #e2e8f0 !important;
        font-family: 'Inter', sans-serif !important;
    }

    .leaflet-control-layers {
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
        border: none !important;
        padding: 8px !important;
    }
    .leaflet-control-layers-base label {
        font-size: 10px;
        font-weight: 700 !important;
        text-transform: uppercase;
        color: #475569;
        cursor: pointer;
        font-family: 'Inter', sans-serif !important;
        margin-bottom: 4px;
    }
</style>

<div class="fixed inset-0 flex sm:ml-64 bg-slate-50 text-slate-900">
    
    {{-- SIDEBAR --}}
    <aside class="w-[320px] bg-white border-r border-slate-200 flex flex-col hidden md:flex z-[1050]">
        <div class="p-6 bg-slate-800 text-white">
            <h3 class="text-lg font-bold uppercase tracking-tight">Dashboard GIS</h3>
            <p class="text-[10px] text-slate-400 font-medium uppercase mt-1">Dishub Kab. Bandung Barat</p>
        </div>

        <div class="flex-1 p-5 flex flex-col overflow-y-auto no-scrollbar">
            {{-- Counter Total --}}
            <div class="bg-slate-50 p-6 rounded-xl border border-slate-200 text-center shadow-sm mb-4">
                <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">Total Aset Terdaftar</p>
                <h2 class="text-5xl font-black text-slate-800 tracking-tighter" id="totalAssetLabel">0</h2>
            </div>

            {{-- CHART STATISTIK --}}
            <div class="mb-6 p-4 bg-white border border-slate-100 rounded-xl shadow-sm">
                <p class="text-[11px] font-bold text-slate-500 uppercase mb-3 text-center tracking-widest">Persentase Kondisi</p>
                <div class="h-40 relative">
                    <canvas id="conditionChart"></canvas>
                </div>
            </div>

            {{-- Kondisi dengan Persentase --}}
            <div class="space-y-2 mb-6">
                <p class="text-[11px] font-bold text-slate-500 uppercase ml-1">Kondisi Aset</p>
                @foreach(['Baik' => '#16a34a', 'Proses Perbaikan' => '#3b82f6', 'Rusak' => '#fbbf24', 'Kritis' => '#dc2626'] as $st => $hex)
                <div class="bg-white p-3 rounded-lg border border-slate-200 flex items-center justify-between shadow-sm hover:border-slate-300 transition-colors">
                    <div class="flex items-center gap-3">
                        <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $hex }}"></span>
                        <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wide">{{ $st == 'Proses Perbaikan' ? 'Proses' : $st }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-black sidebar-status-count text-slate-900" data-status="{{ $st }}">0</span>
                        <span class="text-[9px] font-bold text-slate-400 block sidebar-status-pct" data-status-pct="{{ $st }}">0%</span>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Kategori --}}
            <div class="space-y-1 pt-4 border-t border-slate-100">
                <p class="text-[11px] font-bold text-slate-500 uppercase ml-1 mb-2">Kategori</p>
                @foreach([
                    'Penerangan Jalan Umum (PJU)' => 'emerald',
                    'Perlengkapan Jalan' => 'rose',
                    'Fasilitas Lalu Lintas' => 'blue',
                    'Pengendalian & Pengawasan' => 'amber',
                    'Prasarana Transportasi' => 'slate'
                ] as $name => $color)
                <div class="flex justify-between items-center py-2 px-3 bg-slate-50/50 rounded-md border border-transparent hover:border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-{{ $color }}-500 rounded-full"></span>
                        <span class="text-[10px] font-bold text-slate-600 uppercase tracking-tight">{{ $name }}</span>
                    </div>
                    <span class="text-[11px] font-black sidebar-kat-count" data-katname="{{ $name }}">0</span>
                </div>
                @endforeach
            </div>

            <div class="mt-auto pt-10 pb-6">
                <a href="{{ route('assets.create') }}" class="w-full flex items-center justify-center gap-2 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold uppercase shadow-lg shadow-blue-100 transition-all active:scale-95">
                    <span class="text-lg">+</span> Input Aset Baru
                </a>
            </div>
        </div>
    </aside>

    <div class="flex-1 relative flex flex-col overflow-hidden">
        {{-- TOPBAR FILTER --}}
        <div class="bg-white border-b border-slate-200 p-3 flex items-center justify-between top-filter-bar">
            
            <div class="flex items-center gap-1.5">
                {{-- Search Area --}}
                <div class="flex items-center bg-slate-100 rounded-lg border-2 border-transparent focus-within:border-blue-500 focus-within:bg-white transition-all overflow-hidden group">
                    <div class="pl-3 py-2 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="assetSearch" placeholder="Cari Nama, ID, Wilayah..." class="bg-transparent border-none py-2 px-2 text-[11px] w-56 focus:ring-0 outline-none font-medium">
                    <button onclick="renderMarkers()" class="bg-blue-600 hover:bg-blue-700 text-white text-[10px] font-black uppercase px-4 py-2 transition-all active:scale-95">
                        Cari
                    </button>
                </div>

                {{-- Filter Status --}}
                <div class="relative group">
                    <button class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase flex items-center gap-1.5 hover:bg-slate-50">Status <small class="text-slate-400">▼</small></button>
                    <div class="filter-dropdown w-48 shadow-2xl border-slate-200" id="dropStatus">
                        <label class="flex items-center gap-3 p-2 border-b border-slate-100 hover:bg-blue-50 cursor-pointer rounded transition-colors">
                            <input type="checkbox" id="selectAllStatus" checked class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                            <span class="text-[10px] font-black text-blue-600 uppercase">Semua Status</span>
                        </label>
                        @foreach(['Baik', 'Proses Perbaikan', 'Rusak', 'Kritis'] as $s)
                        <label class="flex items-center gap-3 p-2 hover:bg-slate-50 cursor-pointer rounded transition-colors">
                            <input type="checkbox" name="fStatus" value="{{ $s }}" checked class="filter-checkbox w-4 h-4 rounded text-slate-800 focus:ring-slate-400">
                            <span class="text-[10px] font-bold text-slate-700 uppercase">{{ $s == 'Proses Perbaikan' ? 'Proses' : $s }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Filter Kategori --}}
                <div class="relative group">
                    <button class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase flex items-center gap-1.5 hover:bg-slate-50">Kategori <small class="text-slate-400">▼</small></button>
                    <div class="filter-dropdown w-72 shadow-2xl border-slate-200" id="dropKategori"></div>
                </div>

                {{-- Filter Jenis --}}
                <div class="relative group">
                    <button class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-[10px] font-bold uppercase flex items-center gap-1.5 hover:bg-slate-50">Jenis Aset <small class="text-slate-400">▼</small></button>
                    <div class="filter-dropdown w-72 max-h-64 overflow-y-auto no-scrollbar shadow-2xl border-slate-200" id="dropJenis"></div>
                </div>

                <button onclick="resetFilters()" class="text-[9px] font-bold uppercase text-rose-500 hover:text-rose-700 hover:underline px-4 transition-colors">Reset Filter</button>
            </div>

            {{-- REFRESH VIEW BUTTON --}}
            <button onclick="refreshView()" class="p-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-all active:scale-90 shadow-sm group" title="Refresh Tampilan Default">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 group-hover:rotate-180 transition-transform duration-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>

        {{-- MAP AREA --}}
        <main class="flex-1 relative z-0">
            <div id="map" class="w-full h-full bg-slate-900"></div>
            
            {{-- DETAIL CARD LAMA - SEKARANG JADI FALLBACK ATAU BISA DIHAPUS JIKA MAU FULL POPUP --}}
            <div id="detailCard" class="absolute bottom-8 left-8 z-[1200] w-72 bg-white rounded-2xl shadow-2xl overflow-hidden hidden transform transition-all duration-300 border border-slate-200">
            </div>
        </main>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

{{-- JS UNTUK CLUSTERING --}}
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

{{-- 1. PANGGIL LOGIC ASSET LAYER --}}
<script src="{{ asset('js/asset-layer-logic.js') }}"></script>

{{-- 2. PANGGIL POLYGON LAYERS --}}
<script src="{{ asset('js/polygon-layers.js') }}"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let conditionChart = null; 

    const statusColors = { 
        'Baik': '#16a34a', 
        'Rusak': '#fbbf24', 
        'Kritis': '#dc2626', 
        'Proses Perbaikan': '#3b82f6' 
    };

    // 1. DATA PREP
    const assetsRaw = @json($assets ?? []);
    const assets = assetsRaw.map(a => ({
        ...a,
        lat: parseFloat(a.lat),
        lng: parseFloat(a.lng),
        kategori: (a.kategori || 'Lainnya').trim(),
        jenis: (a.jenis || 'Lainnya').trim(),
        status: (a.status || 'Baik').trim(),
        icon_marker: a.icon_marker || null,
        id_asset: a.id_asset || '',
        alamat: a.alamat || ''
    }));

    // 2. MAP INIT
    const mapConfig = @json($mapConfig ?? ['center' => [-6.8431, 107.4912], 'defaultZoom' => 12]);
    const defaultCenter = [mapConfig.center[0], mapConfig.center[1]];
    const defaultZoom = mapConfig.defaultZoom;

    const darkMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 20 });
    const streetMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { maxZoom: 20 });
    const googleRoadmap = L.tileLayer("https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}", { maxZoom: 20 });
    const googleSatelite = L.tileLayer("https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}", { maxZoom: 22 });

    const map = L.map('map', { 
        zoomControl: true, 
        attributionControl: false,
        layers: [darkMap] 
    }).setView(defaultCenter, defaultZoom);

    if (typeof initPolygonLayers === 'function') {
        initPolygonLayers(map);
    }

    const baseMaps = {
        "<span style='font-family:Inter; font-weight:700;'>🌑 DARK MODE</span>": darkMap,
        "<span style='font-family:Inter; font-weight:700;'>⚪ STREETS</span>": streetMap,
        "<span style='font-family:Inter; font-weight:700;'>🗺️ GOOGLE ROADMAP</span>": googleRoadmap,
        "<span style='font-family:Inter; font-weight:700;'>🛰️ SATELLITE</span>": googleSatelite
    };
    L.control.layers(baseMaps, null, { position: 'topright' }).addTo(map);

    const markerClusterGroup = L.markerClusterGroup({
        disableClusteringAtZoom: 13, 
        maxClusterRadius: 130,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true
    }).addTo(map);

    // 3. CHART LOGIC
    function updateChart(counts) {
        const canvas = document.getElementById('conditionChart');
        if(!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const data = {
            labels: ['Baik', 'Proses', 'Rusak', 'Kritis'],
            datasets: [{
                data: [counts['Baik'], counts['Proses Perbaikan'], counts['Rusak'], counts['Kritis']],
                backgroundColor: [statusColors['Baik'], statusColors['Proses Perbaikan'], statusColors['Rusak'], statusColors['Kritis']],
                borderWidth: 0,
                hoverOffset: 12
            }]
        };

        if (conditionChart) {
            conditionChart.data = data;
            conditionChart.update();
        } else {
            conditionChart = new Chart(ctx, {
                type: 'doughnut',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { 
                        legend: { display: false },
                        tooltip: { 
                            bodyFont: { family: 'Inter', weight: 'bold' },
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${context.label}: ${value} (${pct}%)`;
                                }
                            }
                        }
                    },
                    cutout: '75%'
                }
            });
        }
    }

    // 4. FILTER & SEARCH SYSTEM
    function initFilters() {
        const kats = [...new Set(assets.map(a => a.kategori))].sort();
        const dropKat = document.getElementById('dropKategori');
        if(!dropKat) return;
        
        let katHtml = `
            <label class="flex items-center gap-3 p-2 border-b border-slate-100 hover:bg-blue-50 cursor-pointer rounded">
                <input type="checkbox" id="selectAllKat" checked class="w-4 h-4 rounded text-blue-600">
                <span class="text-[10px] font-black text-blue-600 uppercase">Semua Kategori</span>
            </label>
        `;
        
        katHtml += kats.map(k => `
            <label class="flex items-center justify-between p-2 hover:bg-slate-50 cursor-pointer rounded transition-colors">
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="fKat" value="${k}" checked class="filter-checkbox w-4 h-4 rounded">
                    <span class="text-[11px] font-bold uppercase text-slate-700">${k}</span>
                </div>
            </label>
        `).join('');
        
        dropKat.innerHTML = katHtml;

        const selectAllStatus = document.getElementById('selectAllStatus');
        if(selectAllStatus) {
            selectAllStatus.onclick = function() {
                document.querySelectorAll('input[name="fStatus"]').forEach(c => c.checked = this.checked);
                renderMarkers();
            };
        }

        const selectAllKat = document.getElementById('selectAllKat');
        if(selectAllKat) {
            selectAllKat.onclick = function() {
                document.querySelectorAll('input[name="fKat"]').forEach(c => c.checked = this.checked);
                updateJenisFilter();
            };
        }

        updateJenisFilter();
    }

    window.updateJenisFilter = function() {
        const selKats = Array.from(document.querySelectorAll('input[name="fKat"]:checked')).map(i => i.value);
        const filteredJenis = [...new Set(assets.filter(a => selKats.includes(a.kategori)).map(a => a.jenis))].sort();
        const dropJenis = document.getElementById('dropJenis');
        if(!dropJenis) return;

        let jenHtml = `
            <label class="flex items-center gap-3 p-2 border-b border-slate-100 hover:bg-blue-50 cursor-pointer rounded">
                <input type="checkbox" id="selectAllJenis" checked class="w-4 h-4 rounded text-blue-600">
                <span class="text-[10px] font-black text-blue-600 uppercase">Semua Jenis Aset</span>
            </label>
        `;

        jenHtml += filteredJenis.map(j => `
            <label class="flex items-center justify-between p-2 hover:bg-slate-50 cursor-pointer rounded transition-colors">
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="fJen" value="${j}" checked class="filter-checkbox w-4 h-4 rounded text-slate-700">
                    <span class="text-[11px] font-bold uppercase text-slate-700">${j}</span>
                </div>
            </label>
        `).join('');
        
        dropJenis.innerHTML = jenHtml;

        const selectAllJenis = document.getElementById('selectAllJenis');
        if(selectAllJenis) {
            selectAllJenis.onclick = function() {
                document.querySelectorAll('input[name="fJen"]').forEach(c => c.checked = this.checked);
                renderMarkers();
            };
        }

        renderMarkers();
    }

    // FUNGSI RENDER UTAMA DENGAN INTEGRASI POPUP ATAS EMOJI
    window.renderMarkers = function() {
        markerClusterGroup.clearLayers();
        
        const searchInput = document.getElementById('assetSearch');
        const search = searchInput ? searchInput.value.toLowerCase().trim() : '';
        
        const selStatus = Array.from(document.querySelectorAll('input[name="fStatus"]:checked')).map(i => i.value);
        const selKats = Array.from(document.querySelectorAll('input[name="fKat"]:checked')).map(i => i.value);
        const selJenis = Array.from(document.querySelectorAll('input[name="fJen"]:checked')).map(i => i.value);

        const filtered = assets.filter(a => {
            const matchesSearch = search === '' || 
                                (a.nama && a.nama.toLowerCase().includes(search)) || 
                                (a.id_asset && a.id_asset.toLowerCase().includes(search)) || 
                                (a.alamat && a.alamat.toLowerCase().includes(search));
            
            const matchesStatus = selStatus.includes(a.status);
            const matchesKat = selKats.includes(a.kategori);
            const matchesJen = (selJenis.length === 0) ? true : selJenis.includes(a.jenis);
            
            return matchesSearch && matchesStatus && matchesKat && matchesJen;
        });

        // Update UI Sidebar
        const totalFiltered = filtered.length;
        const counts = { 'Baik': 0, 'Proses Perbaikan': 0, 'Rusak': 0, 'Kritis': 0 };
        filtered.forEach(a => { if(counts.hasOwnProperty(a.status)) counts[a.status]++; });

        if(document.getElementById('totalAssetLabel')) document.getElementById('totalAssetLabel').innerText = totalFiltered;
        
        document.querySelectorAll('.sidebar-status-count').forEach(el => {
            const count = counts[el.dataset.status] || 0;
            el.innerText = count;
            const pctEl = document.querySelector(`[data-status-pct="${el.dataset.status}"]`);
            if (pctEl) pctEl.innerText = (totalFiltered > 0 ? ((count / totalFiltered) * 100).toFixed(1) : 0) + '%';
        });
        
        document.querySelectorAll('.sidebar-kat-count').forEach(el => {
            el.innerText = filtered.filter(a => a.kategori === el.dataset.katname).length;
        });

        updateChart(counts);

        // Rendering Markers to Map
        filtered.forEach(a => {
            if (!a.lat || !a.lng) return;

            const statusColor = statusColors[a.status] || '#94a3b8';
            const icon = createMarkerIcon(a.kategori, statusColor, a.icon_marker);
            const marker = L.marker([a.lat, a.lng], { icon });

            // LOGIKA IMAGE PATH
            let fotoPath = a.foto ? a.foto.replace('public/', '') : '';
            if (fotoPath && !fotoPath.includes('assets/')) fotoPath = 'assets/' + fotoPath;
            const fullImgUrl = a.foto ? `/storage/${fotoPath}` : 'https://placehold.co/400x200?text=No+Image';

            // HTML POPUP (FOKUS UPDATE: KONTRAS TOMBOL EDIT UNTUK AKSESIBILITAS)
            const popupHtml = `
                <div class="bg-white overflow-hidden" style="font-family: 'Inter', sans-serif;">
                    <div class="relative h-36 bg-slate-200">
                        <img src="${fullImgUrl}" class="w-full h-full object-cover">
                        <div class="absolute top-3 right-3 flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-black uppercase text-white shadow-lg" style="background-color: ${statusColor}">
                            ${a.status === 'Kritis' ? '<span class="text-[10px]">✕</span>' : ''}
                            ${a.status === 'Proses Perbaikan' ? 'PROSES' : a.status}
                        </div>
                    </div>
                    <div class="p-4">
                        <h4 class="font-black text-[13px] uppercase text-slate-800 leading-tight mb-1">${a.nama}</h4>
                        
                        <div class="flex items-start gap-1 mb-4">
                            <span class="text-indigo-500 mt-0.5">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                                </svg>
                            </span>
                            <p class="text-[9px] text-slate-400 italic font-medium leading-tight">${a.alamat || 'Alamat tidak tersedia'}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-2 mb-4">
                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100 text-center">
                                <p class="text-[7px] font-black text-slate-400 uppercase tracking-tighter mb-0.5">KATEGORI</p>
                                <p class="text-[9px] font-bold text-slate-700 uppercase leading-none">${a.kategori}</p>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-lg border border-slate-100 text-center">
                                <p class="text-[7px] font-black text-slate-400 uppercase tracking-tighter mb-0.5">JENIS</p>
                                <p class="text-[9px] font-bold text-slate-700 uppercase leading-none">${a.jenis}</p>
                            </div>
                        </div>

                        <div class="pt-1">
                            <a href="/admin/assets/${a.id}/edit" class="block w-full text-center bg-amber-400 hover:bg-amber-500 text-slate-900 py-3 rounded-xl text-[11px] font-black uppercase tracking-widest transition-all active:scale-95 shadow-md shadow-amber-100 border border-amber-500/20">
                                Edit Data 
                            </a>
                        </div>
                    </div>
                </div>
            `;
            
            // BIND POPUP DENGAN OFFSET AGAR MELAYANG DI ATAS EMOJI
            marker.bindPopup(popupHtml, {
                offset: L.point(0, -15), 
                className: 'custom-leaflet-popup'
            });

            marker.on('click', () => {
                map.flyTo([a.lat, a.lng], 18, { duration: 1 });
            });

            markerClusterGroup.addLayer(marker);
        });

        // Fit Bounds jika mencari sesuatu
        if (search !== '' && filtered.length > 0) {
            if (filtered.length === 1) {
                 map.flyTo([filtered[0].lat, filtered[0].lng], 17);
            } else {
                 map.fitBounds(markerClusterGroup.getBounds(), { padding: [50, 50], maxZoom: 15 });
            }
        }
    }

    window.refreshView = function() {
        map.setView(defaultCenter, defaultZoom, { animate: true });
        map.closePopup();
        resetFilters();
    }

    // Event Listener Search
    const assetSearch = document.getElementById('assetSearch');
    if(assetSearch) {
        assetSearch.addEventListener('keypress', (e) => { if (e.key === 'Enter') renderMarkers(); });
        let timeout = null;
        assetSearch.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => renderMarkers(), 500);
        });
    }

    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('filter-checkbox')) {
            if (e.target.name === 'fKat') updateJenisFilter(); else renderMarkers();
        }
    });

    window.resetFilters = function() {
        document.querySelectorAll('input[type="checkbox"]').forEach(c => c.checked = true);
        if(document.getElementById('assetSearch')) document.getElementById('assetSearch').value = '';
        updateJenisFilter();
    };

    // Propagasi Fix
    ['dropStatus', 'dropKategori', 'dropJenis', 'assetSearch'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { 
            L.DomEvent.disableClickPropagation(el); 
            L.DomEvent.disableScrollPropagation(el); 
        }
    }); 

    initFilters();
});
</script>
@endpush