@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
{{-- 1. CSS Leaflet Base --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
{{-- 2. CSS Geocoder --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />

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
    /* Styling khusus tombol geocoder agar senada dengan UI */
    .leaflet-control-geocoder {
        border-radius: 12px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        border: none !important;
    }
</style>

<div class="main-container-scroll bg-slate-50 sm:ml-64 p-8 inter-font-scope custom-scroll">
    <div class="max-w-[1400px] mx-auto">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase">Edit Data Aset</h1>
                <p class="text-slate-500 font-bold text-xs mt-1 tracking-widest uppercase opacity-70">
                    ID Perangkat: <span class="text-indigo-600">{{ $asset->id_asset }}</span> — Manajemen GIS Dishub KBB
                </p>
            </div>
            <a href="{{ route('assets.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-[10px] uppercase tracking-widest hover:bg-slate-50 transition-all shadow-sm">
                ← Kembali
            </a>
        </div>

        @if ($errors->any())
        <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-2xl shadow-sm">
            <p class="font-black text-[10px] uppercase mb-2 tracking-widest">Terjadi Kesalahan Input:</p>
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
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Nama Perangkat</label>
                            <input type="text" name="nama" value="{{ old('nama', $asset->nama) }}" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none focus:border-indigo-500 transition-all font-semibold text-slate-700">
                        </div>

                        {{-- Alamat (Auto-detect) --}}
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Alamat Terdeteksi</label>
                            <textarea name="alamat" id="alamatInput" rows="2" readonly class="w-full px-5 py-3.5 bg-slate-100 border border-slate-200 rounded-xl outline-none font-semibold text-slate-600 text-sm leading-relaxed tracking-tight">{{ old('alamat', $asset->alamat) }}</textarea>
                        </div>

                        {{-- Kategori Utama --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Kategori Utama</label>
                            <select name="kategori" id="kat_select" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none font-semibold text-slate-700 appearance-none cursor-pointer focus:border-indigo-500">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Penerangan Jalan Umum (PJU)" {{ old('kategori', $asset->kategori) == 'Penerangan Jalan Umum (PJU)' ? 'selected' : '' }}>💡 Penerangan Jalan Umum (PJU)</option>
                                <option value="Perlengkapan Jalan" {{ old('kategori', $asset->kategori) == 'Perlengkapan Jalan' ? 'selected' : '' }}>🛑 Perlengkapan Jalan</option>
                                <option value="Fasilitas Lalu Lintas" {{ old('kategori', $asset->kategori) == 'Fasilitas Lalu Lintas' ? 'selected' : '' }}>🛣️ Fasilitas Lalu Lintas</option>
                                <option value="Pengendalian dan Pengawasan" {{ old('kategori', $asset->kategori) == 'Pengendalian dan Pengawasan' ? 'selected' : '' }}>📹 Pengendalian dan Pengawasan</option>
                                <option value="Prasarana Transportasi" {{ old('kategori', $asset->kategori) == 'Prasarana Transportasi' ? 'selected' : '' }}>🏢 Prasarana Transportasi</option>
                            </select>
                        </div>

                        {{-- Jenis Spesifik --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Jenis Aset</label>
                            <select name="jenis" id="jenis_select" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none font-semibold text-slate-700 appearance-none focus:border-indigo-500 transition-all">
                                <option value="{{ $asset->jenis }}">{{ strtoupper($asset->jenis) }}</option>
                            </select>
                        </div>

                        {{-- Picker Icon Marker --}}
                        <div class="col-span-2 py-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-3 ml-1 text-center text-indigo-600">Visual Marker Di Peta</label>
                            <div class="flex justify-center flex-wrap gap-4 p-5 bg-slate-50 rounded-2xl border border-slate-100 shadow-inner" id="iconPicker">
                                @php
                                    $emojis = [
                                        '💡' => 'Lampu PJU', '🚦' => 'Traffic Light', '🛑' => 'Rambu Jalan',
                                        '🚧' => 'Pekerjaan Jalan', '📹' => 'CCTV / ATCS', '☀️' => 'Solar Cell',
                                        '🅿️' => 'Area Parkir', '🚏' => 'Halte / Bus', '🏢' => 'Gedung / Terminal',
                                        '🛡️' => 'Peringatan', '⚡' => 'Panel / Kabel', '📡' => 'Sensor / Radio', 
                                        '📺' => 'VMS / Papan', '🔋' => 'Baterai', '🌉' => 'Jembatan/JPO', 
                                        '🏁' => 'Zebra Cross', '🗺️' => 'Rambu Petunjuk', '🔵' => 'Rambu Perintah'
                                    ];
                                @endphp

                                @foreach($emojis as $emoji => $label)
                                <label class="cursor-pointer group relative flex flex-col items-center">
                                    <input type="radio" name="icon_marker" value="{{ $emoji }}" class="hidden peer" {{ old('icon_marker', $asset->icon_marker) == $emoji ? 'checked' : '' }} required>
                                    <div class="w-12 h-12 flex items-center justify-center rounded-xl border-2 border-transparent bg-white shadow-sm peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:scale-110 text-2xl transition-all hover:shadow-md hover:bg-slate-50" title="{{ $label }}">
                                        {{ $emoji }}
                                    </div>
                                    <span class="absolute -bottom-6 scale-75 opacity-0 group-hover:opacity-100 peer-checked:opacity-100 peer-checked:scale-100 transition-all text-[9px] font-black text-indigo-600 whitespace-nowrap bg-white px-2 py-0.5 rounded-md shadow-sm border border-slate-100 z-10 pointer-events-none">
                                        {{ $label }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Status Kondisi --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Kondisi Saat Ini</label>
                            <select name="status" required class="w-full px-5 py-3.5 bg-slate-50 border border-slate-200 rounded-xl outline-none font-semibold text-slate-700 focus:border-indigo-500">
                                <option value="Baik" {{ old('status', $asset->status) == 'Baik' ? 'selected' : '' }}>🟢 BAIK</option>
                                <option value="Proses Perbaikan" {{ old('status', $asset->status) == 'Proses Perbaikan' ? 'selected' : '' }}>🟡 PROSES PERBAIKAN</option>
                                <option value="Rusak" {{ old('status', $asset->status) == 'Rusak' ? 'selected' : '' }}>🟠 RUSAK</option>
                                <option value="Kritis" {{ old('status', $asset->status) == 'Kritis' ? 'selected' : '' }}>🔴 KRITIS</option>
                            </select>
                        </div>

                        {{-- Upload Foto --}}
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Ganti Foto Fisik</label>
                            <input type="file" name="foto" id="foto_input" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-3 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-indigo-600 file:text-white cursor-pointer bg-slate-50 border border-slate-200 rounded-xl transition-all">
                        </div>
                    </div>
                </div>

                {{-- Preview Foto --}}
                <div class="bg-white p-4 rounded-[24px] border border-slate-200 shadow-sm transition-all">
                    <p class="text-[9px] font-bold text-slate-400 uppercase mb-2 ml-1">Preview Foto:</p>
                    <img id="preview" src="{{ $asset->foto ? asset('storage/' . $asset->foto) : 'https://placehold.co/800x450/f8fafc/64748b?text=Tanpa+Foto' }}" class="w-full h-64 object-cover rounded-xl shadow-inner border border-slate-100">
                </div>
            </div>

            {{-- Kolom Kanan: Map --}}
            <div class="col-span-12 lg:col-span-6 flex flex-col gap-6">
                
                {{-- Panel Pencarian Pintar --}}
                <div class="bg-white p-4 rounded-3xl border border-slate-200 shadow-sm flex gap-3">
                    <input type="text" id="searchCoords" placeholder="Cari Terminal, Desa, Jalan, atau Koordinat..." 
                           class="flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-700 outline-none focus:border-indigo-500">
                    <button type="button" onclick="searchByCoords()" 
                            class="bg-slate-900 text-white px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition-all">
                        Cari
                    </button>
                </div>

                <div class="bg-white p-3 rounded-[32px] shadow-sm border border-slate-200 h-[580px]">
                    <div id="mapPicker" class="w-full h-full rounded-[24px] z-0"></div>
                </div>

                {{-- Koordinat --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="block text-[9px] font-black text-slate-400 uppercase mb-1 text-center">Latitude</span>
                        <input type="text" name="lat" id="latInput" value="{{ old('lat', $asset->lat) }}" readonly required placeholder="0.000000" class="w-full text-center font-bold text-slate-800 bg-transparent outline-none">
                    </div>
                    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                        <span class="block text-[9px] font-black text-slate-400 uppercase mb-1 text-center">Longitude</span>
                        <input type="text" name="lng" id="lngInput" value="{{ old('lng', $asset->lng) }}" readonly required placeholder="0.000000" class="w-full text-center font-bold text-slate-800 bg-transparent outline-none">
                    </div>
                </div>

                <button type="submit" class="w-full py-5 bg-indigo-600 text-white rounded-[20px] font-bold text-xs uppercase tracking-widest shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-95">
                    Simpan Perubahan Aset 💾
                </button>
            </div>
        </form>
    </div>
</div>

{{-- SCRIPT SECTION --}}
{{-- 1. JS Leaflet Base --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
{{-- 2. JS Leaflet Geocoder (WAJIB sebelum map-picker.js) --}}
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
{{-- 3. File Logika Peta --}}
<script src="{{ asset('js/map-picker.js') }}"></script>
{{-- 4. Poligon Bandung Barat --}}
<script src="{{ asset('js/polygon-layers.js') }}"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // DATA MASTER
        const dbJenis = {
            'Penerangan Jalan Umum (PJU)': ['Tiang PJU Galvanis', 'Tiang PJU Dekoratif', 'Lampu LED', 'Lampu Solar Cell', 'Panel Box PJU', 'Kabel Udara PJU', 'Baterai Solar Cell', 'Modul Solar Cell'],
            'Perlengkapan Jalan': ['Rambu Larangan', 'Rambu Peringatan', 'Rambu Perintah', 'Rambu Petunjuk', 'RPPJ (Papan Jurusan)', 'Cermin Tikungan', 'Guardrail', 'Delineator', 'Patok Kilometer'],
            'Fasilitas Lalu Lintas': ['APILL (Traffic Light)', 'Warning Light', 'Marka Zebra Cross', 'Marka RHK', 'Marka Parkir', 'Paku Jalan', 'Water Barrier', 'Traffic Cone', 'Road Barrier Beton'],
            'Pengendalian dan Pengawasan': ['CCTV Surveilans', 'CCTV E-TLE', 'VMS (Papan Digital)', 'ATCS Controller', 'Speed Gun', 'Voice Announcer', 'Radio Komunikasi'],
            'Prasarana Transportasi': ['Halte Bus', 'Terminal', 'Gedung PKB', 'Jembatan Penyeberangan (JPO)', 'Parkir Off-Street', 'Pool Bus']
        };

        const katSelect = document.getElementById('kat_select');
        const jenisSelect = document.getElementById('jenis_select');
        const iconRadios = document.getElementsByName('icon_marker');

        // Logic Dropdown
        katSelect.addEventListener('change', function() {
            const selectedKat = this.value;
            jenisSelect.innerHTML = '<option value="">-- Pilih Jenis --</option>';
            if(selectedKat && dbJenis[selectedKat]) {
                dbJenis[selectedKat].forEach(jenis => {
                    const opt = document.createElement('option');
                    opt.value = jenis;
                    opt.text = jenis.toUpperCase();
                    jenisSelect.add(opt);
                });
            }
            autoSuggestIcon();
        });

        jenisSelect.addEventListener('change', autoSuggestIcon);

        function autoSuggestIcon() {
            const kat = katSelect.value;
            const jen = jenisSelect.value.toLowerCase();
            let target = '';
            if(kat === 'Penerangan Jalan Umum (PJU)') target = '💡';
            else if(kat === 'Perlengkapan Jalan') target = '🛑';
            else if(kat === 'Fasilitas Lalu Lintas') target = '🚦';
            else if(kat === 'Pengendalian dan Pengawasan') target = '📹';
            else if(kat === 'Prasarana Transportasi') target = '🏢';

            if(jen.includes('solar')) target = '☀️';
            else if(jen.includes('panel') || jen.includes('box') || jen.includes('kabel') || jen.includes('baterai')) target = '⚡';
            else if(jen.includes('vms') || jen.includes('papan digital')) target = '📺';
            else if(jen.includes('petunjuk') || jen.includes('rppj')) target = '🗺️';
            else if(jen.includes('perintah')) target = '🔵';
            else if(jen.includes('peringatan')) target = '🛡️';
            else if(jen.includes('parkir')) target = '🅿️';
            else if(jen.includes('halte') || jen.includes('terminal')) target = '🚏';
            else if(jen.includes('jpo') || jen.includes('jembatan')) target = '🌉';
            else if(jen.includes('marka') || jen.includes('paku') || jen.includes('barrier') || jen.includes('delineator')) target = '🚧';
            else if(jen.includes('zebra')) target = '🏁';

            if(target) {
                iconRadios.forEach(r => { if(r.value === target) r.checked = true; });
            }
        }

        // --- INISIALISASI PETA ---
        window.onload = function() {
            if (typeof initMapPicker === 'function') {
                // Konversi lat/lng ke float untuk keamanan koordinat peta
                const initialLat = parseFloat("{{ old('lat', $asset->lat) }}");
                const initialLng = parseFloat("{{ old('lng', $asset->lng) }}");
                
                initMapPicker(
                    'mapPicker', 
                    'latInput', 
                    'lngInput', 
                    'alamatInput', 
                    [initialLat, initialLng]
                );

                // Integrasi Poligon: Panggil fungsi dari polygon-layers.js
                if (typeof initPolygonLayers === 'function') {
                    initPolygonLayers(window.map);
                }
            }
        };

        // Image Preview
        document.getElementById('foto_input').onchange = function() {
            const [file] = this.files;
            if (file) document.getElementById('preview').src = URL.createObjectURL(file);
        };
        
        setTimeout(() => { if(window.map) window.map.invalidateSize(); }, 500);
    });
</script>
@endsection