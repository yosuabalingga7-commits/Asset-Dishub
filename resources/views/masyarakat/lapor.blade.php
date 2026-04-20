@extends('layouts.public')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<div class="min-h-screen relative font-['Inter'] pb-20 text-white overflow-hidden bg-[#0B2A4A]" x-data="{ 
    alamat: 'Mendeteksi lokasi...',
    isSubmitting: false,
    init() {
        this.getLocation();
        window.alpineData = this;
    },
    getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition((pos) => {
                updatePosition(pos.coords.latitude, pos.coords.longitude);
                map.setView([pos.coords.latitude, pos.coords.longitude], 17);
            }, (error) => {
                console.error('Gagal ambil GPS:', error);
            }, { enableHighAccuracy: true });
        }
    }
}">
    
    {{-- LATAR BELAKANG BARU: MESH GRADIENT STATIC (Lebih Jelas & Mewah) --}}
    <div class="absolute inset-0 z-0">
        <div class="absolute inset-0 bg-[#0B2A4A]"></div>
        <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[600px] h-[600px] bg-indigo-900/40 rounded-full blur-[150px]"></div>
    </div>

    {{-- RUNNING TEXT (MARQUEE) --}}
    <div class="relative z-50 bg-[#F4B400] overflow-hidden py-2 border-b border-black/10 shadow-lg">
        <div class="whitespace-nowrap animate-marquee flex items-center gap-10">
            <span class="text-[#0B2A4A] text-[10px] font-black uppercase tracking-widest italic">Pusat Pengaduan Asset Dinas Perhubungan Kabupaten Bandung Barat — Laporkan Kerusakan Untuk Pelayanan Lebih Baik </span>
            <span class="text-[#0B2A4A] text-[10px] font-black uppercase tracking-widest italic">Pusat Pengaduan Asset Dinas Perhubungan Kabupaten Bandung Barat — Laporkan Kerusakan Untuk Pelayanan Lebih Baik </span>
        </div>
    </div>

    {{-- TOMBOL KEMBALI --}}
    <a href="{{ url('/') }}" class="fixed top-14 left-6 z-[100] bg-white/10 backdrop-blur-xl text-white p-3.5 rounded-2xl shadow-2xl border border-white/20 flex items-center justify-center hover:bg-[#F4B400] hover:text-[#0B2A4A] transition-all duration-300 group active:scale-95">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
        </svg>
    </a>

    {{-- HEADER HERO --}}
    <div class="relative h-[350px] flex flex-col items-center justify-center overflow-hidden">
        <div class="relative z-10 text-center px-6">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500/20 backdrop-blur-md border border-blue-400/30 rounded-full mb-6">
                <span class="w-2 h-2 bg-[#F4B400] rounded-full animate-ping"></span>
                <span class="text-[#F4B400] text-[10px] font-black uppercase tracking-widest">Layanan Publik Digital</span>
            </div>
            <h1 class="text-white text-4xl md:text-6xl font-black uppercase tracking-tighter leading-none mb-4">
                Sistem Pengaduan <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-[#F4B400] to-yellow-200">Asset Rusak</span>
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

        {{-- FORM UTAMA: Update Visual (Lebih Solid & Kontras) --}}
        <form action="{{ route('lapor.store') }}" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true">
            @csrf
            <input type="hidden" name="status" value="masuk">

            <div class="bg-white rounded-[2.5rem] shadow-[0_20px_50px_rgba(0,0,0,0.3)] p-8 md:p-12 border-b-8 border-[#F4B400] text-[#1E293B]">
                
                {{-- SECTION 1: DATA DIRI --}}
                <div class="mb-12">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-[#0B2A4A] text-[#F4B400] rounded-2xl flex items-center justify-center text-lg font-black">01</div>
                        <div>
                            <h3 class="text-base font-black text-[#0B2A4A] uppercase tracking-widest">Informasi Pelapor</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Identitas aman dalam enkripsi kami</p>
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

                {{-- SECTION 2: DETAIL --}}
                <div class="mb-10">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 bg-[#0B2A4A] text-[#F4B400] rounded-2xl flex items-center justify-center text-lg font-black">02</div>
                        <div>
                            <h3 class="text-base font-black text-[#0B2A4A] uppercase tracking-widest">Detail Kerusakan</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">Isi detail aset yang bermasalah</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1 tracking-widest">Aset Yang Dilaporkan</label>
                            <input type="text" name="judul_laporan" value="{{ old('judul_laporan') }}" placeholder="Misal: PJU Padam di Jl. Raya Gadobangkong" class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl py-4 px-6 text-sm font-bold focus:border-[#0B2A4A] focus:ring-4 focus:ring-blue-100 transition-all outline-none text-[#0B2A4A]" required>
                        </div>

                        {{-- UPLOAD PHOTO --}}
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1 tracking-widest">Bukti Foto (Wajib Kamera)</label>
                            <div class="relative group">
                                <div class="w-full py-10 bg-slate-50 border-2 border-dashed border-slate-300 rounded-[2rem] flex flex-col items-center justify-center transition-all group-hover:border-[#0B2A4A] group-hover:bg-blue-50/50">
                                    <div class="w-14 h-14 bg-white shadow-lg rounded-2xl flex items-center justify-center text-2xl mb-3">📸</div>
                                    <p class="text-[11px] font-black text-[#0B2A4A] uppercase tracking-widest">Klik Untuk Ambil Gambar</p>
                                    <input type="file" name="foto" id="foto_kamera" accept="image/*" capture="camera" class="absolute inset-0 opacity-0 cursor-pointer" required onchange="previewImage(event)">
                                </div>
                            </div>
                            <div id="image_preview_container" class="mt-4 hidden">
                                <img id="image_preview" src="#" class="w-full h-52 object-cover rounded-3xl border-4 border-white shadow-xl">
                            </div>
                        </div>

                        {{-- KONDISI --}}
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-500 uppercase ml-1 tracking-widest">Kategori Kondisi</label>
                            <div class="relative">
                                <select name="kondisi_aset" class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl py-4 px-6 text-sm font-black outline-none focus:border-[#0B2A4A] transition-all appearance-none cursor-pointer text-[#0B2A4A]" required>
                                    <option value="" disabled selected>Pilih Salah Satu</option>
                                    <option value="Rusak">🚨 RUSAK</option>
                                    <option value="Hilang">🚫 HILANG / DICURI</option>
                                    <option value="Pindah Tempat">📍 PINDAH POSISI</option>
                                    <option value="Lainnya">📝 LAINNYA</option>
                                </select>
                                <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none text-[#0B2A4A]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </div>
                            </div>
                        </div>

                        {{-- MAPS --}}
                        <div class="pt-6">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Titik Koordinat Lokasi</label>
                                <div class="flex gap-2 w-full sm:w-auto">
                                    <input type="text" id="manual_search" placeholder="Cari area/jalan..." class="flex-1 sm:w-48 bg-slate-100 border border-slate-200 rounded-xl py-2 px-4 text-[11px] font-bold outline-none focus:border-[#0B2A4A] text-[#0B2A4A]">
                                    <button type="button" onclick="searchAddress()" class="bg-[#0B2A4A] text-[#F4B400] px-5 py-2 rounded-xl text-[10px] font-black uppercase transition-all active:scale-90">Cari</button>
                                </div>
                            </div>
                            
                            <div class="relative overflow-hidden rounded-[2rem] shadow-inner border-2 border-slate-100">
                                <div id="miniMap" class="w-full h-64 bg-slate-200 z-0"></div>
                            </div>
                            
                            <div class="mt-4 px-6 py-4 bg-blue-50 rounded-2xl border border-blue-100 flex items-start gap-4">
                                <div class="w-3 h-3 mt-1 bg-red-600 rounded-full animate-pulse flex-shrink-0"></div>
                                <p class="text-[11px] font-black text-[#0B2A4A] leading-relaxed uppercase tracking-tight italic" x-text="alamat"></p>
                            </div>

                            <input type="hidden" name="lat" id="inp_lat">
                            <input type="hidden" name="lng" id="inp_lng">
                            <input type="hidden" name="alamat" id="inp_alamat">
                            <input type="hidden" name="lokasi_koordinat" id="inp_koordinat">
                        </div>
                    </div>
                </div>

                {{-- SUBMIT BUTTON --}}
                <div class="mt-12">
                    <button type="submit" 
                        x-bind:disabled="isSubmitting"
                        class="group relative w-full bg-[#0B2A4A] text-[#F4B400] py-5 rounded-[1.25rem] text-xs font-black uppercase tracking-[0.3em] shadow-xl hover:bg-black transition-all active:scale-95 disabled:opacity-70">
                        <span x-show="!isSubmitting" class="flex items-center justify-center gap-3">
                            Kirim Laporan Resmi
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </span>
                        <span x-show="isSubmitting" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-[#F4B400]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memproses Laporan...
                        </span>
                    </button>
                    <div class="flex flex-col items-center mt-8 gap-2">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/b/b2/Lambang_Kabupaten_Bandung_Barat.svg" class="h-8 grayscale">
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
    let map, marker;
    
    document.addEventListener("DOMContentLoaded", function () {
        map = L.map('miniMap', { zoomControl: false, attributionControl: false }).setView([-6.8431, 107.4912], 14);
        L.tileLayer("https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}", { maxZoom: 20 }).addTo(map);

        const customIcon = L.divIcon({
            className: 'custom-marker',
            html: "<div class='marker-pin'></div>",
            iconSize: [30, 30], iconAnchor: [15, 30]
        });

        marker = L.marker([-6.8431, 107.4912], {draggable: true, icon: customIcon}).addTo(map);

        map.on('click', function(e) { updatePosition(e.latlng.lat, e.latlng.lng); });
        marker.on('dragend', function() { let pos = marker.getLatLng(); updatePosition(pos.lat, pos.lng); });
    });

    async function updatePosition(lat, lng) {
        if(!marker) return;
        marker.setLatLng([lat, lng]);
        map.panTo([lat, lng]);
        
        document.getElementById('inp_lat').value = lat;
        document.getElementById('inp_lng').value = lng;
        document.getElementById('inp_koordinat').value = lat + ',' + lng;

        if(window.alpineData) window.alpineData.alamat = "MENDEFINISIKAN TITIK LOKASI...";

        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const data = await response.json();
            const fullAddress = data.display_name || "LOKASI TIDAK TERJANGKAU";

            if(window.alpineData) window.alpineData.alamat = fullAddress.toUpperCase();
            document.getElementById('inp_alamat').value = fullAddress;

        } catch (e) { 
            const fallback = `KOORDINAT: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            if(window.alpineData) window.alpineData.alamat = fallback; 
            document.getElementById('inp_alamat').value = fallback;
        }
    }

    function searchAddress() {
        const query = document.getElementById('manual_search').value;
        if (!query) return;
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${query}, Bandung Barat`)
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    updatePosition(parseFloat(data[0].lat), parseFloat(data[0].lon));
                    map.setView([data[0].lat, data[0].lon], 17);
                }
            });
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