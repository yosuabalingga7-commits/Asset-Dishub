@extends('layouts.app')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    /* Hilangkan scroll di seluruh halaman */
    html,
    body {
        overflow: hidden !important;
        height: 100vh !important;
    }

    /* Custom Scrollbar - Boxy (tidak dipakai karena scroll dihilangkan) */
    .custom-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    .custom-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
    }

    .leaflet-interactive {
        pointer-events: auto !important;
    }

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
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255, 255, 255, 0.8);
    }

    .layer-panel {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
        padding: 10px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.5);
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
        transition: all 0.2s;
    }

    .layer-option:hover {
        background: rgba(79, 70, 229, 0.08);
    }

    .layer-option span {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .leaflet-control-zoom {
        border: none !important;
        margin-left: 20px !important;
        margin-top: 20px !important;
    }

    .leaflet-control-zoom-in,
    .leaflet-control-zoom-out {
        background: white !important;
        color: #4F46E5 !important;
        width: 40px !important;
        height: 40px !important;
        line-height: 40px !important;
        margin-bottom: 5px !important;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    /* LEGEND - HANYA DUA ITEM: BATAS KECAMATAN DAN BATAS DESA */
    .map-legend-floating {
        position: absolute;
        bottom: 15px;
        left: 15px;
        z-index: 1001;
        background: white;
        border: 1px solid #e2e8f0;
        padding: 6px 12px;
        display: flex;
        flex-direction: row;
        gap: 16px;
        pointer-events: auto;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
    }

    .legend-item span {
        font-size: 10px;
        font-weight: 700;
        color: #1e293b;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Boxy style - hapus semua rounded */
    .boxy-panel {
        border: 1px solid #e2e8f0;
        background: white;
    }

    input,
    select,
    button {
        outline: none;
    }

    input:focus,
    select:focus,
    button:focus {
        outline: none;
    }

    /* Warna teks input hitam */
    #geo-search,
    #input-lat,
    #input-lng {
        color: #1e293b !important;
    }

    #geo-search::placeholder {
        color: #94a3b8;
    }
</style>

<div class="h-screen bg-[#F8FAFC] flex flex-col transition-all duration-300 overflow-hidden">

    <div class="p-6 md:px-10 pt-8 pb-3 flex-none">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
            <div class="space-y-0.5">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="w-6 h-0.5 bg-indigo-600"></span>
                    <span class="text-[9px] font-black text-indigo-600 tracking-[0.3em]">System Engine</span>
                </div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight ">
                    Konfigurasi <span class="text-indigo-600">Map</span>
                </h1>
                <p class="text-slate-400 font-semibold text-[10px] tracking-widest opacity-80 flex items-center gap-2">
                    <span class="inline-block w-1.5 h-1.5 bg-emerald-500"></span>
                    Pengaturan Default Center & Zoom Level — LINTAS KBB
                </p>
            </div>

            <div class="flex items-center gap-3 bg-white p-1.5 border border-slate-100">
                <a href="{{ route('dashboard') }}" class="px-4 py-2 text-slate-500 font-bold text-[9px] tracking-widest hover:bg-slate-50 transition-all flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Dashboard
                </a>
                <button onclick="simpanPengaturan()" id="btnSimpan" class="px-6 py-2 bg-indigo-600 text-white font-black text-[9px] tracking-[0.2em] shadow-md shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-95 flex items-center gap-1.5">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>

    {{-- BARIS PENCARIAN HORIZONTAL DIPERKECIL --}}
    <div class="px-6 md:px-10 pb-2 flex-none">
        <div class="bg-white border border-slate-100">
            <div class="flex items-center gap-2 py-2 px-3">
                <label class="text-[9px] font-black text-indigo-600 tracking-[0.2em] shrink-0 whitespace-nowrap">Cari Wilayah / Koordinat</label>
                <input type="text" id="geo-search" placeholder="Contoh: Lembang atau -6.9123,107.6543" class="flex-1 bg-slate-50 border border-slate-200 py-2 px-3 text-xs font-medium focus:border-indigo-500 outline-none text-slate-900">
                <button id="btn-search-loc" class="bg-slate-900 text-white px-4 py-2 hover:bg-indigo-600 transition-all flex items-center justify-center gap-1.5 font-bold text-[10px] tracking-wider">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Cari
                </button>
            </div>
        </div>
    </div>

    <div class="flex-1 px-6 md:px-10 pb-6 overflow-hidden">
        {{-- MAP DIPERLUAS: kolom kiri 9, kanan 3 --}}
        <div class="grid grid-cols-12 gap-6 h-full min-h-0">

            <div class="col-span-12 lg:col-span-9 relative h-full">
                <div class="group relative bg-white p-1 border border-slate-100 h-full overflow-hidden min-h-0">

                    <div class="custom-layer-control">
                        <div class="layer-button">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 12l10 5 10-5M2 17l10 5 10-5"></path>
                            </svg>
                        </div>
                        <div class="layer-panel">
                            <div class="layer-option" onclick="switchBaseLayer('light')">
                                <input type="radio" name="map_style" id="radio-light" checked>
                                <span>☀️ Light Mode</span>
                            </div>
                            <div class="layer-option" onclick="switchBaseLayer('dark')">
                                <input type="radio" name="map_style" id="radio-dark">
                                <span>🌑 Dark Mode</span>
                            </div>
                            <div class="layer-option" onclick="switchBaseLayer('roadmap')">
                                <input type="radio" name="map_style" id="radio-roadmap">
                                <span>🗺️ Google Roadmap</span>
                            </div>
                            <div class="layer-option" onclick="switchBaseLayer('satellite')">
                                <input type="radio" name="map_style" id="radio-satellite">
                                <span>🛰️ Google Satellite</span>
                            </div>
                        </div>
                    </div>

                    <div id="map-aset" class="w-full h-full relative z-0"></div>
                </div>
            </div>

            {{-- KOLOM KANAN (3 kolom) --}}
            <div class="col-span-12 lg:col-span-3 space-y-4">
                <div class="bg-white p-4 border border-slate-100 space-y-3">
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-slate-50/80 p-2 border border-slate-100">
                            <span class="block text-[8px] font-black text-slate-400 mb-0.5">Latitude</span>
                            <input type="text" id="input-lat" value="{{ $mapConfig['center'][0] }}" readonly class="w-full bg-transparent font-black text-slate-800 text-xs outline-none">
                        </div>
                        <div class="bg-slate-50/80 p-2 border border-slate-100">
                            <span class="block text-[8px] font-black text-slate-400 mb-0.5">Longitude</span>
                            <input type="text" id="input-lng" value="{{ $mapConfig['center'][1] }}" readonly class="w-full bg-transparent font-black text-slate-800 text-xs outline-none">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[9px] font-black text-slate-400 tracking-widest">Zoom Level</label>
                        <select id="input-zoom" class="w-full px-3 py-2 bg-slate-900 outline-none font-bold text-indigo-400 text-xs appearance-none cursor-pointer">
                            <option value="11" {{ $mapConfig['defaultZoom'] == 11 ? 'selected' : '' }}>🗺️ 11 - Default</option>
                            <option value="13" {{ $mapConfig['defaultZoom'] == 13 ? 'selected' : '' }}>🏢 13 - Kota</option>
                            <option value="15" {{ $mapConfig['defaultZoom'] == 15 ? 'selected' : '' }}>🏘️ 15 - Detail</option>
                            <option value="18" {{ $mapConfig['defaultZoom'] == 18 ? 'selected' : '' }}>📍 18 - Jalan</option>
                        </select>
                    </div>

                    <div id="statusBox" class="p-2 bg-emerald-50 border border-emerald-100 hidden animate-pulse text-center">
                        <p class="text-[9px] font-black text-emerald-800">Titik Baru Terdeteksi!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/polygon-layers.js') }}"></script>
<script>
    // Diperbaiki menggunakan pembungkusan kutip string & parsing desimal (Protected Variations)
    // Mengeliminasi kesalahan pembacaan statis oleh TypeScript/JS Server di IDE Anda
    const MAP_SETTINGS = {
        center: [
            parseFloat("{{ $mapConfig['center'][0] }}"),
            parseFloat("{{ $mapConfig['center'][1] }}")
        ],
        defaultZoom: parseInt("{{ $mapConfig['defaultZoom'] }}"),
    };

    let map, marker;
    let baseLayers = {};

    document.addEventListener("DOMContentLoaded", function() {
        // 4 MODE PETA
        baseLayers.light = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 20
        });
        baseLayers.dark = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            maxZoom: 20
        });
        baseLayers.roadmap = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20
        });
        baseLayers.satellite = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
            maxZoom: 20
        });

        map = L.map('map-aset', {
            zoomControl: false,
            layers: [baseLayers.light]
        }).setView(MAP_SETTINGS.center, MAP_SETTINGS.defaultZoom);
        L.control.zoom({
            position: 'topleft'
        }).addTo(map);

        if (typeof initPolygonLayers === "function") {
            initPolygonLayers(map);
        }

        const adminIcon = L.divIcon({
            className: 'custom-div-icon',
            html: "<div style='background-color:#4F46E5; width:20px; height:20px; border-radius:50%; border:3px solid white; box-shadow:0 0 15px rgba(79,70,229,0.5);'></div>",
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        marker = L.marker(MAP_SETTINGS.center, {
            draggable: true,
            icon: adminIcon
        }).addTo(map);

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

        // FUNGSI PENCARIAN - KOORDINAT DAN LOKASI
        document.getElementById('btn-search-loc').addEventListener('click', function() {
            const query = document.getElementById('geo-search').value.trim();
            if (!query) {
                alert("Masukkan nama wilayah atau koordinat!");
                return;
            }

            // CEK APAKAH INPUT BERISI KOORDINAT (mengandung koma)
            if (query.includes(',')) {
                const parts = query.split(',');
                if (parts.length === 2) {
                    const lat = parseFloat(parts[0].trim());
                    const lng = parseFloat(parts[1].trim());
                    if (!isNaN(lat) && !isNaN(lng)) {
                        map.flyTo([lat, lng], 18);
                        marker.setLatLng([lat, lng]);
                        updateCoordinate(lat, lng);
                        document.getElementById('input-zoom').value = "18";
                        return;
                    } else {
                        alert("Format koordinat tidak valid! Gunakan format: latitude,longitude (contoh: -6.9123,107.6543)");
                        return;
                    }
                } else {
                    alert("Format koordinat tidak valid! Gunakan format: latitude,longitude");
                    return;
                }
            }

            // JIKA BUKAN KOORDINAT, LANJUTKAN PENCARIAN NAMA WILAYAH
            const searchUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ", Bandung Barat")}&limit=1`;

            fetch(searchUrl)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);
                        const displayName = data[0].display_name;
                        map.flyTo([lat, lon], 15);
                        marker.setLatLng([lat, lon]);
                        updateCoordinate(lat, lon);
                        document.getElementById('input-zoom').value = "15";
                        console.log("Lokasi ditemukan:", displayName);
                    } else {
                        alert("Wilayah tidak ditemukan. Silakan coba dengan nama yang lebih spesifik.");
                    }
                })
                .catch(error => {
                    console.error("Error searching location:", error);
                    alert("Terjadi kesalahan saat mencari lokasi. Silakan coba lagi.");
                });
        });

        // ENTER KEY UNTUK PENCARIAN
        document.getElementById('geo-search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('btn-search-loc').click();
            }
        });
    });

    function updateCoordinate(lat, lng) {
        document.getElementById('input-lat').value = lat.toFixed(6);
        document.getElementById('input-lng').value = lng.toFixed(6);
        document.getElementById('statusBox').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('statusBox').classList.add('hidden');
        }, 3000);
    }

    // Peta Pengubah Tipe Dasar (Basemap)
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
        // Ambil data koordinat dan amankan tipe data melalui parsing matematis (Protected Variations)
        const lat = parseFloat(document.getElementById('input-lat').value);
        const lng = parseFloat(document.getElementById('input-lng').value);
        const zoom = parseInt(document.getElementById('input-zoom').value);
        const btn = document.getElementById('btnSimpan');

        // Validasi batasan spasial global untuk mencegah error sirkular / kebocoran parse di tingkat React
        if (isNaN(lat) || lat < -90 || lat > 90) {
            alert("❌ Koordinat Latitude tidak valid! Nilai harus berada dalam rentang -90 hingga 90.");
            return;
        }

        if (isNaN(lng) || lng < -180 || lng > 180) {
            alert("❌ Koordinat Longitude tidak valid! Nilai harus berada dalam rentang -180 hingga 180.");
            return;
        }

        if (isNaN(zoom) || zoom < 1 || zoom > 20) {
            alert("❌ Tingkat Zoom tidak valid! Harus bernilai bilangan bulat.");
            return;
        }

        btn.disabled = true;
        btn.innerHTML = "Menyimpan...";

        fetch("{{ route('admin.map.settings.update') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    latitude: lat,
                    longitude: lng,
                    zoom: zoom
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    alert("✅ Pengaturan Map Disimpan!");
                    location.reload();
                } else {
                    alert("❌ Gagal menyimpan: " + (data.message || "Unknown error"));
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error menyimpan pengaturan.");
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = "Simpan Perubahan ";
            });
    }
</script>
@endsection