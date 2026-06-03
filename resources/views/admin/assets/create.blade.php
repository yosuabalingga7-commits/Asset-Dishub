@extends('layouts.app')

@section('content')

@php
    $from = request('from', 'map');
@endphp

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    .inter-font-scope, 
    .inter-font-scope input, 
    .inter-font-scope select, 
    .inter-font-scope textarea, 
    .inter-font-scope button {
        font-family: 'Inter', sans-serif !important;
    }
    
    /* PERBAIKAN: Hilangkan uppercase default browser */
    select, option {
        text-transform: none !important;
    }
    
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    .main-container-scroll {
        height: 100vh;
        overflow-y: auto;
        scrollbar-gutter: stable;
    }
    
    .main-container-scroll {
        margin-left: 0 !important;
        width: 100% !important;
    }
    
    body > div:first-child {
        margin-left: 0 !important;
    }
    
    .icon-option {
        transition: all 0.2s ease;
    }
    .icon-option:hover {
        transform: scale(1.05);
    }
    
    .icon-label {
        font-size: 9px;
        font-weight: 600;
        color: #475569;
        text-align: center;
        margin-top: 4px;
        letter-spacing: 0.3px;
    }
    .peer-checked ~ .icon-label {
        color: #6366f1;
        font-weight: 800;
    }
    
    /* Hilangkan bingkai map - full lebar */
    .map-wrapper {
        border-radius: 20px;
        overflow: hidden;
        height: 580px;
        width: 100%;
    }
    #mapPicker {
        width: 100%;
        height: 100%;
        z-index: 0;
    }
    
    /* Perlebar kolom map dan perkecil padding */
    .form-container {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .map-column {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
</style>

<div class="main-container-scroll bg-slate-50 p-4 inter-font-scope custom-scroll" style="margin-left: 0; width: 100%;">
    <div class="max-w-[1400px] mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Registrasi Aset Baru</h1>
                <p class="text-slate-500 font-bold text-xs mt-1 tracking-widest opacity-70">Manajemen Gis Dishub KBB</p>
            </div>
            @if($from == 'list')
                <a href="{{ route('assets.list') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-[10px] tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                    ← Kembali ke daftar aset
                </a>
            @else
                <a href="{{ route('assets.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-[10px] tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                    ← Kembali ke peta
                </a>
            @endif
        </div>

        @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-2xl shadow-sm">
            <p class="font-black text-[10px] mb-2 tracking-widest">terjadi kesalahan input:</p>
            <ul class="list-disc ml-5 text-xs font-medium">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('assets.store', ['from' => $from]) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-12 gap-4 mb-20">
            @csrf

            <div class="col-span-12 lg:col-span-5 space-y-6">
                <div class="bg-white p-6 rounded-[24px] shadow-sm border border-slate-200">
                    <div class="grid grid-cols-2 gap-5">
                        
                        {{-- Hidden input untuk ID Aset otomatis --}}
                        <input type="hidden" name="id_asset" id="id_asset_hidden" value="">

                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wider mb-2 ml-1">Nama Perangkat</label>
                            <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="contoh: cctv simpang padalarang 01" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-indigo-500 transition-all font-semibold text-slate-700">
                        </div>

                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wider mb-2 ml-1">Alamat Terdeteksi (Klik di Peta)</label>
                            <textarea name="alamat" id="alamatInput" rows="2" readonly placeholder="Alamat akan muncul otomatis saat Anda memilih titik di peta" class="w-full px-5 py-3.5 bg-slate-100 border border-slate-200 rounded-xl outline-none font-semibold text-slate-600 text-sm leading-relaxed tracking-tight">{{ old('alamat') }}</textarea>
                        </div>

                        {{-- Kategori Utama (col-span-2 = full width, sejajar dengan Jenis Aset) --}}
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wider mb-2 ml-1">Kategori Utama</label>
                            <select name="kategori" id="kat_select" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none font-semibold text-slate-700 appearance-none cursor-pointer focus:border-indigo-500">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="PJ" data-nama="Penerangan Jalan Umum (PJU)">Penerangan Jalan Umum (PJU)</option>
                                <option value="PL" data-nama="Perlengkapan Jalan">Perlengkapan Jalan</option>
                                <option value="FL" data-nama="Fasilitas Lalu Lintas">Fasilitas Lalu Lintas</option>
                                <option value="PP" data-nama="Pengendalian & Pengawasan">Pengendalian & Pengawasan</option>
                                <option value="PT" data-nama="Prasarana Transportasi">Prasarana Transportasi</option>
                            </select>
                            {{-- Hidden input untuk menyimpan nama kategori lengkap --}}
                            <input type="hidden" name="kategori_nama" id="kategori_nama" value="{{ old('kategori_nama') }}">
                        </div>

                        {{-- Jenis Aset dengan inisial 1 karakter dari huruf pertama kata pertama --}}
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wider mb-2 ml-1">Jenis Aset</label>
                            <select name="jenis" id="jenis_select" required disabled class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none font-semibold text-slate-700 appearance-none cursor-not-allowed opacity-50 focus:border-indigo-500 transition-all">
                                <option value="">-- Pilih Kategori Dahulu --</option>
                            </select>
                            {{-- Hidden input untuk menyimpan kode jenis 1 karakter --}}
                            <input type="hidden" name="jenis_kode" id="jenis_kode" value="{{ old('jenis_kode') }}">
                        </div>

                        <div class="col-span-2 py-2">
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wider mb-3 ml-1 text-center text-indigo-600">visual marker di peta (terpilih otomatis & bisa diganti)</label>
                            <div class="flex justify-center flex-wrap gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner" id="iconPicker">
                                @php
    // ========== PERUBAHAN 1: Label icon diubah jadi Huruf Kapital di Awal ==========
    $icons = [
        'Penerangan Jalan Umum (PJU)' => [
            'fa-lightbulb' => 'Lampu Pju',
            'fa-solar-panel' => 'Solar Cell',
            'fa-charging-station' => 'Panel Box Pju',
            'fa-bolt' => 'Kabel Udara Pju',
            'fa-battery-full' => 'Baterai Solar Cell',
        ],
        'Perlengkapan Jalan' => [
            'fa-triangle-exclamation' => 'Rambu Peringatan',
            'fa-circle-exclamation' => 'Rambu Larangan',
            'fa-circle-info' => 'Rambu Petunjuk',
            'fa-hand-peace' => 'Rambu Perintah',
            'fa-map' => 'Rppj (Papan Jurusan)',
            'fa-arrow-rotate-left' => 'Cermin Tikungan',
            'fa-shield-halved' => 'Guardrail',
            'fa-road-circle-exclamation' => 'Delineator',
            'fa-location-dot' => 'Patok Kilometer',
        ],
        'Fasilitas Lalu Lintas' => [
            'fa-traffic-light' => 'Apill (Traffic Light)',
            'fa-exclamation-triangle' => 'Warning Light',
            'fa-person-walking-arrow-right' => 'Marka Zebra Cross',
            'fa-road' => 'Marka Rhk',
            'fa-square-parking' => 'Marka Parkir',
            'fa-circle' => 'Paku Jalan',
            'fa-water' => 'Water Barrier',
            'fa-triangle-exclamation' => 'Traffic Cone',
            'fa-shield-virus' => 'Road Barrier Beton',
        ],
        'Pengendalian dan Pengawasan' => [
            'fa-video' => 'Cctv Surveilans',
            'fa-camera' => 'Cctv E-Tle',
            'fa-display' => 'Vms (Papan Digital)',
            'fa-microchip' => 'Atcs Controller',
            'fa-gun' => 'Speed Gun',
            'fa-bullhorn' => 'Voice Announcer',
            'fa-tower-broadcast' => 'Radio Komunikasi',
        ],
        'Prasarana Transportasi' => [
            'fa-bus' => 'Halte Bus',
            'fa-building' => 'Terminal',
            'fa-warehouse' => 'Gedung Pkb',
            'fa-person-walking' => 'Jembatan Penyeberangan (Jpo)',
            'fa-square-parking' => 'Parkir Off-Street',
            'fa-truck-front' => 'Pool Bus',
        ],
    ];

    $categoryIcons = [
        'Penerangan Jalan Umum (PJU)' => 'fa-lightbulb',
        'Perlengkapan Jalan' => 'fa-triangle-exclamation',
        'Fasilitas Lalu Lintas' => 'fa-traffic-light',
        'Pengendalian dan Pengawasan' => 'fa-video',
        'Prasarana Transportasi' => 'fa-bus',
    ];
@endphp
                               <div class="flex flex-col gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner" id="iconPicker">
    @foreach($icons as $kategori => $items)
    <div class="icon-category-group border border-slate-200 rounded-xl bg-white overflow-hidden">
        <button type="button" class="category-dropdown-btn w-full flex justify-between items-center p-3 bg-gradient-to-r from-slate-50 to-white hover:from-slate-100 transition-all" data-category="{{ Str::slug($kategori) }}">
            <div class="flex items-center gap-3">
                <i class="fas {{ $categoryIcons[$kategori] ?? 'fa-folder' }} text-indigo-500 text-lg w-5"></i>
                <span class="font-black text-xs text-slate-700">{{ $kategori }}</span>
                <span class="text-[9px] text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ count($items) }} icon</span>
            </div>
            <svg class="dropdown-arrow w-4 h-4 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        
        <div class="category-content hidden p-3 bg-slate-50/50 border-t border-slate-100" data-category="{{ Str::slug($kategori) }}">
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
                @foreach($items as $icon => $label)
                <label class="cursor-pointer group relative flex flex-col items-center icon-option p-1">
                    <input type="radio" name="icon_marker" value="{{ $icon }}" class="hidden peer" {{ old('icon_marker') == $icon ? 'checked' : '' }}>
                    <div class="w-10 h-10 flex items-center justify-center rounded-lg border-2 border-slate-200 bg-white shadow-sm peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:scale-105 transition-all">
                        <i class="fas {{ $icon }} text-slate-700 peer-checked:text-indigo-600 text-lg"></i>
                    </div>
                    <span class="icon-label mt-1 text-[8px] font-bold text-slate-500 text-center">{{ $label }}</span>
                </label>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wider mb-2 ml-1">Kondisi Saat Ini</label>
                            <select name="status" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none font-semibold text-slate-700 focus:border-indigo-500">
                                <option value="Baik" {{ old('status') == 'Baik' ? 'selected' : '' }}>🟢 Baik</option>
                                <option value="Proses Perbaikan" {{ old('status') == 'Proses Perbaikan' ? 'selected' : '' }}>🔵 Proses Perbaikan</option>
                                <option value="Rusak" {{ old('status') == 'Rusak' ? 'selected' : '' }}>🟡 Rusak</option>
                                <option value="Kritis" {{ old('status') == 'Kritis' ? 'selected' : '' }}>🔴 Kritis</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wider mb-2 ml-1">Foto Fisik Aset</label>
                            <input type="file" name="foto" id="foto_input" required accept="image/jpeg,image/png,image/jpg,image/webp" class="w-full text-xs text-slate-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer bg-slate-50 border border-slate-200 rounded-xl transition-all">
                        </div>
                    </div>
                </div>

                <div id="photoPreviewContainer" class="hidden bg-white p-4 rounded-[24px] border border-slate-200 shadow-sm transition-all">
                    <p class="text-[9px] font-bold text-slate-400 mb-2 ml-1">preview foto:</p>
                    <img id="preview" class="w-full h-64 object-cover rounded-xl shadow-inner border border-slate-100">
                </div>
            </div>

            <div class="col-span-12 lg:col-span-7 flex flex-col gap-4 map-column">
                <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-sm flex gap-3">
                    <input type="text" id="searchCoords" placeholder="Cari lokasi aset / masukkan koordinat..." 
                           class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-700 outline-none focus:border-indigo-500">
                    <button type="button" onclick="searchByCoords()" 
                            class="bg-slate-900 text-white px-6 py-2 rounded-xl text-[10px] font-black tracking-widest hover:bg-slate-800 transition-all">
                        Cari
                    </button>
                </div>

                <!-- PERUBAHAN: Hilangkan bingkai putih pada map - full lebar -->
                <div class="map-wrapper">
                    <div id="mapPicker"></div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="block text-[9px] font-black text-slate-400 mb-1 text-center">Latitude</span>
                        <input type="text" name="lat" id="latInput" value="{{ old('lat') }}" readonly required placeholder="0.000000" class="w-full text-center font-bold text-slate-800 bg-transparent outline-none">
                    </div>
                    <div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="block text-[9px] font-black text-slate-400 mb-1 text-center">Longitude</span>
                        <input type="text" name="lng" id="lngInput" value="{{ old('lng') }}" readonly required placeholder="0.000000" class="w-full text-center font-bold text-slate-800 bg-transparent outline-none">
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-[20px] font-bold text-xs tracking-widest shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-95">
                    Daftarkan Aset Ke Sistem
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/polygon-layers.js') }}"></script>

<script>
    // Data Jenis dengan kode 1 karakter dari HURUF PERTAMA kata pertama
    const dbJenis = {
        'Penerangan Jalan Umum (PJU)': [
            {nama: 'Lampu Pju', kode: 'L'},
            {nama: 'Tiang PJU Galvanis', kode: 'T'},
            {nama: 'Tiang PJU Dekoratif', kode: 'T'},
            {nama: 'Lampu LED', kode: 'L'},
            {nama: 'Lampu Solar Cell', kode: 'L'},
            {nama: 'Panel Box PJU', kode: 'P'},
            {nama: 'Kabel Udara PJU', kode: 'K'},
            {nama: 'Baterai Solar Cell', kode: 'B'},
            {nama: 'Modul Solar Cell', kode: 'M'}
        ],
        'Perlengkapan Jalan': [
            {nama: 'Rambu Larangan', kode: 'R'},
            {nama: 'Rambu Peringatan', kode: 'R'},
            {nama: 'Rambu Perintah', kode: 'R'},
            {nama: 'Rambu Petunjuk', kode: 'R'},
            {nama: 'RPPJ (Papan Jurusan)', kode: 'R'},
            {nama: 'Cermin Tikungan', kode: 'C'},
            {nama: 'Guardrail', kode: 'G'},
            {nama: 'Delineator', kode: 'D'},
            {nama: 'Patok Kilometer', kode: 'P'},
            {nama: 'Zebra Cross', kode: 'Z'}
        ],
        'Fasilitas Lalu Lintas': [
            {nama: 'APILL (Traffic Light)', kode: 'A'},
            {nama: 'Warning Light', kode: 'W'},
            {nama: 'Marka Zebra Cross', kode: 'M'},
            {nama: 'Marka RHK', kode: 'M'},
            {nama: 'Marka Parkir', kode: 'M'},
            {nama: 'Paku Jalan', kode: 'P'},
            {nama: 'Water Barrier', kode: 'W'},
            {nama: 'Traffic Cone', kode: 'T'},
            {nama: 'Road Barrier Beton', kode: 'R'}
        ],
        'Pengendalian & Pengawasan': [
            {nama: 'CCTV Surveilans', kode: 'C'},
            {nama: 'CCTV E-TLE', kode: 'C'},
            {nama: 'VMS (Papan Digital)', kode: 'V'},
            {nama: 'ATCS Controller', kode: 'A'},
            {nama: 'Speed Gun', kode: 'S'},
            {nama: 'Voice Announcer', kode: 'V'},
            {nama: 'Radio Komunikasi', kode: 'R'}
        ],
        'Prasarana Transportasi': [
            {nama: 'Halte Bus', kode: 'H'},
            {nama: 'Terminal', kode: 'T'},
            {nama: 'Gedung PKB', kode: 'G'},
            {nama: 'Jembatan Penyeberangan (JPO)', kode: 'J'},
            {nama: 'Parkir Off-Street', kode: 'P'},
            {nama: 'Pool Bus', kode: 'P'}
        ]
    };

    const iconMapping = {
        'Penerangan Jalan Umum (PJU)': 'fa-lightbulb',
        'Perlengkapan Jalan': 'fa-triangle-exclamation',
        'Fasilitas Lalu Lintas': 'fa-traffic-light',
        'Pengendalian dan Pengawasan': 'fa-video',
        'Prasarana Transportasi': 'fa-bus',
        'tiang pju galvanis': 'fa-lightbulb',
        'tiang pju dekoratif': 'fa-lightbulb',
        'lampu led': 'fa-lightbulb',
        'lampu solar cell': 'fa-solar-panel',
        'panel box pju': 'fa-charging-station',
        'kabel udara pju': 'fa-bolt',
        'baterai solar cell': 'fa-battery-full',
        'modul solar cell': 'fa-solar-panel',
        'rambu larangan': 'fa-circle-exclamation',
        'rambu peringatan': 'fa-triangle-exclamation',
        'rambu perintah': 'fa-hand-peace',
        'rambu petunjuk': 'fa-circle-info',
        'rppj (papan jurusan)': 'fa-map',
        'cermin tikungan': 'fa-arrow-rotate-left',
        'guardrail': 'fa-shield-halved',
        'delineator': 'fa-road-circle-exclamation',
        'patok kilometer': 'fa-location-dot',
        'apill (traffic light)': 'fa-traffic-light',
        'warning light': 'fa-exclamation-triangle',
        'marka zebra cross': 'fa-person-walking-arrow-right',
        'marka rhk': 'fa-road',
        'marka parkir': 'fa-square-parking',
        'paku jalan': 'fa-circle',
        'water barrier': 'fa-water',
        'traffic cone': 'fa-triangle-exclamation',
        'road barrier beton': 'fa-shield-virus',
        'cctv surveilans': 'fa-video',
        'cctv e-tle': 'fa-camera',
        'vms (papan digital)': 'fa-display',
        'atcs controller': 'fa-microchip',
        'speed gun': 'fa-gun',
        'voice announcer': 'fa-bullhorn',
        'radio komunikasi': 'fa-tower-broadcast',
        'halte bus': 'fa-bus',
        'terminal': 'fa-building',
        'gedung pkb': 'fa-warehouse',
        'jembatan penyeberangan (jpo)': 'fa-person-walking',
        'parkir off-street': 'fa-square-parking',
        'pool bus': 'fa-truck-front',
        'zebra cross': 'fa-person-walking-arrow-right'
    };

    const katSelect = document.getElementById('kat_select');
    const jenisSelect = document.getElementById('jenis_select');
    const kategoriNamaHidden = document.getElementById('kategori_nama');
    const jenisKodeHidden = document.getElementById('jenis_kode');
    const iconRadios = document.getElementsByName('icon_marker');
    const alamatInput = document.getElementById('alamatInput');
    const idAssetHidden = document.getElementById('id_asset_hidden');

    // Fungsi untuk generate ID otomatis (disimpan ke hidden input)
    function generateAndSetId() {
        const kategoriKode = katSelect.value;
        const jenisKode = jenisKodeHidden ? jenisKodeHidden.value : '';
        
        if (kategoriKode && jenisKode) {
            const tahun = new Date().getFullYear();
            const prefix = kategoriKode + jenisKode + '-' + tahun + '-';
            
            // Fetch ke server untuk mendapatkan nomor urut terbaru
            fetch(`/api/next-asset-number?prefix=${encodeURIComponent(prefix)}`)
                .then(response => response.json())
                .then(data => {
                    const nomorUrut = data.nextNumber;
                    const generatedId = prefix + nomorUrut;
                    if (idAssetHidden) {
                        idAssetHidden.value = generatedId;
                    }
                })
                .catch(() => {
                    // Fallback
                    if (idAssetHidden) {
                        idAssetHidden.value = prefix + '001';
                    }
                });
        } else {
            if (idAssetHidden) {
                idAssetHidden.value = '';
            }
        }
    }

    // Event handler untuk kategori
    katSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const kategoriKode = this.value;
        const kategoriNama = selectedOption.getAttribute('data-nama') || '';
        
        // Simpan nama kategori lengkap ke hidden input
        if (kategoriNamaHidden) {
            kategoriNamaHidden.value = kategoriNama;
        }
        
        // Update dropdown jenis
        jenisSelect.innerHTML = '<option value="">-- pilih jenis --</option>';
        
        if(kategoriKode && kategoriNama && dbJenis[kategoriNama]) {
            jenisSelect.disabled = false;
            jenisSelect.classList.remove('cursor-not-allowed', 'opacity-50');
            dbJenis[kategoriNama].forEach(jenis => {
                const opt = document.createElement('option');
                opt.value = jenis.nama;
                opt.setAttribute('data-kode', jenis.kode);
                opt.text = jenis.nama;
                jenisSelect.add(opt);
            });
        } else {
            jenisSelect.disabled = true;
            jenisSelect.classList.add('cursor-not-allowed', 'opacity-50');
            if (jenisKodeHidden) jenisKodeHidden.value = '';
        }
        
        // Reset preview ID
        jenisKodeHidden.value = '';
        generateAndSetId();
        autoSuggestIcon();
    });

    // Event handler untuk jenis
    jenisSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const jenisKode = selectedOption ? selectedOption.getAttribute('data-kode') : '';
        
        if (jenisKodeHidden) {
            jenisKodeHidden.value = jenisKode || '';
        }
        
        // Generate ulang ID
        generateAndSetId();
        autoSuggestIcon();
    });

    function autoSuggestIcon() {
        const katOption = katSelect.options[katSelect.selectedIndex];
        const katNama = katOption ? katOption.getAttribute('data-nama') : '';
        const jenNama = jenisSelect.value.toLowerCase();
        let target = '';
        
        if(iconMapping[katNama]) target = iconMapping[katNama];
        
        for(const [key, icon] of Object.entries(iconMapping)) {
            if(jenNama.includes(key.toLowerCase())) {
                target = icon;
                break;
            }
        }
        
        if(!target) {
            if(katNama === 'Penerangan Jalan Umum (PJU)') target = 'fa-lightbulb';
            else if(katNama === 'Perlengkapan Jalan') target = 'fa-triangle-exclamation';
            else if(katNama === 'Fasilitas Lalu Lintas') target = 'fa-traffic-light';
            else if(katNama === 'Pengendalian & Pengawasan') target = 'fa-video';
            else if(katNama === 'Prasarana Transportasi') target = 'fa-bus';
        }
        
        if(target) {
            for(let i = 0; i < iconRadios.length; i++) {
                if(iconRadios[i].value === target) {
                    iconRadios[i].checked = true;
                    break;
                }
            }
        }
    }

    function initIconCategoryDropdown() {
        const dropdownBtns = document.querySelectorAll('.category-dropdown-btn');
        
        function toggleDropdown(btn) {
            const categorySlug = btn.dataset.category;
            const content = document.querySelector(`.category-content[data-category="${categorySlug}"]`);
            const arrow = btn.querySelector('.dropdown-arrow');
            
            if (content) {
                content.classList.toggle('hidden');
                if (!content.classList.contains('hidden')) {
                    arrow.style.transform = 'rotate(180deg)';
                } else {
                    arrow.style.transform = 'rotate(0deg)';
                }
            }
        }
        
        dropdownBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleDropdown(this);
            });
        });
    }

    async function updateAlamat(lat, lng) {
        alamatInput.value = "mendeteksi lokasi...";
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`);
            const data = await response.json();
            alamatInput.value = data.display_name || "alamat tidak ditemukan";
        } catch (error) {
            alamatInput.value = "gagal memuat alamat";
        }
    }

    const darkMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 20 });
    const streetMap = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', { maxZoom: 20 });
    const satelliteMap = L.tileLayer('https://mt{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['0', '1', '2', '3']
    });
    const googleStreetDefault = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20, subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    });

    const map = L.map('mapPicker', {
        center: [{{ $mapConfig['center'][0] ?? -6.8441 }}, {{ $mapConfig['center'][1] ?? 107.4917 }}],
        zoom: {{ $mapConfig['defaultZoom'] ?? 13 }},
        layers: [googleStreetDefault],
        zoomControl: true,
        attributionControl: false
    });

    if (typeof initPolygonLayers === 'function') {
        initPolygonLayers(map);
    }

    const baseMaps = {
        "google street": googleStreetDefault,
        "dark mode": darkMap,
        "streets": streetMap,
        "satellite": satelliteMap
    };

    L.control.layers(baseMaps).addTo(map);

    let marker;

    function setLocation(latlng, skipAlamat = false) {
        document.getElementById('latInput').value = latlng.lat.toFixed(8);
        document.getElementById('lngInput').value = latlng.lng.toFixed(8);
        
        if (marker) {
            marker.setLatLng(latlng);
        } else {
            marker = L.marker(latlng, { draggable: true }).addTo(map);
            marker.on('dragend', function(event) {
                const pos = event.target.getLatLng();
                setLocation(pos);
            });
        }
        
        if(!skipAlamat) {
            updateAlamat(latlng.lat, latlng.lng);
        }
    }

    map.on('click', function(e) {
        setLocation(e.latlng);
    });

    window.searchByCoords = async function() {
        const val = document.getElementById('searchCoords').value.trim();
        if(!val) return alert("masukkan nama tempat atau koordinat!");

        const coordRegex = /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/;

        if (coordRegex.test(val)) {
            const split = val.split(',');
            const lat = parseFloat(split[0].trim());
            const lng = parseFloat(split[1].trim());
            const pos = L.latLng(lat, lng);
            map.flyTo(pos, 18);
            setLocation(pos);
        } else {
            const query = val + " Kabupaten Bandung Barat";
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`);
                const data = await response.json();

                if (data.length > 0) {
                    const latlng = L.latLng(parseFloat(data[0].lat), parseFloat(data[0].lon));
                    map.flyTo(latlng, 17);
                    setLocation(latlng, true);
                    alamatInput.value = data[0].display_name;
                } else {
                    alert("lokasi '" + val + "' tidak ditemukan. coba tambahkan detail (cth: desa, kec).");
                }
            } catch (error) {
                alert("gagal menghubungi server peta.");
            }
        }
    };

    const fotoInput = document.getElementById('foto_input');
    if (fotoInput) {
        fotoInput.onchange = function() {
            const [file] = this.files;
            if (file) {
                const previewContainer = document.getElementById('photoPreviewContainer');
                const previewImg = document.getElementById('preview');
                if (previewContainer && previewImg) {
                    previewContainer.classList.remove('hidden');
                    previewImg.src = URL.createObjectURL(file);
                }
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        initIconCategoryDropdown();
    });
</script>

<style>
    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
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
        box-shadow: 0 10px 15px rgba(0,0,0,0.4);
    }
    .leaflet-container { background: #f1f5f9 !important; border-radius: 2rem; }
    input::placeholder { color: #94a3b8 !important; font-size: 11px; }
    html { scroll-behavior: smooth; }
</style>
@endsection