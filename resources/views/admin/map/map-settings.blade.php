@extends('layouts.app')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>
    body { 
        font-family: 'Inter', sans-serif; 
        background-color: #F8FAFC;
    }
    
    /* Custom Scrollbar */
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* Fix: Supaya klik tembus ke map meskipun ada polygon */
    .leaflet-interactive {
        pointer-events: none !important;
    }

    /* Modern Layer Selector Styling */
    .custom-layer-control {
        position: absolute;
        top: 25px;
        right: 25px;
        z-index: 1000;
        display: flex;
        flex-direction: row-reverse;
        align-items: flex-start;
        gap: 12px;
    }

    .layer-button {
        background: white;
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255,255,255,0.8);
    }

    .layer-panel {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
        padding: 10px;
        border-radius: 20px;
        box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
        border: 1px solid rgba(255,255,255,0.5);
        display: flex;
        flex-direction: column;
        gap: 4px;
        width: 180px;
        opacity: 0;
        visibility: hidden;
        transform: translateX(20px);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .custom-layer-control:hover .layer-panel {
        opacity: 1;
        visibility: visible;
        transform: translateX(0);
    }

    .custom-layer-control:hover .layer-button {
        background: #4F46E5;
        transform: rotate(90deg);
    }

    .custom-layer-control:hover .layer-button svg {
        stroke: white;
    }

    .layer-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        cursor: pointer;
        border-radius: 12px;
        transition: all 0.2s;
    }

    .layer-option:hover { background: rgba(79, 70, 229, 0.08); }
    .layer-option span { font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; }
    .layer-option input[type="radio"] { accent-color: #4F46E5; cursor: pointer; }

    /* Zoom Control Customization */
    .leaflet-control-zoom {
        border: none !important;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1) !important;
    }
    .leaflet-control-zoom-in, .leaflet-control-zoom-out {
        background: white !important;
        color: #4F46E5 !important;
        border: 1px solid #f1f5f9 !important;
        width: 40px !important;
        height: 40px !important;
        line-height: 40px !important;
        font-weight: bold !important;
        border-radius: 12px !important;
        margin-bottom: 5px !important;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    /* Floating Legend Checkbox */
    .map-legend-floating {
        position: absolute;
        bottom: 25px;
        left: 25px;
        z-index: 1000;
        background: white;
        padding: 12px 18px;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        border: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
    }

    .legend-item input {
        accent-color: #4F46E5;
        width: 16px;
        height: 16px;
    }

    .legend-item span {
        font-size: 11px;
        font-weight: 800;
        color: #1e293b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<div class="h-screen bg-[#F8FAFC] sm:ml-64 flex flex-col transition-all duration-300">
    <div class="p-6 md:px-10 md:pt-10 md:pb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="space-y-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="w-8 h-1 bg-indigo-600 rounded-full"></span>
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.3em]">System Engine</span>
                </div>
                <h1 class="text-4xl font-black text-slate-900 tracking-tight uppercase">
                    Konfigurasi <span class="text-indigo-600">Map</span>
                </h1>
                <p class="text-slate-400 font-semibold text-[11px] tracking-widest uppercase opacity-80 flex items-center gap-2">
                    <span class="inline-block w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    Pengaturan Default Center & Zoom Level — Dishub KBB
                </p>
            </div>
            
            <div class="flex items-center gap-4 bg-white p-2 rounded-3xl shadow-sm border border-slate-100">
                <a href="{{ route('dashboard') }}" class="px-5 py-3 text-slate-500 rounded-2xl font-bold text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Dashboard
                </a>
                <button onclick="simpanPengaturan()" id="btnSimpan" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all active:scale-95 flex items-center gap-2">
                    Simpan Perubahan 💾
                </button>
            </div>
        </div>
    </div>

    <div class="flex-1 px-6 md:px-10 pb-10">
        <div class="grid grid-cols-12 gap-8 h-full">
            
            <div class="col-span-12 lg:col-span-8 h-full min-h-[600px]">
                <div class="group relative bg-white p-2 rounded-[3rem] shadow-[0_20px_50px_rgba(0,0,0,0.04)] border border-slate-100 h-full overflow-hidden transition-all">
                    
                    <div class="custom-layer-control">
                        <div class="layer-button">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 12l10 5 10-5M2 17l10 5 10-5"></path></svg>
                        </div>
                        <div class="layer-panel">
                            <div class="layer-option" onclick="switchBaseLayer('street')">
                                <input type="radio" name="map_style" id="radio-street" checked>
                                <span>🛣️ Streets</span>
                            </div>
                            <div class="layer-option" onclick="switchBaseLayer('satellite')">
                                <input type="radio" name="map_style" id="radio-satellite">
                                <span>🛰️ Satellite View</span>
                            </div>
                            <div class="layer-option" onclick="switchBaseLayer('dark')">
                                <input type="radio" name="map_style" id="radio-dark">
                                <span>🌑 Dark Mode</span>
                            </div>
                        </div>
                    </div>

                    <div class="map-legend-floating">
                        <label class="legend-item">
                            <input type="checkbox" id="chk-kecamatan" checked onchange="togglePolygonLayer('kecamatan', this.checked)">
                            <span style="color: #e11d48;">🔴 Batas Kecamatan</span>
                        </label>
                        <label class="legend-item">
                            <input type="checkbox" id="chk-desa" onchange="togglePolygonLayer('desa', this.checked)">
                            <span style="color: #2563eb;">🔵 Batas Desa</span>
                        </label>
                    </div>

                    <div id="map-aset" class="w-full h-full rounded-[2.5rem] z-0"></div>
                    
                    <div class="absolute bottom-8 right-8 z-[1000] glass-card p-4 rounded-2xl shadow-2xl max-w-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-indigo-600 rounded-xl flex items-center justify-center text-white text-xs">📍</div>
                            <p class="text-[9px] font-bold text-slate-700 leading-tight">Klik peta atau geser marker untuk mengubah koordinat pusat.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-4 space-y-6 overflow-y-auto custom-scroll pr-2">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 relative overflow-hidden">
                    <label class="block text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em] mb-4 ml-1">Cari Wilayah Administrasi</label>
                    <div class="relative group">
                        <input type="text" id="geo-search" placeholder="Contoh: Lembang, Padalarang..." class="modern-input w-full bg-slate-50 border-2 border-slate-100 rounded-2xl py-4 px-6 pr-16 text-sm font-bold focus:border-indigo-500 focus:bg-white transition-all outline-none">
                        <button id="btn-search-loc" class="absolute right-2 top-2 bg-slate-900 text-white w-12 h-12 rounded-2xl hover:bg-indigo-600 transition-all shadow-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </button>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 space-y-5">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Live Properties</h3>
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-600 text-[9px] font-black rounded-lg uppercase">Syncing</span>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <span class="block text-[9px] font-black text-slate-400 uppercase mb-1 tracking-tighter">Latitude</span>
                            <input type="text" id="input-lat" value="{{ $mapConfig['center'][0] }}" readonly class="w-full bg-transparent font-black text-slate-800 text-sm outline-none">
                        </div>
                        <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <span class="block text-[9px] font-black text-slate-400 uppercase mb-1 tracking-tighter">Longitude</span>
                            <input type="text" id="input-lng" value="{{ $mapConfig['center'][1] }}" readonly class="w-full bg-transparent font-black text-slate-800 text-sm outline-none">
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1">Zoom Level (Focus)</label>
                        <div class="relative">
                            <select id="input-zoom" class="w-full px-6 py-4 bg-slate-900 border-none rounded-2xl outline-none font-bold text-indigo-400 appearance-none cursor-pointer focus:ring-4 focus:ring-indigo-500/10 transition-all">
                                <option value="11" {{ $mapConfig['defaultZoom'] == 11 ? 'selected' : '' }}>🗺️ 11 - DEFAULT (LUAS KBB)</option>
                                <option value="13" {{ $mapConfig['defaultZoom'] == 13 ? 'selected' : '' }}>🏢 13 - KOTA</option>
                                <option value="15" {{ $mapConfig['defaultZoom'] == 15 ? 'selected' : '' }}>🏘️ 15 - DETAIL</option>
                                <option value="18" {{ $mapConfig['defaultZoom'] == 18 ? 'selected' : '' }}>📍 18 - JALAN</option>
                            </select>
                            <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-indigo-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>
                    </div>

                    <div id="statusBox" class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl hidden animate-pulse">
                        <p class="text-[10px] font-black text-emerald-800 uppercase text-center">Titik Baru Terdeteksi!</p>
                    </div>
                </div>

                <div class="p-6 bg-indigo-600 rounded-[2.5rem] text-white shadow-xl relative overflow-hidden group">
                    <div class="absolute -right-2 -bottom-2 text-6xl opacity-10 group-hover:rotate-12 transition-transform">⚙️</div>
                    <h4 class="text-[10px] font-black uppercase tracking-widest mb-3">Panduan Cepat</h4>
                    <ul class="text-[10px] font-medium opacity-90 space-y-2 relative z-10">
                        <li>• Klik lokasi di peta untuk memindahkan titik pusat.</li>
                        <li>• Gunakan tombol + / - untuk mengatur tingkat zoom.</li>
                        <li>• Pastikan klik "Simpan" untuk menerapkan perubahan.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/polygon-layers.js') }}"></script>

<script>
    const MAP_SETTINGS = {
        center: [{{ $mapConfig['center'][0] }}, {{ $mapConfig['center'][1] }}], 
        defaultZoom: {{ $mapConfig['defaultZoom'] }}, 
        detailZoom: {{ $mapConfig['detailZoom'] }}
    };

    let map, marker;
    let baseLayers = {};

    document.addEventListener("DOMContentLoaded", function() {
        // Base Layers
        baseLayers.street = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 20, attribution: '©OSM'
        });

        baseLayers.satellite = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
            maxZoom: 20, attribution: '©Google'
        });

        baseLayers.dark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 20, attribution: '©Carto'
        });

        // Setup Map
        map = L.map('map-aset', {
            zoomControl: false,
            attributionControl: false,
            layers: [baseLayers.street]
        }).setView(MAP_SETTINGS.center, MAP_SETTINGS.defaultZoom);

        L.control.zoom({ position: 'topleft' }).addTo(map);

        // Polygon Layers Init
        if (typeof initPolygonLayers === "function") {
            initPolygonLayers(map);
            // Matikan Desa by default sesuai ss
            setTimeout(() => {
                if(window.polygonLayers && window.polygonLayers.desa) {
                    map.removeLayer(window.polygonLayers.desa);
                    document.getElementById('chk-desa').checked = false;
                }
            }, 500);
        }

        // Custom Marker
        const adminIcon = L.divIcon({
            className: 'custom-div-icon',
            html: "<div style='background-color:#4F46E5; width:24px; height:24px; border-radius:50%; border:4px solid white; box-shadow:0 0 20px rgba(79,70,229,0.5);'></div>",
            iconSize: [24, 24], iconAnchor: [12, 12]
        });

        marker = L.marker(MAP_SETTINGS.center, { draggable: true, icon: adminIcon }).addTo(map);

        // Interaction Logic - Klik untuk pindah titik
        map.on('click', function(e) {
            updateCoordinate(e.latlng.lat, e.latlng.lng);
            marker.setLatLng(e.latlng);
            map.flyTo(e.latlng, 15); 
            document.getElementById('input-zoom').value = "15";
        });

        marker.on('dragend', function(event) {
            const position = marker.getLatLng();
            updateCoordinate(position.lat, position.lng);
            map.panTo(position);
        });

        document.getElementById('input-zoom').addEventListener('change', function() {
            map.setZoom(parseInt(this.value));
        });

        // Search Logic
        const btnSearch = document.getElementById('btn-search-loc');
        const inputSearch = document.getElementById('geo-search');
        btnSearch.addEventListener('click', function() {
            const query = inputSearch.value;
            if (!query) return;
            const finalQuery = query + ", Kabupaten Bandung Barat, Jawa Barat";
            btnSearch.innerHTML = "⏳";
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(finalQuery)}&limit=1`)
                .then(r => r.json())
                .then(data => {
                    if (data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);
                        map.flyTo([lat, lon], 15);
                        marker.setLatLng([lat, lon]);
                        updateCoordinate(lat, lon);
                        document.getElementById('input-zoom').value = "15";
                    }
                })
                .finally(() => { 
                    btnSearch.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>'; 
                });
        });
    });

    function togglePolygonLayer(type, isChecked) {
        if (window.polygonLayers && window.polygonLayers[type]) {
            if (isChecked) map.addLayer(window.polygonLayers[type]);
            else map.removeLayer(window.polygonLayers[type]);
        }
    }

    function switchBaseLayer(type) {
        Object.values(baseLayers).forEach(l => map.removeLayer(l));
        map.addLayer(baseLayers[type]);
        document.getElementById(`radio-${type}`).checked = true;
    }

    function updateCoordinate(lat, lng) {
        document.getElementById('input-lat').value = lat.toFixed(6);
        document.getElementById('input-lng').value = lng.toFixed(6);
        document.getElementById('statusBox').classList.remove('hidden');
    }

    function simpanPengaturan() {
        const lat = document.getElementById('input-lat').value;
        const lng = document.getElementById('input-lng').value;
        const zoom = document.getElementById('input-zoom').value;
        const btn = document.getElementById('btnSimpan');
        const originalText = btn.innerHTML;
        btn.innerHTML = "⌛ Menyimpan...";
        btn.disabled = true;

        fetch("{{ route('admin.map.settings.update') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ latitude: lat, longitude: lng, zoom: zoom })
        })
        .then(r => r.json())
        .then(data => {
            if(data.status === 'success') {
                alert("✅ Konfigurasi Berhasil Disimpan!");
                location.reload();
            }
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
@endsection