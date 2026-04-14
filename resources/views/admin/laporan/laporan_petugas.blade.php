@extends('layouts.app')

@section('content')
{{-- Assets --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
    .content-wrapper-petugas {
        min-height: 100vh;
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
        padding-bottom: 50px;
    }

    #miniMap { 
        height: 450px; 
        width: 100%; 
        border-radius: 1.5rem; 
        z-index: 10;
        border: 4px solid white;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    /* Floating My Location Button */
    .locate-button {
        position: absolute;
        top: 80px;
        right: 10px;
        z-index: 1000;
        background: white;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        cursor: pointer;
        border: none;
        transition: all 0.2s;
    }
    .locate-button:hover { background: #f4f4f4; transform: scale(1.05); }
    .locate-button:active { transform: scale(0.95); }

    input:focus, select:focus, textarea:focus {
        border-color: #0B2A4A !important;
        box-shadow: 0 0 0 4px rgba(11, 42, 74, 0.05) !important;
    }

    @media (min-width: 1024px) {
        .sticky-side {
            position: sticky;
            top: 1rem;
        }
    }
</style>

<div class="content-wrapper-petugas" x-data="{ 
    alamat: 'Mencari lokasi GPS...',
    isSubmitting: false,
    init() {
        window.alpineData = this;
    }
}">
    
    {{-- HEADER --}}
    <div class="bg-[#0B2A4A] pt-12 pb-32 px-4 text-center relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <svg width="100%" height="100%"><pattern id="grid" width="30" height="30" patternUnits="userSpaceOnUse"><path d="M 30 0 L 0 0 0 30" fill="none" stroke="white" stroke-width="0.5"/></pattern><rect width="100%" height="100%" fill="url(#grid)" /></svg>
        </div>
        <div class="relative z-10">
            <span class="bg-[#F4B400] text-[#0B2A4A] text-[10px] font-black px-4 py-1.5 rounded-full uppercase tracking-tighter">Internal Staff Only</span>
            <h1 class="text-white text-3xl md:text-4xl font-extrabold mt-3 tracking-tight">INPUT LAPORAN <span class="text-[#F4B400]">TEKNIS</span></h1>
        </div>
    </div>

    {{-- FORM AREA --}}
    <div class="max-w-6xl mx-auto -mt-20 px-4 relative z-20">
        <form action="{{ route('laporan.petugas.store') }}" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                {{-- SISI KIRI: GABUNGAN IDENTITAS & DETAIL --}}
                <div class="lg:col-span-7 space-y-5">
                    <div class="bg-white rounded-[2rem] shadow-xl p-8 border border-slate-100 transition-all hover:shadow-2xl">
                        
                        {{-- SECTION 1: DATA PETUGAS --}}
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-[#0B2A4A] text-[#F4B400] rounded-xl flex items-center justify-center font-bold text-sm">01</div>
                            <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Identitas & Temuan</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Nama Petugas</label>
                                <input type="text" name="nama_pelapor" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold outline-none focus:bg-white" placeholder="Nama Lengkap" required>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">NIP</label>
                                <input type="text" name="nip" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold outline-none focus:bg-white" placeholder="Masukkan NIP" required>
                            </div>
                        </div>

                        <div class="mb-8">
                            <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">WhatsApp Aktif</label>
                            <input type="tel" name="kontak_pelapor" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold outline-none focus:bg-white" placeholder="628..." required>
                        </div>

                        <div class="border-t border-slate-100 pt-8 space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Judul Laporan / Nama Aset</label>
                                <input type="text" name="judul_laporan" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold outline-none focus:bg-white" placeholder="Contoh: Perbaikan PJU Ruas Ciburuy" required>
                            </div>
                            
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Status Kondisi</label>
                                <select name="kondisi_aset" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm font-bold outline-none focus:bg-white appearance-none" required>
                                    <option value="Baik">✅ Baik / Normal</option>
                                    <option value="Proses Perbaikan">⚙️ Proses Perbaikan</option>
                                    <option value="Rusak">🚨 Rusak</option>
                                    <option value="Kritis">⚠️ Kritis</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Catatan Kronologi</label>
                                <textarea name="deskripsi" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold outline-none focus:bg-white" placeholder="Tambahkan catatan teknis..."></textarea>
                            </div>
                            
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase ml-1">Foto Dokumentasi</label>
                                <div class="relative mt-2 border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:bg-slate-50 transition-all cursor-pointer group">
                                    <span class="text-3xl">📸</span>
                                    <p class="text-[10px] font-bold text-slate-500 mt-2 uppercase">Ambil Foto Lewat Kamera</p>
                                    <input type="file" name="foto" accept="image/*" capture="camera" class="absolute inset-0 opacity-0 cursor-pointer" required onchange="previewImage(event)">
                                </div>
                                <div id="image_preview_container" class="mt-4 hidden text-center">
                                    <img id="image_preview" src="#" class="inline-block max-h-64 rounded-2xl border-4 border-white shadow-lg">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SISI KANAN: PETA --}}
                <div class="lg:col-span-5">
                    <div class="sticky-side space-y-5">
                        <div class="bg-white rounded-[2rem] shadow-xl p-6 border border-slate-100">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 bg-[#0B2A4A] text-[#F4B400] rounded-xl flex items-center justify-center font-bold text-sm">02</div>
                                <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Lokasi Kejadian</h3>
                            </div>

                            <div class="relative">
                                <div id="miniMap"></div>
                                {{-- Tombol GPS ala Gojek --}}
                                <button type="button" onclick="getCurrentLocation()" class="locate-button" title="Cari Lokasi Saya">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#0B2A4A]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                </button>
                            </div>
                            
                            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 my-4">
                                <label class="text-[9px] font-black text-slate-400 uppercase block mb-1">Alamat Terdeteksi:</label>
                                <p class="text-[11px] font-bold text-[#0B2A4A] italic" x-text="alamat"></p>
                            </div>

                            <input type="hidden" name="lat" id="inp_lat">
                            <input type="hidden" name="lng" id="inp_lng">

                            <button type="submit" x-bind:disabled="isSubmitting" 
                                class="w-full bg-[#0B2A4A] text-[#F4B400] py-5 rounded-[1.5rem] text-sm font-black uppercase shadow-xl hover:bg-[#0f365e] transition-all">
                                <span x-show="!isSubmitting">Kirim Laporan Resmi</span>
                                <span x-show="isSubmitting">Sedang Mengirim...</span>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
    let map, marker;
    
    document.addEventListener("DOMContentLoaded", function () {
        // Init Map ke Default (Padalarang)
        map = L.map('miniMap', { scrollWheelZoom: false }).setView([-6.8431, 107.4912], 13);

        L.tileLayer("https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}", { 
            maxZoom: 20,
            attribution: '© Google Maps'
        }).addTo(map);

        const icon = L.divIcon({
            className: 'custom-marker',
            html: `<div style="background:#0B2A4A; width:22px; height:22px; border-radius:50%; border:3px solid #F4B400; box-shadow:0 0 10px rgba(0,0,0,0.2);"></div>`,
            iconSize: [22, 22]
        });

        marker = L.marker([-6.8431, 107.4912], {draggable: true, icon: icon}).addTo(map);

        map.on('click', (e) => updatePosition(e.latlng.lat, e.latlng.lng));
        marker.on('dragend', () => updatePosition(marker.getLatLng().lat, marker.getLatLng().lng));
        
        // Langsung cari lokasi saat buka
        getCurrentLocation();
    });

    function getCurrentLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                updatePosition(lat, lng);
                map.setView([lat, lng], 17);
            }, (err) => {
                alert("Gagal mengambil lokasi. Pastikan GPS aktif.");
            });
        }
    }

    async function updatePosition(lat, lng) {
        marker.setLatLng([lat, lng]);
        document.getElementById('inp_lat').value = lat;
        document.getElementById('inp_lng').value = lng;
        
        if(window.alpineData) window.alpineData.alamat = "Mencari alamat...";

        try {
            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const data = await res.json();
            if(window.alpineData) window.alpineData.alamat = data.display_name;
        } catch (e) { 
            if(window.alpineData) window.alpineData.alamat = lat + ', ' + lng;
        }
    }

    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = () => {
            const container = document.getElementById('image_preview_container');
            document.getElementById('image_preview').src = reader.result;
            container.classList.remove('hidden');
        }
        if(event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection