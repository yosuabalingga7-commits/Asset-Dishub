@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    /* Force font Inter ke seluruh elemen di halaman ini */
    .inter-font-scope, 
    .inter-font-scope input, 
    .inter-font-scope select, 
    .inter-font-scope textarea, 
    .inter-font-scope button {
        font-family: 'Inter', sans-serif !important;
    }
    .custom-scroll::-webkit-scrollbar { width: 6px; }
    .custom-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* Penyesuaian agar container utama bisa di-scroll dengan pointer */
    .main-container-scroll {
        height: 100vh;
        overflow-y: auto;
        scrollbar-gutter: stable;
    }
    
    /* HILANGKAN SIDEBAR KOSONG - MODIFIKASI PENTING */
    .main-container-scroll {
        margin-left: 0 !important;
        width: 100% !important;
    }
    
    /* Pastikan tidak ada padding tambahan dari sidebar */
    body > div:first-child {
        margin-left: 0 !important;
    }
    
    /* Styling khusus tombol geocoder agar senada dengan UI */
    .leaflet-control-geocoder {
        border-radius: 12px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        border: none !important;
    }
    
    /* Styling untuk icon picker */
    .icon-option {
        transition: all 0.2s ease;
    }
    .icon-option:hover {
        transform: scale(1.05);
    }
    
    /* Nama icon tampil langsung di bawah */
    .icon-label {
        font-size: 9px;
        font-weight: 600;
        color: #475569;
        text-align: center;
        margin-top: 4px;
        letter-spacing: 0.3px;
        text-transform: lowercase;
    }
    .peer-checked ~ .icon-label {
        color: #6366f1;
        font-weight: 800;
    }
</style>

<div class="main-container-scroll bg-slate-50 p-8 inter-font-scope custom-scroll" style="margin-left: 0; width: 100%;">
    <div class="max-w-[1400px] mx-auto">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight">Edit Data Aset</h1>
                <p class="text-slate-500 font-bold text-xs mt-1 tracking-widest opacity-70">
                    ID Perangkat: <span class="text-indigo-600">{{ $asset->id_asset }}</span> — Manajemen GIS Dishub KBB
                </p>
            </div>
            <a href="{{ route('assets.list') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-[10px] tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                ← kembali
            </a>
        </div>

        @if ($errors->any())
        <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-2xl shadow-sm">
            <p class="font-black text-[10px] mb-2 tracking-widest">terjadi kesalahan input:</p>
            <ul class="list-disc ml-5 text-xs font-medium">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('assets.update', $asset->id) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-12 gap-8 mb-20">
            @csrf
            @method('PUT')

            {{-- Kolom Kiri: Form Detail --}}
            <div class="col-span-12 lg:col-span-6 space-y-6">
                <div class="bg-white p-8 rounded-[24px] shadow-sm border border-slate-200">
                    <div class="grid grid-cols-2 gap-6">
                        
                        {{-- Nama Aset --}}
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wider mb-2 ml-1">nama perangkat (identitas lengkap)</label>
                            <input type="text" name="nama" value="{{ old('nama', $asset->nama) }}" required placeholder="contoh: cctv simpang padalarang 01" class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-indigo-500 transition-all font-semibold text-slate-700">
                        </div>

                        {{-- Alamat (Auto-detect) --}}
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wider mb-2 ml-1">alamat terdeteksi (klik di peta)</label>
                            <textarea name="alamat" id="alamatInput" rows="2" readonly class="w-full px-5 py-3.5 bg-slate-100 border border-slate-200 rounded-xl outline-none font-semibold text-slate-600 text-sm leading-relaxed tracking-tight">{{ old('alamat', $asset->alamat) }}</textarea>
                        </div>

                        {{-- Kategori Utama --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wider mb-2 ml-1">kategori utama</label>
                            <select name="kategori" id="kat_select" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none font-semibold text-slate-700 appearance-none cursor-pointer focus:border-indigo-500">
                                <option value="">-- pilih kategori --</option>
                                <option value="Penerangan Jalan Umum (PJU)" {{ old('kategori', $asset->kategori) == 'Penerangan Jalan Umum (PJU)' ? 'selected' : '' }}>penerangan jalan umum (pju)</option>
                                <option value="Perlengkapan Jalan" {{ old('kategori', $asset->kategori) == 'Perlengkapan Jalan' ? 'selected' : '' }}>perlengkapan jalan</option>
                                <option value="Fasilitas Lalu Lintas" {{ old('kategori', $asset->kategori) == 'Fasilitas Lalu Lintas' ? 'selected' : '' }}>fasilitas lalu lintas</option>
                                <option value="Pengendalian dan Pengawasan" {{ old('kategori', $asset->kategori) == 'Pengendalian dan Pengawasan' ? 'selected' : '' }}>pengendalian dan pengawasan</option>
                                <option value="Prasarana Transportasi" {{ old('kategori', $asset->kategori) == 'Prasarana Transportasi' ? 'selected' : '' }}>prasarana transportasi</option>
                            </select>
                        </div>

                        {{-- Jenis Spesifik --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wider mb-2 ml-1">jenis aset</label>
                            <select name="jenis" id="jenis_select" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none font-semibold text-slate-700 appearance-none focus:border-indigo-500 transition-all">
                                <option value="{{ old('jenis', $asset->jenis) }}">{{ strtoupper(old('jenis', $asset->jenis)) }}</option>
                            </select>
                        </div>

                        {{-- Picker Icon Marker (Menggunakan Font Awesome Icons) --}}
                        <div class="col-span-2 py-2">
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wider mb-3 ml-1 text-center text-indigo-600">visual marker di peta (terpilih otomatis & bisa diganti)</label>
                            <div class="flex flex-col gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner" id="iconPicker">
                                @php
                                    // ICON NYATA UNTUK SETIAP KATEGORI DAN JENIS ASET DISHUB (VERSI GROUPED)
                                    $icons = [
                                        // ========== PENERANGAN JALAN UMUM (PJU) ==========
                                        'PENERANGAN JALAN UMUM (PJU)' => [
                                            'fa-lightbulb' => 'lampu pju',
                                            'fa-solar-panel' => 'solar cell',
                                            'fa-charging-station' => 'panel box pju',
                                            'fa-bolt' => 'kabel udara pju',
                                            'fa-battery-full' => 'baterai solar cell',
                                        ],
                                        
                                        // ========== PERLENGKAPAN JALAN ==========
                                        'PERLENGKAPAN JALAN' => [
                                            'fa-triangle-exclamation' => 'rambu peringatan',
                                            'fa-circle-exclamation' => 'rambu larangan',
                                            'fa-circle-info' => 'rambu petunjuk',
                                            'fa-hand-peace' => 'rambu perintah',
                                            'fa-map' => 'rppj (papan jurusan)',
                                            'fa-arrow-rotate-left' => 'cermin tikungan',
                                            'fa-shield-halved' => 'guardrail',
                                            'fa-road-circle-exclamation' => 'delineator',
                                            'fa-location-dot' => 'patok kilometer',
                                        ],
                                        
                                        // ========== FASILITAS LALU LINTAS ==========
                                        'FASILITAS LALU LINTAS' => [
                                            'fa-traffic-light' => 'apill (traffic light)',
                                            'fa-exclamation-triangle' => 'warning light',
                                            'fa-person-walking-arrow-right' => 'marka zebra cross',
                                            'fa-road' => 'marka rhk',
                                            'fa-square-parking' => 'marka parkir',
                                            'fa-circle' => 'paku jalan',
                                            'fa-water' => 'water barrier',
                                            'fa-triangle-exclamation' => 'traffic cone',
                                            'fa-shield-virus' => 'road barrier beton',
                                        ],
                                        
                                        // ========== PENGENDALIAN & PENGAWASAN ==========
                                        'PENGENDALIAN & PENGAWASAN' => [
                                            'fa-video' => 'cctv surveilans',
                                            'fa-camera' => 'cctv e-tle',
                                            'fa-display' => 'vms (papan digital)',
                                            'fa-microchip' => 'atcs controller',
                                            'fa-gun' => 'speed gun',
                                            'fa-bullhorn' => 'voice announcer',
                                            'fa-tower-broadcast' => 'radio komunikasi',
                                        ],
                                        
                                        // ========== PRASARANA TRANSPORTASI ==========
                                        'PRASARANA TRANSPORTASI' => [
                                            'fa-bus' => 'halte bus',
                                            'fa-building' => 'terminal',
                                            'fa-warehouse' => 'gedung pkb',
                                            'fa-person-walking' => 'jembatan penyeberangan (jpo)',
                                            'fa-square-parking' => 'parkir off-street',
                                            'fa-truck-front' => 'pool bus',
                                        ],
                                    ];

                                    // ICON UNTUK TAMPILAN KATEGORI DI DROPDOWN (Font Awesome)
                                    $categoryIcons = [
                                        'PENERANGAN JALAN UMUM (PJU)' => 'fa-lightbulb',
                                        'PERLENGKAPAN JALAN' => 'fa-triangle-exclamation',
                                        'FASILITAS LALU LINTAS' => 'fa-traffic-light',
                                        'PENGENDALIAN & PENGAWASAN' => 'fa-video',
                                        'PRASARANA TRANSPORTASI' => 'fa-bus',
                                    ];
                                @endphp

                                @foreach($icons as $kategori => $items)
                                <div class="icon-category-group border border-slate-200 rounded-xl bg-white overflow-hidden">
                                    <button type="button" class="category-dropdown-btn w-full flex justify-between items-center p-3 bg-gradient-to-r from-slate-50 to-white hover:from-slate-100 transition-all" data-category="{{ Str::slug($kategori) }}">
                                        <div class="flex items-center gap-3">
                                            <i class="fas {{ $categoryIcons[$kategori] ?? 'fa-folder' }} text-indigo-500 text-lg w-5"></i>
                                            <span class="font-black text-xs uppercase text-slate-700">{{ $kategori }}</span>
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
                                                <input type="radio" name="icon_marker" value="{{ $icon }}" class="hidden peer" {{ old('icon_marker', $asset->icon_marker) == $icon ? 'checked' : '' }}>
                                                <div class="w-10 h-10 flex items-center justify-center rounded-lg border-2 border-slate-200 bg-white shadow-sm peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:scale-105 transition-all">
                                                    <i class="fas {{ $icon }} text-slate-700 peer-checked:text-indigo-600 text-lg"></i>
                                                </div>
                                                <span class="icon-label mt-1 text-[8px] font-bold text-slate-500 uppercase text-center">{{ $label }}</span>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                    {{-- Status Kondisi --}}
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Kondisi Saat Ini</label>
                    <select name="status" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none font-semibold text-slate-700 focus:border-indigo-500">
                        <option value="Baik" {{ old('status', $asset->status) == 'Baik' ? 'selected' : '' }}>🟢 Baik</option>
                        <option value="Rusak" {{ old('status', $asset->status) == 'Rusak' ? 'selected' : '' }}>🟡 Rusak</option>
                        <option value="Kritis" {{ old('status', $asset->status) == 'Kritis' ? 'selected' : '' }}>🔴 Kritis</option>
                        <option value="Proses Perbaikan" {{ old('status', $asset->status) == 'Proses Perbaikan' ? 'selected' : '' }}>🔵 Proses Perbaikan</option>
                    </select>
                </div>

                        {{-- Upload Foto --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 tracking-wider mb-2 ml-1">ganti foto fisik</label>
                            <input type="file" name="foto" id="foto_input" accept="image/jpeg,image/png,image/jpg,image/webp" class="w-full text-xs text-slate-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer bg-slate-50 border border-slate-200 rounded-xl transition-all">
                        </div>
                    </div>
                </div>

                {{-- Preview Foto --}}
                <div class="bg-white p-4 rounded-[24px] border border-slate-200 shadow-sm transition-all">
                    <p class="text-[9px] font-bold text-slate-400 mb-2 ml-1">preview foto:</p>
                    <img id="preview" src="{{ $asset->foto ? asset('storage/' . $asset->foto) : 'https://placehold.co/800x450/f8fafc/64748b?text=Tanpa+Foto' }}" class="w-full h-64 object-cover rounded-xl shadow-inner border border-slate-100">
                </div>
            </div>

            {{-- Kolom Kanan: Map --}}
            <div class="col-span-12 lg:col-span-6 flex flex-col gap-6">
                
                {{-- Panel Pencarian Pintar --}}
                <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm flex gap-3">
                    <input type="text" id="searchCoords" placeholder="cari terminal, desa, jalan, atau koordinat..." 
                           class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-700 outline-none focus:border-indigo-500">
                    <button type="button" onclick="searchByCoords()" 
                            class="bg-slate-900 text-white px-6 py-2 rounded-xl text-[10px] font-black tracking-widest hover:bg-slate-800 transition-all">
                        cari
                    </button>
                </div>

                <div class="bg-white p-3 rounded-[32px] shadow-sm border border-slate-200 h-[580px]">
                    <div id="mapPicker" class="w-full h-full rounded-[24px] z-0"></div>
                </div>

                {{-- Koordinat --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="block text-[9px] font-black text-slate-400 mb-1 text-center">latitude</span>
                        <input type="text" name="lat" id="latInput" value="{{ old('lat', $asset->lat) }}" readonly required placeholder="0.000000" class="w-full text-center font-bold text-slate-800 bg-transparent outline-none">
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="block text-[9px] font-black text-slate-400 mb-1 text-center">longitude</span>
                        <input type="text" name="lng" id="lngInput" value="{{ old('lng', $asset->lng) }}" readonly required placeholder="0.000000" class="w-full text-center font-bold text-slate-800 bg-transparent outline-none">
                    </div>
                </div>

                <button type="submit" class="w-full py-5 bg-indigo-600 text-white rounded-[20px] font-bold text-xs tracking-widest shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-95">
                    Simpan Perubahan Aset
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script src="{{ asset('js/polygon-layers.js') }}"></script>

<script>
    // DATA MASTER LENGKAP
    const dbJenis = {
        'Penerangan Jalan Umum (PJU)': [
            'Tiang PJU Galvanis', 'Tiang PJU Dekoratif', 'Lampu LED', 'Lampu Solar Cell', 'Panel Box PJU', 'Kabel Udara PJU', 'Baterai Solar Cell', 'Modul Solar Cell'
        ],
        'Perlengkapan Jalan': [
            'Rambu Larangan', 'Rambu Peringatan', 'Rambu Perintah', 'Rambu Petunjuk', 'RPPJ (Papan Jurusan)', 'Cermin Tikungan', 'Guardrail', 'Delineator', 'Patok Kilometer'
        ],
        'Fasilitas Lalu Lintas': [
            'APILL (Traffic Light)', 'Warning Light', 'Marka Zebra Cross', 'Marka RHK', 'Marka Parkir', 'Paku Jalan', 'Water Barrier', 'Traffic Cone', 'Road Barrier Beton'
        ],
        'Pengendalian dan Pengawasan': [
            'CCTV Surveilans', 'CCTV E-TLE', 'VMS (Papan Digital)', 'ATCS Controller', 'Speed Gun', 'Voice Announcer', 'Radio Komunikasi'
        ],
        'Prasarana Transportasi': [
            'Halte Bus', 'Terminal', 'Gedung PKB', 'Jembatan Penyeberangan (JPO)', 'Parkir Off-Street', 'Pool Bus'
        ]
    };

    // MAPPING ICON FONT AWESOME UNTUK AUTO SUGGEST
    const iconMapping = {
        'Penerangan Jalan Umum (PJU)': 'fa-lightbulb',
        'Perlengkapan Jalan': 'fa-triangle-exclamation',
        'Fasilitas Lalu Lintas': 'fa-traffic-light',
        'Pengendalian dan Pengawasan': 'fa-video',
        'Prasarana Transportasi': 'fa-bus',
        
        'lampu led': 'fa-lightbulb',
        'lampu solar cell': 'fa-solar-panel',
        'panel box pju': 'fa-charging-station',
        'kabel udara pju': 'fa-bolt',
        'baterai solar cell': 'fa-battery-full',
        'modul solar cell': 'fa-solar-panel',
        'tiang pju galvanis': 'fa-lightbulb',
        'tiang pju dekoratif': 'fa-lightbulb',
        
        'rambu larangan': 'fa-circle-exclamation',
        'rambu peringatan': 'fa-triangle-exclamation',
        'rambu perintah': 'fa-hand-peace',
        'rambu petunjuk': 'fa-circle-info',
        'rppj': 'fa-map',
        'cermin tikungan': 'fa-arrow-rotate-left',
        'guardrail': 'fa-shield-halved',
        'delineator': 'fa-road-circle-exclamation',
        'patok kilometer': 'fa-location-dot',
        
        'apill': 'fa-traffic-light',
        'traffic light': 'fa-traffic-light',
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
        'vms': 'fa-display',
        'papan digital': 'fa-display',
        'atcs controller': 'fa-microchip',
        'speed gun': 'fa-gun',
        'voice announcer': 'fa-bullhorn',
        'radio komunikasi': 'fa-tower-broadcast',
        
        'halte bus': 'fa-bus',
        'terminal': 'fa-building',
        'gedung pkb': 'fa-warehouse',
        'jembatan penyeberangan': 'fa-person-walking',
        'jpo': 'fa-person-walking',
        'parkir off-street': 'fa-square-parking',
        'pool bus': 'fa-truck-front'
    };

    const katSelect = document.getElementById('kat_select');
    const jenisSelect = document.getElementById('jenis_select');
    const iconRadios = document.getElementsByName('icon_marker');
    const alamatInput = document.getElementById('alamatInput');

    // Simpan nilai jenis saat ini
    const currentJenis = "{{ old('jenis', $asset->jenis) }}";

    katSelect.addEventListener('change', function() {
        const selectedKat = this.value;
        jenisSelect.innerHTML = '<option value="">-- pilih jenis --</option>';
        if(selectedKat && dbJenis[selectedKat]) {
            jenisSelect.disabled = false;
            jenisSelect.classList.remove('cursor-not-allowed', 'opacity-50');
            dbJenis[selectedKat].forEach(jenis => {
                const opt = document.createElement('option');
                opt.value = jenis;
                opt.text = jenis.toLowerCase();
                if(opt.value === currentJenis) opt.selected = true;
                jenisSelect.add(opt);
            });
        } else {
            jenisSelect.disabled = true;
            jenisSelect.classList.add('cursor-not-allowed', 'opacity-50');
        }
        autoSuggestIcon();
    });

    // Trigger change untuk load data awal
    if(katSelect.value) {
        katSelect.dispatchEvent(new Event('change'));
    }

    jenisSelect.addEventListener('change', autoSuggestIcon);

    function autoSuggestIcon() {
        const kat = katSelect.value;
        const jen = jenisSelect.value.toLowerCase();
        let target = '';
        
        if(iconMapping[kat]) target = iconMapping[kat];
        
        for(const [key, icon] of Object.entries(iconMapping)) {
            if(jen.includes(key)) {
                target = icon;
                break;
            }
        }
        
        if(!target) {
            if(kat === 'Penerangan Jalan Umum (PJU)') target = 'fa-lightbulb';
            else if(kat === 'Perlengkapan Jalan') target = 'fa-triangle-exclamation';
            else if(kat === 'Fasilitas Lalu Lintas') target = 'fa-traffic-light';
            else if(kat === 'Pengendalian dan Pengawasan') target = 'fa-video';
            else if(kat === 'Prasarana Transportasi') target = 'fa-bus';
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
            
            if(content) {
                content.classList.toggle('hidden');
                if(!content.classList.contains('hidden')) {
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

    // Setup Map
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
        center: [{{ old('lat', $asset->lat) }}, {{ old('lng', $asset->lng) }}],
        zoom: 17,
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
        
        if(marker) {
            marker.setLatLng(latlng);
        } else {
            marker = L.marker(latlng, { draggable: true }).addTo(map);
            marker.on('dragend', function(event) {
                setLocation(event.target.getLatLng());
            });
        }
        
        if(!skipAlamat) {
            updateAlamat(latlng.lat, latlng.lng);
        }
    }

    map.on('click', function(e) {
        setLocation(e.latlng);
    });

    // Set marker awal
    const initialLat = parseFloat("{{ old('lat', $asset->lat) }}");
    const initialLng = parseFloat("{{ old('lng', $asset->lng) }}");
    if(initialLat && initialLng) {
        setLocation(L.latLng(initialLat, initialLng), true);
        map.setView([initialLat, initialLng], 17);
    }

    const geocoder = L.Control.geocoder({
        defaultMarkGeocode: false,
        placeholder: "cari terminal, desa, jalan...",
        collapsed: false,
        position: 'topleft'
    })
    .on('markgeocode', function(e) {
        const latlng = e.geocode.center;
        map.flyTo(latlng, 17);
        setLocation(latlng);
        alamatInput.value = e.geocode.name;
    })
    .addTo(map);

    window.searchByCoords = async function() {
        const val = document.getElementById('searchCoords').value.trim();
        if(!val) return alert("masukkan nama tempat atau koordinat!");

        const coordRegex = /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/;

        if(coordRegex.test(val)) {
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
                if(data.length > 0) {
                    const latlng = L.latLng(parseFloat(data[0].lat), parseFloat(data[0].lon));
                    map.flyTo(latlng, 17);
                    setLocation(latlng, true);
                    alamatInput.value = data[0].display_name;
                } else {
                    alert("lokasi '" + val + "' tidak ditemukan.");
                }
            } catch (error) {
                alert("gagal menghubungi server peta.");
            }
        }
    };

    const fotoInput = document.getElementById('foto_input');
    if(fotoInput) {
        fotoInput.onchange = function() {
            const [file] = this.files;
            if(file) {
                const previewImg = document.getElementById('preview');
                if(previewImg) previewImg.src = URL.createObjectURL(file);
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        initIconCategoryDropdown();
        setTimeout(() => { map.invalidateSize(); }, 500);
    });
</script>
@endsection