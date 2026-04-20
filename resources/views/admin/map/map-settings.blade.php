@extends('layouts.app')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    /* Custom Scrollbar */
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    .leaflet-interactive { pointer-events: auto !important; }

    .custom-layer-control {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 999;
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

    .custom-layer-control:hover .layer-panel { opacity: 1; visibility: visible; transform: translateX(0); }
    .custom-layer-control:hover .layer-button { background: #4F46E5; transform: rotate(90deg); }
    .custom-layer-control:hover .layer-button svg { stroke: white; }

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

    .leaflet-control-zoom { border: none !important; margin-left: 20px !important; margin-top: 20px !important; }
    .leaflet-control-zoom-in, .leaflet-control-zoom-out {
        background: white !important;
        color: #4F46E5 !important;
        width: 40px !important;
        height: 40px !important;
        line-height: 40px !important;
        border-radius: 12px !important;
        margin-bottom: 5px !important;
    }

    .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.5); }

    .map-legend-floating {
        position: absolute;
        bottom: 25px;
        left: 25px;
        z-index: 999;
        background: white;
        padding: 12px 18px;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .legend-item { display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .legend-item span { font-size: 11px; font-weight: 800; color: #1e293b; text-transform: uppercase; }
</style>

{{-- Hapus sm:ml-64 karena sudah memakai flex di app.blade --}}
<div class="h-full bg-[#F8FAFC] flex flex-col transition-all duration-300 overflow-hidden">
    
    <div class="p-6 md:px-10 pt-10 pb-6 flex-none">
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
                    Pengaturan Default Center & Zoom Level — LINTAS KBB
                </p>
            </div>
            
            <div class="flex items-center gap-4 bg-white p-2 rounded-3xl shadow-sm border border-slate-100">
                <a href="{{ route('dashboard') }}" class="px-5 py-3 text-slate-500 rounded-2xl font-bold text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Dashboard
                </a>
                <button onclick="simpanPengaturan()" id="btnSimpan" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] shadow-xl shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-95 flex items-center gap-2">
                    Simpan Perubahan 💾
                </button>
            </div>
        </div>
    </div>

    <div class="flex-1 px-6 md:px-10 pb-10 overflow-y-auto custom-scroll">
        <div class="grid grid-cols-12 gap-8 h-full min-h-[500px]">
            
            <div class="col-span-12 lg:col-span-8 relative h-full">
                <div class="group relative bg-white p-2 rounded-[3rem] shadow-sm border border-slate-100 h-full overflow-hidden min-h-[450px]">
                    
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

                    <div id="map-aset" class="w-full h-full rounded-[2.5rem] relative z-0"></div>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-4 space-y-6">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                    <label class="block text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em] mb-4">Cari Wilayah Administrasi</label>
                    <div class="relative group">
                        <input type="text" id="geo-search" placeholder="Contoh: Lembang..." class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl py-4 px-6 text-sm font-bold focus:border-indigo-500 outline-none">
                        <button id="btn-search-loc" class="absolute right-2 top-2 bg-slate-900 text-white w-12 h-12 rounded-2xl hover:bg-indigo-600 transition-all flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </button>
                    </div>
                </div>

                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 space-y-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <span class="block text-[9px] font-black text-slate-400 uppercase mb-1">Latitude</span>
                            <input type="text" id="input-lat" value="{{ $mapConfig['center'][0] }}" readonly class="w-full bg-transparent font-black text-slate-800 text-sm outline-none">
                        </div>
                        <div class="bg-slate-50/80 p-4 rounded-2xl border border-slate-100">
                            <span class="block text-[9px] font-black text-slate-400 uppercase mb-1">Longitude</span>
                            <input type="text" id="input-lng" value="{{ $mapConfig['center'][1] }}" readonly class="w-full bg-transparent font-black text-slate-800 text-sm outline-none">
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Zoom Level</label>
                        <select id="input-zoom" class="w-full px-6 py-4 bg-slate-900 rounded-2xl outline-none font-bold text-indigo-400 appearance-none cursor-pointer">
                            <option value="11" {{ $mapConfig['defaultZoom'] == 11 ? 'selected' : '' }}>🗺️ 11 - DEFAULT (LUAS KBB)</option>
                            <option value="13" {{ $mapConfig['defaultZoom'] == 13 ? 'selected' : '' }}>🏢 13 - KOTA</option>
                            <option value="15" {{ $mapConfig['defaultZoom'] == 15 ? 'selected' : '' }}>🏘️ 15 - DETAIL</option>
                            <option value="18" {{ $mapConfig['defaultZoom'] == 18 ? 'selected' : '' }}>📍 18 - JALAN</option>
                        </select>
                    </div>

                    <div id="statusBox" class="p-4 bg-emerald-50 border border-emerald-100 rounded-2xl hidden animate-pulse text-center">
                        <p class="text-[10px] font-black text-emerald-800 uppercase">Titik Baru Terdeteksi!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/polygon-layers.js') }}"></script>
<script>
    const MAP_SETTINGS = {
        center: [{{ $mapConfig['center'][0] }}, {{ $mapConfig['center'][1] }}], 
        defaultZoom: {{ $mapConfig['defaultZoom'] }}, 
    };

    let map, marker;
    let baseLayers = {};

    document.addEventListener("DOMContentLoaded", function() {
        baseLayers.street = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 20 });
        baseLayers.satellite = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', { maxZoom: 20 });
        baseLayers.dark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 20 });

        map = L.map('map-aset', { zoomControl: false, layers: [baseLayers.street] }).setView(MAP_SETTINGS.center, MAP_SETTINGS.defaultZoom);
        L.control.zoom({ position: 'topleft' }).addTo(map);

        if (typeof initPolygonLayers === "function") { initPolygonLayers(map); }

        const adminIcon = L.divIcon({
            className: 'custom-div-icon',
            html: "<div style='background-color:#4F46E5; width:24px; height:24px; border-radius:50%; border:4px solid white; box-shadow:0 0 20px rgba(79,70,229,0.5);'></div>",
            iconSize: [24, 24], iconAnchor: [12, 12]
        });

        marker = L.marker(MAP_SETTINGS.center, { draggable: true, icon: adminIcon }).addTo(map);

        map.on('click', function(e) {
            updateCoordinate(e.latlng.lat, e.latlng.lng);
            marker.setLatLng(e.latlng);
        });

        marker.on('dragend', function() {
            const position = marker.getLatLng();
            updateCoordinate(position.lat, position.lng);
        });

        document.getElementById('input-zoom').addEventListener('change', function() {
            map.setZoom(parseInt(this.value));
        });

        document.getElementById('btn-search-loc').addEventListener('click', function() {
            const query = document.getElementById('geo-search').value;
            if (!query) return;
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ", KBB")}&limit=1`)
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
                });
        });
    });

    function updateCoordinate(lat, lng) {
        document.getElementById('input-lat').value = lat.toFixed(6);
        document.getElementById('input-lng').value = lng.toFixed(6);
        document.getElementById('statusBox').classList.remove('hidden');
    }

    function switchBaseLayer(type) {
        Object.values(baseLayers).forEach(l => map.removeLayer(l));
        map.addLayer(baseLayers[type]);
        document.getElementById(`radio-${type}`).checked = true;
    }

    function togglePolygonLayer(type, isChecked) {
        if (window.polygonLayers && window.polygonLayers[type]) {
            if (isChecked) map.addLayer(window.polygonLayers[type]);
            else map.removeLayer(window.polygonLayers[type]);
        }
    }

    function simpanPengaturan() {
        const lat = document.getElementById('input-lat').value;
        const lng = document.getElementById('input-lng').value;
        const zoom = document.getElementById('input-zoom').value;
        const btn = document.getElementById('btnSimpan');

        btn.disabled = true;
        btn.innerHTML = "Menyimpan...";

        fetch("{{ route('admin.map.settings.update') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ latitude: lat, longitude: lng, zoom: zoom })
        })
        .then(r => r.json())
        .then(data => {
            if(data.status === 'success') {
                alert("✅ Pengaturan Map Disimpan!");
                location.reload();
            }
        })
        .catch(() => alert("Error menyimpan."))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = "Simpan Perubahan 💾";
        });
    }
</script>
@endsection