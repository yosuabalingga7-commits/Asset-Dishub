@extends('layouts.public')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<style>
    /* Custom Dropdown Styles */
    .custom-dropdown {
        position: relative;
        width: 100%;
    }

    .dropdown-selected {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        background-color: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.2s;
    }

    .dropdown-selected:hover {
        border-color: #cbd5e1;
    }

    .dropdown-selected .selected-text {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .dropdown-selected .selected-text i {
        width: 1.25rem;
        font-size: 1rem;
        color: #64748b;
    }

    .dropdown-selected i.chevron {
        color: #64748b;
        font-size: 0.75rem;
        transition: transform 0.2s;
    }

    .dropdown-selected.open .chevron {
        transform: rotate(180deg);
    }

    .dropdown-options {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        margin-top: 0.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        z-index: 50;
        max-height: 250px;
        overflow-y: auto;
        display: none;
    }

    .dropdown-options.open {
        display: block;
    }

    .dropdown-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.15s;
        border-bottom: 1px solid #f1f5f9;
    }

    .dropdown-option:last-child {
        border-bottom: none;
    }

    .dropdown-option:hover {
        background-color: #f1f5f9;
    }

    .dropdown-option.selected {
        background-color: #e0e7ff;
        color: #4f46e5;
    }

    .dropdown-option i {
        width: 1.25rem;
        font-size: 1rem;
        color: #64748b;
    }

    .dropdown-option.selected i {
        color: #4f46e5;
    }

    /* Asset Dropdown Styles */
    .asset-dropdown {
        position: relative;
        width: 100%;
    }

    .asset-dropdown-selected {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        background-color: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.2s;
    }

    .asset-dropdown-selected:hover {
        border-color: #cbd5e1;
    }

    .asset-dropdown-options {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        margin-top: 0.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        z-index: 50;
        max-height: 250px;
        overflow-y: auto;
        display: none;
    }

    .asset-dropdown-options.open {
        display: block;
    }

    .asset-dropdown-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.15s;
        border-bottom: 1px solid #f1f5f9;
    }

    .asset-dropdown-option:last-child {
        border-bottom: none;
    }

    .asset-dropdown-option:hover {
        background-color: #f1f5f9;
    }
</style>

<div class="min-h-screen relative font-['Inter'] pb-20 text-white overflow-hidden bg-[#0B2A4A]">

    {{-- LATAR BELAKANG BARU: MESH GRADIENT STATIC --}}
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-[#0B2A4A]"></div>
        <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[600px] h-[600px] bg-indigo-900/40 rounded-full blur-[150px]"></div>
    </div>

    {{-- HEADER HERO --}}
    <div class="relative h-[350px] flex flex-col items-center justify-center overflow-hidden">
        <div class="relative z-10 text-center px-6">
            <h1 class="text-white text-4xl md:text-6xl font-black uppercase tracking-tighter leading-none mb-4">
                Pengaduan <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-[#F4B400] to-yellow-200">Kerusakan Aset</span>
            </h1>
            <p class="text-slate-300 text-xs md:text-sm font-medium tracking-wide max-w-lg mx-auto leading-relaxed opacity-80">
                Dinas Perhubungan Kabupaten Bandung Barat melayani pelaporan kerusakan infrastruktur jalan secara cepat dan transparan.
            </p>
        </div>
    </div>

    <div class="max-w-2xl mx-auto -mt-12 relative z-20 px-4">

        @if(session('success'))
        <div class="mb-6 bg-emerald-500/20 backdrop-blur-md border border-emerald-500/50 text-emerald-400 p-5 rounded-3xl shadow-xl flex items-center gap-4">
            <div class="text-2xl">✅</div>
            <div>
                <p class="font-black uppercase text-xs tracking-widest">Laporan Terkirim</p>
                <p class="text-[11px] font-medium opacity-90">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-500/20 backdrop-blur-md border border-red-500/50 text-red-400 p-5 rounded-3xl shadow-xl flex items-center gap-4">
            <div class="text-2xl">⚠️</div>
            <div>
                <p class="font-black uppercase text-xs tracking-widest">Gagal Mengirim</p>
                <p class="text-[11px] font-medium opacity-90">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        {{-- FORM UTAMA --}}
        <form action="{{ route('lapor.store') }}" method="POST" enctype="multipart/form-data" id="laporanForm">
            @csrf
            <input type="hidden" name="status" value="masuk">
            <input type="hidden" name="asset_id" id="selected_asset_id">
            <input type="hidden" name="id_asset_selected" id="selected_id_asset">
            <input type="hidden" name="judul_laporan" id="judul_laporan_hidden">

            <div class="bg-white rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.3)] p-8 md:p-12 border-b-8 border-[#F4B400] text-[#1E293B]">

                {{-- SECTION 1: DATA DIRI --}}
                <div class="mb-12">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-[#0B2A4A] text-[#F4B400] rounded-2xl flex items-center justify-center text-lg font-black">01</div>
                        <div>
                            <h3 class="text-base font-black text-[#0B2A4A] tracking-widest">Informasi Pelapor</h3>
                            <p class="text-[10px] text-slate-400 font-bold tracking-tight">Identitas aman dalam enkripsi kami</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1 tracking-widest">Nama Lengkap</label>
                            <input type="text" name="nama_pelapor" value="{{ old('nama_pelapor') }}" placeholder="Input nama sesuai identitas" class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl py-4 px-6 text-sm font-bold focus:border-[#0B2A4A] focus:ring-4 focus:ring-blue-100 transition-all outline-none text-[#0B2A4A]" required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1 tracking-widest">Nomor Wa</label>
                            <input type="text" name="kontak_pelapor" value="{{ old('kontak_pelapor') }}" inputmode="numeric" oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="08xxx" class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl py-4 px-6 text-sm font-bold focus:border-[#0B2A4A] focus:ring-4 focus:ring-blue-100 transition-all outline-none text-[#0B2A4A]" required>
                        </div>
                    </div>
                </div>

                {{-- SECTION MAPS --}}
                <div class="mb-12">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-[#0B2A4A] text-[#F4B400] rounded-2xl flex items-center justify-center text-lg font-black">02</div>
                        <div>
                            <h3 class="text-base font-black text-[#0B2A4A] tracking-widest">Lokasi Kejadian</h3>
                            <p class="text-[10px] text-slate-400 font-bold tracking-tight">Tentukan titik lokasi kejadian pada peta</p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <div class="flex flex-wrap gap-3 mb-4">
                            <button type="button" id="btnAktifkanGPS" class="bg-[#0B2A4A] text-[#F4B400] px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wide shadow-md hover:bg-black transition-all">
                                <i class="fas fa-satellite-dish mr-2"></i> Aktifkan GPS
                            </button>
                            <button type="button" id="btnToggleKunci" class="bg-amber-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wide shadow-md hover:bg-amber-700 transition-all">
                                <i class="fas fa-lock mr-2"></i> Kunci Lokasi
                            </button>
                        </div>

                        <div class="relative overflow-hidden rounded-[2rem] shadow-inner border-2 border-slate-100" style="height: 450px;">
                            <div id="miniMap" class="w-full h-full bg-slate-200 z-0"></div>
                        </div>

                        {{-- KETERANGAN TITIK ASET --}}
                        <div class="mt-3 text-center bg-blue-50/80 backdrop-blur-sm rounded-xl py-2 px-4 border border-blue-200">
                            <div class="flex items-center justify-center gap-2">
                                <div style="background-color: #F4B400; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: 2px solid #0B2A4A; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                                    <i class="fas fa-box" style="font-size: 9px; color: #0B2A4A;"></i>
                                </div>
                                <span class="font-black text-blue-800 text-[10px]">= Lokasi aset terdaftar dalam radius 100 meter dari posisi Anda</span>
                            </div>
                        </div>

                        <div class="mt-4 px-6 py-4 bg-blue-50 rounded-2xl border border-blue-100 flex items-start justify-between gap-4">
                            <div class="flex items-start gap-4 flex-1">
                                <div class="w-3 h-3 mt-1 bg-red-600 rounded-full animate-pulse flex-shrink-0"></div>
                                <p class="text-[11px] font-black text-[#0B2A4A] leading-relaxed tracking-tight italic" id="alamatText">Mendeteksi lokasi...</p>
                            </div>
                        </div>

                        {{-- SISTEM DETEKSI ASET OTOMATIS --}}
                        <div class="mt-6 space-y-3 bg-blue-50 rounded-2xl p-4 border border-blue-100">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-location-dot text-blue-600"></i>
                                <label class="text-[10px] font-black text-blue-800 uppercase ml-1 tracking-widest">Sistem Deteksi Aset Otomatis</label>
                            </div>
                            <p class="text-[11px] text-blue-700 font-semibold">
                                Aktifkan GPS atau klik di peta. Sistem akan mendeteksi aset dalam radius <span id="radiusValue">100</span> meter.
                            </p>

                            {{-- DROPDOWN PILIH ASET --}}
                            <div class="mt-2" id="assetDropdownContainer" style="display: none;">
                                <label class="text-[10px] font-black text-blue-800 uppercase ml-1 tracking-widest">Pilih Aset yang Dilaporkan</label>
                                <div class="asset-dropdown mt-2" id="assetDropdown">
                                    <div class="asset-dropdown-selected" onclick="window.LaporPublic.toggleAssetDropdown()">
                                        <div class="selected-text">
                                            <i class="fas fa-building"></i>
                                            <span id="selectedAssetText">Pilih Aset</span>
                                        </div>
                                        <i class="fas fa-chevron-down chevron"></i>
                                    </div>
                                    <div class="asset-dropdown-options" id="assetDropdownOptions">
                                    </div>
                                </div>
                                <p class="text-[8px] text-blue-500 mt-1" id="assetCountInfo"></p>
                            </div>
                            <div class="mt-2" id="noAssetWarning" style="display: none;">
                                <p class="text-[10px] text-amber-600 font-semibold">⚠️ Tidak ada aset terdaftar dalam radius 100 meter dari lokasi Anda.</p>
                            </div>

                            <div class="flex items-center gap-2 mt-2">
                                <i class="fas fa-ruler-combined text-blue-500 text-xs"></i>
                                <span class="text-[9px] text-blue-600 font-bold">Radius deteksi: <span id="radiusValue2">100</span> meter</span>
                            </div>
                        </div>

                        <div class="mt-3 text-center">
                            <p class="text-[9px] font-bold text-slate-400">
                                <i class="fas fa-chart-line mr-1"></i> Anda harus berada dalam lingkaran biru untuk melaporkan aset
                            </p>
                        </div>

                        <input type="hidden" name="lat" id="inp_lat">
                        <input type="hidden" name="lng" id="inp_lng">
                        <input type="hidden" name="alamat" id="inp_alamat">
                        <input type="hidden" name="lokasi_koordinat" id="inp_koordinat">
                    </div>
                </div>

                {{-- SECTION 2: DETAIL --}}
                <div class="mb-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-[#0B2A4A] text-[#F4B400] rounded-2xl flex items-center justify-center text-lg font-black">03</div>
                        <div>
                            <h3 class="text-base font-black text-[#0B2A4A] tracking-widest">Detail Kerusakan</h3>
                            <p class="text-[10px] text-slate-400 font-bold tracking-tight">Isi detail aset yang bermasalah</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        {{-- UPLOAD PHOTO --}}
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1 tracking-widest">Bukti Foto (Wajib Kamera)</label>
                            <div class="relative group">
                                <div class="w-full py-10 bg-slate-50 border-2 border-dashed border-slate-300 rounded-[2rem] flex flex-col items-center justify-center transition-all group-hover:border-[#0B2A4A] group-hover:bg-blue-50/50">
                                    <div class="w-14 h-14 bg-white shadow-lg rounded-2xl flex items-center justify-center text-2xl mb-3"><i class="fas fa-camera text-slate-400 text-2xl"></i></div>
                                    <p class="text-[11px] font-black text-[#0B2A4A] uppercase tracking-widest">Klik Untuk Ambil Gambar</p>
                                    <input type="file" name="foto" id="foto_kamera" accept="image/*" capture="user" class="absolute inset-0 opacity-0 cursor-pointer" required onchange="window.LaporPublic.previewImage(event)">
                                </div>
                            </div>
                            <div id="image_preview_container" class="mt-4 hidden">
                                <img id="image_preview" src="#" class="w-full h-52 object-cover rounded-3xl border-4 border-white shadow-xl">
                            </div>
                        </div>

                        {{-- KONDISI - CUSTOM DROPDOWN --}}
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1 tracking-widest">Kategori Kondisi</label>
                            <div class="custom-dropdown" id="kondisiDropdown">
                                <div class="dropdown-selected" onclick="window.LaporPublic.toggleKondisiDropdown()">
                                    <div class="selected-text">
                                        <i class="fas fa-circle-info"></i>
                                        <span id="selectedKondisiText" class="text-slate-400">Pilih Salah Satu</span>
                                    </div>
                                    <i class="fas fa-chevron-down chevron"></i>
                                </div>
                                <div class="dropdown-options" id="kondisiOptions">
                                    <div class="dropdown-option" data-value="rusak" onclick="window.LaporPublic.selectKondisi('rusak', 'RUSAK', 'fa-triangle-exclamation')">
                                        <i class="fas fa-triangle-exclamation"></i>
                                        <span>RUSAK</span>
                                    </div>
                                    <div class="dropdown-option" data-value="hilang" onclick="window.LaporPublic.selectKondisi('hilang', 'HILANG / DICURI', 'fa-ban')">
                                        <i class="fas fa-ban"></i>
                                        <span>HILANG / DICURI</span>
                                    </div>
                                    <div class="dropdown-option" data-value="pindah" onclick="window.LaporPublic.selectKondisi('pindah', 'PINDAH POSISI', 'fa-location-dot')">
                                        <i class="fas fa-location-dot"></i>
                                        <span>PINDAH POSISI</span>
                                    </div>
                                    <div class="dropdown-option" data-value="lainnya" onclick="window.LaporPublic.selectKondisi('lainnya', 'LAINNYA', 'fa-ellipsis-h')">
                                        <i class="fas fa-ellipsis-h"></i>
                                        <span>LAINNYA</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="kondisi_aset" id="kondisi_aset_input" value="">
                        </div>
                    </div>
                </div>

                {{-- SUBMIT BUTTON --}}
                <div class="mt-12">
                    <button type="submit" id="submitBtn" class="group relative w-full bg-[#0B2A4A] text-[#F4B400] py-5 rounded-[1.25rem] text-xs font-black uppercase tracking-[0.3em] shadow-xl hover:bg-black transition-all active:scale-95">
                        <span class="flex items-center justify-center gap-3">
                            Kirim Laporan Resmi
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </span>
                    </button>
                    <div class="flex flex-col items-center mt-8 gap-2">
                        <img src="../img/logo_dishub_kbb.png" class="h-20">
                        <p class="text-[8px] text-slate-400 font-black uppercase tracking-[0.4em]">Dishub Kabupaten Bandung Barat • 2026</p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    // Isolasi visual Leaflet Vanilla menggunakan kapsul IIFE (GRASP Low Coupling)
    (function() {
        let map, marker, radiusCircle;
        const radiusMeters = 100;
        let alamatTerkunci = false;
        let daftarAset = [];
        let selectedAssetId = null;
        let selectedAssetIdAsset = null;
        let selectedAssetNama = '';
        let assetMarkers = [];
        let currentLat = null;
        let currentLng = null;

        const lightMapLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20,
            minZoom: 0
        });

        function toggleKondisiDropdown() {
            const options = document.getElementById('kondisiOptions');
            const selected = document.querySelector('#kondisiDropdown .dropdown-selected');
            if (options && selected) {
                options.classList.toggle('open');
                selected.classList.toggle('open');
            }
        }

        function selectKondisi(value, text, iconClass) {
            document.getElementById('kondisi_aset_input').value = value;

            const selectedDiv = document.querySelector('#kondisiDropdown .dropdown-selected');
            const selectedTextDiv = selectedDiv ? selectedDiv.querySelector('.selected-text') : null;
            if (selectedTextDiv) {
                selectedTextDiv.innerHTML = `<i class="fas ${iconClass}"></i><span id="selectedKondisiText" class="text-slate-800">${text}</span>`;
            }

            const options = document.querySelectorAll('#kondisiOptions .dropdown-option');
            options.forEach(opt => opt.classList.remove('selected'));

            const selectedOption = Array.from(options).find(opt => opt.dataset.value === value);
            if (selectedOption) selectedOption.classList.add('selected');

            const dropdownOptions = document.getElementById('kondisiOptions');
            const selectedBtn = document.querySelector('#kondisiDropdown .dropdown-selected');
            if (dropdownOptions) dropdownOptions.classList.remove('open');
            if (selectedBtn) selectedBtn.classList.remove('open');
        }

        function toggleAssetDropdown() {
            const options = document.getElementById('assetDropdownOptions');
            if (options) options.classList.toggle('open');
        }

        function pilihAset(assetId, assetIdAsset, assetNama) {
            selectedAssetId = assetId;
            selectedAssetIdAsset = assetIdAsset;
            selectedAssetNama = assetNama;
            document.getElementById('selected_asset_id').value = assetId;
            document.getElementById('selected_id_asset').value = assetIdAsset;
            document.getElementById('judul_laporan_hidden').value = assetNama;
            document.getElementById('selectedAssetText').innerHTML = `<i class="fas fa-check-circle text-green-600"></i> ${assetNama}`;

            const options = document.getElementById('assetDropdownOptions');
            if (options) options.classList.remove('open');

            if (window.currentOpenPopup) {
                window.currentOpenPopup.close();
                window.currentOpenPopup = null;
            }
        }

        async function cariAsetTerdekat(lat, lng) {
            if (alamatTerkunci) return;

            try {
                const response = await fetch(`/laporan/aset-terdekat?lat=${lat}&lng=${lng}&radius=${radiusMeters}`);
                const data = await response.json();
                daftarAset = data.aset || [];

                assetMarkers.forEach(marker => marker.remove());
                assetMarkers = [];

                daftarAset.forEach(aset => {
                    const assetIcon = L.divIcon({
                        className: 'asset-marker-icon',
                        html: `<div style="background-color: #F4B400; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #0B2A4A; box-shadow: 0 2px 5px rgba(0,0,0,0.2); cursor: pointer;"><i class="fas fa-box" style="font-size: 12px; color: #0B2A4A;"></i></div>`,
                        iconSize: [28, 28],
                        iconAnchor: [14, 14]
                    });

                    const m = L.marker([aset.lat, aset.lng], {
                        icon: assetIcon
                    }).addTo(map);
                    m.bindPopup(`
                        <div style="min-width: 200px; padding: 8px;">
                            <b style="font-size: 12px;">${aset.nama}</b><br>
                            <span style="font-size: 10px; color: #666;">ID: ${aset.id_asset}</span><br>
                            <button type="button" onclick="window.LaporPublic.pilihAset(${aset.id}, '${aset.id_asset}', '${aset.nama.replace(/'/g, "\\'")}')" 
                                style="background:#0B2A4A; color:#F4B400; padding:6px 14px; border-radius:8px; font-size:11px; font-weight:bold; margin-top:10px; width:100%; cursor:pointer;">
                                Pilih Aset Ini
                            </button>
                        </div>
                    `);

                    m.on('popupopen', function(e) {
                        window.currentOpenPopup = e.popup;
                    });

                    m.on('click', function() {
                        pilihAset(aset.id, aset.id_asset, aset.nama);
                    });
                    assetMarkers.push(m);
                });

                const dropdownContainer = document.getElementById('assetDropdownContainer');
                const noAssetWarning = document.getElementById('noAssetWarning');
                const assetCountInfo = document.getElementById('assetCountInfo');
                const dropdownOptions = document.getElementById('assetDropdownOptions');

                if (daftarAset.length > 0) {
                    dropdownContainer.style.display = 'block';
                    noAssetWarning.style.display = 'none';

                    let optionsHtml = '';
                    daftarAset.forEach(aset => {
                        optionsHtml += `
                            <div class="asset-dropdown-option" onclick="window.LaporPublic.pilihAset(${aset.id}, '${aset.id_asset}', '${aset.nama.replace(/'/g, "\\'")}')">
                                <i class="fas fa-box"></i>
                                <div class="flex flex-col">
                                    <span class="font-bold text-[#1E293B]">${aset.nama}</span>
                                    <span class="text-[9px] text-slate-500">${aset.id_asset}</span>
                                </div>
                            </div>
                        `;
                    });
                    dropdownOptions.innerHTML = optionsHtml;

                    if (daftarAset.length === 1) {
                        pilihAset(daftarAset[0].id, daftarAset[0].id_asset, daftarAset[0].nama);
                    }

                    if (daftarAset.length > 1) {
                        assetCountInfo.innerText = `Terdapat ${daftarAset.length} aset dalam radius ini. Silakan pilih yang sesuai.`;
                        assetCountInfo.style.display = 'block';
                    } else {
                        assetCountInfo.style.display = 'none';
                    }
                } else {
                    dropdownContainer.style.display = 'none';
                    noAssetWarning.style.display = 'block';
                    selectedAssetNama = '';
                    selectedAssetId = null;
                    selectedAssetIdAsset = null;
                    document.getElementById('selectedAssetText').innerHTML = '<i class="fas fa-building"></i> Pilih Aset';
                }
            } catch (error) {
                console.error('Gagal mencari aset terdekat:', error);
            }
        }

        async function updatePosition(lat, lng) {
            if (alamatTerkunci) return;

            currentLat = lat;
            currentLng = lng;

            if (marker) {
                marker.setLatLng([lat, lng]);
                map.panTo([lat, lng]);
            }

            if (radiusCircle) {
                radiusCircle.setLatLng([lat, lng]);
            }

            document.getElementById('inp_lat').value = lat;
            document.getElementById('inp_lng').value = lng;
            document.getElementById('inp_koordinat').value = lat + ',' + lng;

            document.getElementById('alamatText').innerText = "MENDAPATKAN ALAMAT...";

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                const data = await response.json();
                const fullAddress = data.display_name || "LOKASI TIDAK TERJANGKAU";
                document.getElementById('alamatText').innerText = fullAddress.toUpperCase();
                document.getElementById('inp_alamat').value = fullAddress;
            } catch (e) {
                const fallback = `KOORDINAT: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                document.getElementById('alamatText').innerText = fallback;
                document.getElementById('inp_alamat').value = fallback;
            }

            await cariAsetTerdekat(lat, lng);
        }

        function previewImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('image_preview');
                output.src = reader.result;
                document.getElementById('image_preview_container').classList.remove('hidden');
            }
            reader.readAsDataURL(file);
        }

        document.addEventListener("DOMContentLoaded", function() {
            map = L.map('miniMap', {
                zoomControl: true,
                attributionControl: false
            }).setView([-6.8431, 107.4912], 16);
            lightMapLayer.addTo(map);

            L.control.zoom({
                position: 'topright'
            }).addTo(map);

            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: "<div class='marker-pin'></div>",
                iconSize: [30, 30],
                iconAnchor: [15, 30]
            });

            marker = L.marker([-6.8431, 107.4912], {
                draggable: true,
                icon: customIcon
            }).addTo(map);

            radiusCircle = L.circle([-6.8431, 107.4912], {
                color: '#14b8a6',
                fillColor: '#14b8a6',
                fillOpacity: 0.15,
                radius: radiusMeters,
                weight: 3,
                opacity: 0.8,
                className: 'radius-circle-glow'
            }).addTo(map);

            map.on('click', function(e) {
                if (!alamatTerkunci) {
                    updatePosition(e.latlng.lat, e.latlng.lng);
                }
            });

            marker.on('dragend', function() {
                if (!alamatTerkunci) {
                    let pos = marker.getLatLng();
                    updatePosition(pos.lat, pos.lng);
                }
            });

            document.getElementById('btnAktifkanGPS').addEventListener('click', function() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(pos) {
                        updatePosition(pos.coords.latitude, pos.coords.longitude);
                        map.setView([pos.coords.latitude, pos.coords.longitude], 18);
                    }, function(error) {
                        console.error('Gagal ambil GPS:', error);
                        alert('Gagal mengakses GPS. Pastikan GPS menyala.');
                    }, {
                        enableHighAccuracy: true
                    });
                } else {
                    alert('Browser tidak mendukung GPS');
                }
            });

            const toggleBtn = document.getElementById('btnToggleKunci');
            toggleBtn.addEventListener('click', function() {
                if (alamatTerkunci) {
                    alamatTerkunci = false;
                    toggleBtn.innerHTML = '<i class="fas fa-lock mr-2"></i> Kunci Lokasi';
                    toggleBtn.classList.remove('bg-gray-600');
                    toggleBtn.classList.add('bg-amber-600');
                    alert('Lokasi sudah dibuka. Posisi dapat berubah.');
                } else {
                    if (currentLat && currentLng) {
                        alamatTerkunci = true;
                        toggleBtn.innerHTML = '<i class="fas fa-lock-open mr-2"></i> Buka Kunci';
                        toggleBtn.classList.remove('bg-amber-600');
                        toggleBtn.classList.add('bg-gray-600');
                        alert('Lokasi telah dikunci. Posisi tidak akan berubah.');
                    } else {
                        alert('Silakan aktifkan GPS atau pilih lokasi di peta terlebih dahulu');
                    }
                }
            });

            document.getElementById('laporanForm').addEventListener('submit', function() {
                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="flex items-center justify-center gap-2"><svg class="animate-spin h-5 w-5 text-[#F4B400]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Memproses Laporan...</span>';
            });
        });

        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('kondisiDropdown');
            if (dropdown && !dropdown.contains(event.target)) {
                const options = document.getElementById('kondisiOptions');
                const selected = document.querySelector('#kondisiDropdown .dropdown-selected');
                if (options) options.classList.remove('open');
                if (selected) selected.classList.remove('open');
            }

            const assetDropdown = document.getElementById('assetDropdown');
            if (assetDropdown && !assetDropdown.contains(event.target)) {
                const options = document.getElementById('assetDropdownOptions');
                if (options) options.classList.remove('open');
            }
        });

        // Ekspos fungsi visual spesifik ke dalam scope lokal terenkapsulasi (Protected Variations)
        window.LaporPublic = {
            toggleKondisiDropdown: toggleKondisiDropdown,
            selectKondisi: selectKondisi,
            toggleAssetDropdown: toggleAssetDropdown,
            pilihAset: pilihAset,
            previewImage: previewImage
        };
    })();
</script>

<style>
    @keyframes marquee {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-50%);
        }
    }

    .animate-marquee {
        animation: marquee 30s linear infinite;
    }

    .marker-pin {
        width: 24px;
        height: 24px;
        border-radius: 50% 50% 50% 0;
        background: #0B2A4A;
        position: absolute;
        transform: rotate(-45deg);
        left: 50%;
        top: 50%;
        margin: -24px 0 0 -12px;
        border: 3px solid #F4B400;
        box-shadow: 0 10px 15px rgba(0, 0, 0, 0.4);
    }

    .leaflet-container {
        background: #f1f5f9 !important;
        border-radius: 2rem;
    }

    input::placeholder {
        color: #94a3b8 !important;
        font-size: 11px;
    }

    html {
        scroll-behavior: smooth;
    }

    .radius-circle-glow {
        stroke-dasharray: 8 12;
        animation: rotateDash 4s linear infinite, pulseGlow 2s ease-in-out infinite;
        filter: drop-shadow(0 0 5px rgba(20, 184, 166, 0.5));
    }

    @keyframes rotateDash {
        to {
            stroke-dashoffset: -40;
        }
    }

    @keyframes pulseGlow {
        0% {
            stroke-opacity: 0.6;
            fill-opacity: 0.1;
            stroke-width: 2;
        }

        50% {
            stroke-opacity: 1;
            fill-opacity: 0.25;
            stroke-width: 4;
        }

        100% {
            stroke-opacity: 0.6;
            fill-opacity: 0.1;
            stroke-width: 2;
        }
    }

    .asset-marker-icon div {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .asset-marker-icon div:hover {
        transform: scale(1.15);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }
</style>
@endsection