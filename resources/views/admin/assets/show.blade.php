{{-- G:\Kerja\Dishub_kbb\resources\views\admin\assets\show.blade.php --}}
@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    /* ===================================================== */
    /* STYLE UTAMA - Menggunakan Font Inter                  */
    /* ===================================================== */
    * {
        font-family: 'Inter', sans-serif !important;
    }
    
    .inter-font-scope, 
    .inter-font-scope input, 
    .inter-font-scope select, 
    .inter-font-scope textarea, 
    .inter-font-scope button {
        font-family: 'Inter', sans-serif !important;
    }
    
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    .main-container-scroll {
        height: 100vh;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-gutter: stable;
    }

    /* PERBAIKAN: Status badge tanpa icon, hanya emoji dan teks */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: normal;
    }
    
    .status-baik { background: #dcfce7; color: #166534; }
    .status-proses { background: #e0f2fe; color: #0369a1; }
    .status-rusak { background: #fef9c3; color: #854d0e; }
    .status-kritis { background: #fee2e2; color: #991b1b; }
    
    .detail-card {
        transition: all 0.2s ease;
    }
    .detail-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -12px rgba(0, 0, 0, 0.15);
    }
    
    /* PERBAIKAN: Custom marker icon */
    .custom-detail-marker {
        background: transparent !important;
        border: none !important;
    }
    
    .detail-marker-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        background: white;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        border: 3px solid white;
        transition: transform 0.2s ease;
    }
    
    .detail-marker-wrapper i {
        font-size: 22px;
    }
    
    .detail-marker-wrapper:hover {
        transform: scale(1.1);
        z-index: 1000;
    }
    
    .detail-marker-tail {
        position: absolute;
        bottom: -10px;
        width: 14px;
        height: 14px;
        background: inherit;
        transform: rotate(45deg);
        border-right: 3px solid white;
        border-bottom: 3px solid white;
        border-radius: 0 0 2px 0;
    }

    /* ===================================================== */
    /* PERBAIKAN: Gambar tidak terpotong                     */
    /* ===================================================== */
    .foto-container {
        width: 100%;
        min-height: 320px;
        max-height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        border-radius: 16px;
        overflow: hidden;
    }
    
    .foto-container img {
        width: 100%;
        height: auto;
        max-height: 400px;
        object-fit: contain;
        object-position: center;
    }
    
    /* ===================================================== */
    /* RESPONSIVE UNTUK MOBILE                               */
    /* ===================================================== */
    @media (max-width: 768px) {
        .main-container-scroll {
            padding: 16px !important;
        }
        .foto-container {
            min-height: 200px;
            max-height: 250px;
        }
        .foto-container img {
            max-height: 250px;
        }
        #detailMap {
            height: 320px !important;
        }
        .grid {
            gap: 16px !important;
        }
    }
    
    /* ===================================================== */
    /* MAP CONTAINER                                         */
    /* ===================================================== */
    #detailMap {
        width: 100%;
        height: 450px;
        border-radius: 16px;
        z-index: 1;
    }
</style>

<div class="main-container-scroll bg-slate-50 p-8 inter-font-scope custom-scroll">
    <div class="max-w-[1400px] mx-auto">
        
        {{-- Header --}}
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Detail Aset</h1>
                <p class="text-slate-500 font-medium text-xs mt-1">Informasi Lengkap & Visualisasi GIS</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('assets.edit', $asset->id) }}" class="px-5 py-2.5 bg-indigo-600 border border-indigo-600 text-white rounded-xl font-bold text-xs hover:bg-indigo-700 transition-all shadow-sm">
                    <i class="fas fa-pencil-alt mr-1"></i> Edit Aset
                </a>
                <a href="{{ route('assets.list') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-50 transition-all shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-8">
            
            {{-- Kolom Kiri: Informasi Detail --}}
            <div class="col-span-12 lg:col-span-6 space-y-6">
                
                {{-- Card Informasi Utama --}}
                <div class="bg-white rounded-[24px] shadow-sm border border-slate-200 overflow-hidden detail-card">
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
                        <h2 class="text-white font-bold text-sm flex items-center gap-2">
                            <i class="fas fa-info-circle"></i> Informasi Detail Aset
                        </h2>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="border-b border-slate-100 pb-4">
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wide mb-1">Nama Perangkat</label>
                            <p class="text-xl font-bold text-slate-800 break-words">{{ $asset->nama }}</p>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 tracking-wide mb-1">Kategori</label>
                                <p class="text-sm font-semibold text-slate-700 bg-slate-100 inline-block px-3 py-1.5 rounded-lg">
                                    {{ $asset->kategori }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 tracking-wide mb-1">Jenis Aset</label>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-semibold text-slate-700 bg-slate-100 inline-block px-3 py-1.5 rounded-lg">
                                        {{ $asset->jenis }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wide mb-1">Kondisi Saat Ini</label>
                            <div>
                                @php
                                    $statusClass = '';
                                    $statusText = '';
                                    $statusEmoji = '';
                                    switch($asset->status) {
                                        case 'Baik':
                                            $statusClass = 'status-baik';
                                            $statusText = 'Baik';
                                            $statusEmoji = '🟢';
                                            break;
                                        case 'Proses Perbaikan':
                                            $statusClass = 'status-proses';
                                            $statusText = 'Proses Perbaikan';
                                            $statusEmoji = '🔵';
                                            break;
                                        case 'Rusak':
                                            $statusClass = 'status-rusak';
                                            $statusText = 'Rusak';
                                            $statusEmoji = '🟡';
                                            break;
                                        case 'Kritis':
                                            $statusClass = 'status-kritis';
                                            $statusText = 'Kritis';
                                            $statusEmoji = '🔴';
                                            break;
                                        default:
                                            $statusClass = 'status-baik';
                                            $statusText = $asset->status;
                                            $statusEmoji = '⚪';
                                    }
                                @endphp
                                <span class="status-badge {{ $statusClass }}">
                                    <span style="font-size: 14px;">{{ $statusEmoji }}</span>
                                    {{ $statusText }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wide mb-1">Alamat / Lokasi</label>
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-200">
                                <p class="text-sm text-slate-700 leading-relaxed break-words">
                                    <i class="fas fa-map-marker-alt text-slate-400 mr-1"></i> {{ $asset->alamat ?? 'Alamat tidak tersedia' }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div class="bg-slate-50 rounded-xl p-3 text-center">
                                <label class="block text-[10px] font-bold text-slate-400 tracking-wide mb-1">Latitude</label>
                                <p class="text-lg font-mono font-bold text-indigo-600 break-all">{{ number_format($asset->lat, 6) }}</p>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-3 text-center">
                                <label class="block text-[10px] font-bold text-slate-400 tracking-wide mb-1">Longitude</label>
                                <p class="text-lg font-mono font-bold text-indigo-600 break-all">{{ number_format($asset->lng, 6) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Card Informasi Tambahan (jika ada kolom lain) --}}
                @if(isset($asset->created_at) || isset($asset->updated_at) || isset($asset->user_id))
                <div class="bg-white rounded-[24px] shadow-sm border border-slate-200 p-6 detail-card">
                    <h3 class="text-xs font-bold text-slate-400 tracking-wide mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i> Informasi Sistem
                    </h3>
                    <div class="space-y-3 text-sm">
                        @if(isset($asset->created_at))
                        <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                            <span class="text-slate-500 font-medium">Dibuat pada</span>
                            <span class="text-slate-700 font-semibold">{{ $asset->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        @if(isset($asset->updated_at) && $asset->updated_at != $asset->created_at)
                        <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                            <span class="text-slate-500 font-medium">Terakhir diupdate</span>
                            <span class="text-slate-700 font-semibold">{{ $asset->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                        @endif
                        @if(isset($asset->user_id) && isset($asset->creator))
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-medium">Diregistrasi oleh</span>
                            <span class="text-slate-700 font-semibold">{{ $asset->creator->name ?? 'Admin' }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            
            {{-- Kolom Kanan: Peta & Foto --}}
            <div class="col-span-12 lg:col-span-6 space-y-6">
                
                {{-- Foto Aset - PERBAIKAN: Gambar tidak terpotong --}}
                @if($asset->foto && Storage::disk('public')->exists($asset->foto))
                <div class="bg-white rounded-[24px] shadow-sm border border-slate-200 overflow-hidden detail-card">
                    <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-4">
                        <h2 class="text-white font-bold text-sm flex items-center gap-2">
                            <i class="fas fa-camera mr-1"></i> Dokumentasi Fisik
                        </h2>
                    </div>
                    <div class="p-4">
                        <div class="foto-container">
                            <img src="{{ asset('storage/' . $asset->foto) }}" 
                                 alt="Foto {{ $asset->nama }}" 
                                 class="rounded-2xl shadow-lg border border-slate-200">
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-white rounded-[24px] shadow-sm border border-slate-200 p-8 text-center detail-card">
                    <i class="fas fa-image text-6xl text-slate-300 mb-3"></i>
                    <p class="text-slate-400 font-medium text-sm">Belum ada foto yang diunggah</p>
                    <a href="{{ route('assets.edit', $asset->id) }}" class="inline-block mt-3 text-indigo-500 text-xs font-semibold hover:underline">
                        <i class="fas fa-upload"></i> Upload foto sekarang
                    </a>
                </div>
                @endif
                
                {{-- Peta Lokasi --}}
                <div class="bg-white rounded-[24px] shadow-sm border border-slate-200 overflow-hidden detail-card">
                    <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-4">
                        <h2 class="text-white font-bold text-sm flex items-center gap-2">
                            <i class="fas fa-map"></i> Visualisasi Peta GIS
                        </h2>
                    </div>
                    <div class="p-3">
                        <div id="detailMap" class="w-full rounded-2xl z-0"></div>
                    </div>
                    <div class="px-6 pb-4 text-center text-[10px] text-slate-400 font-medium">
                        <i class="fas fa-mouse-pointer"></i> Klik marker untuk melihat informasi singkat | <i class="fas fa-hand-peace"></i> Geser peta untuk eksplorasi
                    </div>
                </div>
                
                {{-- Tombol Aksi --}}
                <div class="flex gap-3">
                    <a href="{{ route('assets.edit', $asset->id) }}" class="flex-1 py-4 bg-indigo-600 text-white rounded-xl font-bold text-xs text-center hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200">
                        <i class="fas fa-pencil-alt mr-1"></i> Edit Data Aset
                    </a>
                    <button type="button" onclick="confirmDelete({{ $asset->id }})" class="flex-1 py-4 bg-red-50 border-2 border-red-200 text-red-700 rounded-xl font-bold text-xs hover:bg-red-100 transition-all">
                        <i class="fas fa-trash-alt mr-1"></i> Hapus Aset
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Form Delete (tersembunyi) --}}
<form id="delete-form" action="{{ route('assets.destroy', $asset->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>

<script>
    // Inisialisasi Peta Detail
    const assetLat = {{ $asset->lat }};
    const assetLng = {{ $asset->lng }};
    const assetName = "{{ $asset->nama }}";
    const assetStatus = "{{ $asset->status }}";
    const assetIconClass = "{{ $asset->icon_marker ?? 'fa-map-marker-alt' }}";
    const assetKategori = "{{ $asset->kategori }}";
    
    // Konfigurasi warna marker berdasarkan status (sesuai dengan dashboard)
    function getStatusColor(status) {
        switch(status) {
            case 'Baik': return '#22c55e';
            case 'Proses Perbaikan': return '#3b82f6';
            case 'Rusak': return '#f59e0b';
            case 'Kritis': return '#ef4444';
            default: return '#6366f1';
        }
    }
    
    // Fungsi untuk mendapatkan emoji status
    const getStatusEmoji = (status) => {
        switch(status) {
            case 'Baik': return '🟢';
            case 'Rusak': return '🟡';
            case 'Kritis': return '🔴';
            case 'Proses Perbaikan': return '🔵';
            default: return '⚪';
        }
    };
    
    // Konfigurasi base maps
    const googleStreet = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    });
    
    const darkMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 20 });
    const streetMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { maxZoom: 20 });
    const satelliteMap = L.tileLayer('https://mt{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['0', '1', '2', '3']
    });
    
    const detailMap = L.map('detailMap', {
        center: [assetLat, assetLng],
        zoom: 17,
        layers: [googleStreet],
        zoomControl: true,
        attributionControl: false
    });
    
    const baseMaps = {
        "Google Street": googleStreet,
        "Dark Mode": darkMap,
        "Streets": streetMap,
        "Satellite": satelliteMap
    };
    
    L.control.layers(baseMaps).addTo(detailMap);
    
    // Custom Icon dengan Font Awesome
    const markerColor = getStatusColor(assetStatus);
    
    const customIcon = L.divIcon({
        html: `<div class="detail-marker-wrapper" style="background: ${markerColor};">
                    <i class="fas ${assetIconClass}" style="color: white; font-size: 22px;"></i>
                    <div class="detail-marker-tail" style="background: ${markerColor};"></div>
                </div>`,
        className: 'custom-detail-marker',
        iconSize: [44, 44],
        iconAnchor: [22, 44],
        popupAnchor: [0, -44]
    });
    
    const marker = L.marker([assetLat, assetLng], { icon: customIcon }).addTo(detailMap);
    
    // Popup informasi - menggunakan emoji untuk status
    const popupContent = `
        <div style="font-family: 'Inter', sans-serif; min-width: 260px; max-width: 300px;">
            <div style="font-weight: 800; font-size: 14px; color: #1e293b; margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;">
                <i class="fas ${assetIconClass}" style="color: #4f46e5; margin-right: 6px;"></i> 
                ${assetName}
            </div>
            <div style="font-size: 11px; color: #64748b; margin-bottom: 6px;">
                <span style="font-weight: 600;">Kategori:</span> ${assetKategori}
            </div>
            <div style="font-size: 11px; color: #64748b; margin-bottom: 6px;">
                <span style="font-weight: 600;">Status:</span> 
                <span style="display: inline-flex; align-items: center; gap: 4px; font-weight: 700; color: ${markerColor}">
                    ${getStatusEmoji(assetStatus)} ${assetStatus}
                </span>
            </div>
            <div style="font-size: 10px; color: #94a3b8; margin-top: 8px; border-top: 1px solid #e2e8f0; padding-top: 6px;">
                <i class="fas fa-map-marker-alt"></i> ${assetLat.toFixed(6)}, ${assetLng.toFixed(6)}
            </div>
            <div style="margin-top: 10px;">
                <a href="/admin/assets/${asset.id}" style="display: block; text-align: center; background: #4f46e5; color: white; text-decoration: none; padding: 6px 12px; border-radius: 20px; font-size: 10px; font-weight: 600;">
                    <i class="fas fa-eye"></i> Lihat Detail Lengkap
                </a>
            </div>
        </div>
    `;
    
    marker.bindPopup(popupContent);
    marker.openPopup();
    
    // Fungsi konfirmasi hapus
    window.confirmDelete = function(assetId) {
        if (confirm('Apakah Anda yakin ingin menghapus aset ini?\n\nTindakan ini tidak dapat dibatalkan!')) {
            document.getElementById('delete-form').submit();
        }
    };
    
    // Tambahkan kontrol scale
    L.control.scale({ metric: true, imperial: false, position: 'bottomright' }).addTo(detailMap);
</script>

@endsection