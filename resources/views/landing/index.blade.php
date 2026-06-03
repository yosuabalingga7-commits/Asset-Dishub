@extends('layouts.public')

@section('content')
<style>
    @keyframes wave-move {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .animate-wave-flow { animation: wave-move 20s linear infinite; }
    
    /* Update Gradasi Profesional: Menutup ke arah bawah dan samping */
    .hero-overlay-soft {
        background: linear-gradient(to right, 
            rgba(255,255,255,1) 0%, 
            rgba(255,255,255,0.9) 25%, 
            rgba(255,255,255,0.4) 60%, 
            rgba(255,255,255,0) 100%),
        linear-gradient(to bottom,
            rgba(255,255,255,0) 60%,
            rgba(255,255,255,1) 100%);
    }

    .text-huge {
        font-size: clamp(3.5rem, 10vw, 8rem);
        line-height: 0.9;
        letter-spacing: -0.05em;
    }

    /* Gaya untuk galeri */
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    .gallery-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .gallery-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -12px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="font-inter bg-white">
    {{-- Section Hero dengan Background & Gradasi Terpaku --}}
    <section class="relative min-h-screen flex items-center overflow-hidden pb-32 pt-20">
        
        <div class="absolute inset-0 z-0">
            <img src="{{ asset('img/background_dishub_kbb.png') }}" alt="Visual Dishub KBB" 
                 class="w-full h-full object-cover">
            
            {{-- Layer Gradasi --}}
            <div class="absolute inset-0 hero-overlay-soft"></div>
        </div>

        <div class="relative z-20 max-w-7xl mx-auto px-8 w-full">
            <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)" class="max-w-3xl">
                
                <div x-show="show" x-transition.duration.800ms
                     class="inline-flex items-center gap-3 px-4 py-1.5 rounded-full bg-blue-600/10 border border-blue-600/20 text-blue-600 text-[10px] font-bold uppercase tracking-[0.3em] mb-8">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                    </span>
                    Dishub Kabupaten Bandung Barat
                </div>
                
                <h1 x-show="show" x-transition.delay.200ms
                    class="text-huge font-black text-slate-900 mb-8">
                    LIN<span class="text-blue-600">TAS.</span>
                </h1>
                
                <div x-show="show" x-transition.delay.400ms class="mb-6">
                    <p class="text-xl md:text-2xl text-slate-800 font-bold tracking-tight uppercase">
                        Layanan Inventaris & Sistem Tata Aset
                    </p>
                </div>
                
                <p x-show="show" x-transition.delay.500ms
                   class="max-w-xl text-lg text-slate-600 leading-relaxed mb-10 font-medium">
                    Sistem pelaporan dan pendataan aset infrastruktur jalan yang terintegrasi untuk mewujudkan pelayanan publik yang prima di wilayah Kabupaten Bandung Barat.
                </p>

                {{-- Group Tombol --}}
                <div x-show="show" x-transition.delay.600ms
                     class="flex flex-wrap gap-5 relative z-50">
                    <a href="{{ route('lapor.public') }}" class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm tracking-widest uppercase shadow-lg shadow-blue-600/30 transition-all hover:-translate-y-1 active:scale-95">
                        LAPOR SEKARANG
                    </a>
                </div>
            </div>
        </div>

        {{-- Floating Badge: KBB Smart System --}}
        <div class="absolute bottom-32 right-12 z-30 hidden xl:block">
            <div class="p-6 bg-white/70 backdrop-blur-md border border-white/40 rounded-3xl shadow-xl max-w-xs transition-transform hover:scale-105 duration-500">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-600 rounded-xl shadow-lg shadow-blue-600/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-xs uppercase tracking-tight">
                        <span class="text-slate-900">LIN</span><span class="text-blue-600">TAS.</span>
                      </h4>
                        <p class="text-blue-600 text-[10px] font-bold">Layanan Inventaris & Sistem Tata Aset</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats Section --}}
    <section class="relative z-50 -mt-10 px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 bg-white border border-slate-100 rounded-[2.5rem] overflow-hidden shadow-2xl shadow-slate-200/50">
                <div class="p-10 text-center border-r border-slate-50 hover:bg-slate-50 transition-colors group">
                    <div class="text-3xl font-black text-slate-900 mb-2 group-hover:scale-110 transition-transform">Responsif</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Penanganan Terintegrasi</div>
                </div>
                <div class="p-10 text-center border-r border-slate-50 hover:bg-slate-50 transition-colors group">
                    <div class="text-3xl font-black text-blue-600 mb-2 group-hover:scale-110 transition-transform">Terintegrasi</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Data Aset Real-time</div>
                </div>
                <div class="p-10 text-center hover:bg-slate-50 transition-colors group">
                    <div class="text-3xl font-black text-orange-500 mb-2 group-hover:scale-110 transition-transform">Transparan</div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Proses Terpantau</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Tentang Section --}}
    <section id="tentang" class="py-32 bg-white">
        <div class="max-w-7xl mx-auto px-8">
            <div class="flex flex-col lg:flex-row justify-between items-end mb-24 gap-10">
                <div class="max-w-3xl">
                    <h2 class="text-5xl lg:text-7xl font-black text-slate-900 tracking-tighter leading-[0.9] mb-10">
                        Sinergi <br> Pendataan & Penataan.
                    </h2>
                    <p class="text-xl text-slate-600 font-medium italic leading-relaxed border-l-4 border-blue-600 pl-6">
                       LINTAS dirancang untuk mempermudah koordinasi pengelolaan aset dan pelaporan insiden infrastruktur demi kenyamanan masyarakat Kabupaten Bandung Barat.
                    </p>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-12">
                <div class="group p-8 rounded-3xl border border-transparent hover:border-slate-100 hover:bg-slate-50/50 transition-all duration-500">
                    <div class="w-16 h-16 bg-white shadow-lg rounded-2xl flex items-center justify-center mb-8 group-hover:bg-blue-600 group-hover:text-white transition-all">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4 uppercase tracking-tight">Lapor Cepat</h3>
                    <p class="text-slate-500 font-medium italic text-sm leading-relaxed">Lampirkan foto dan lokasi GPS untuk pelaporan kerusakan fasilitas jalan yang akurat.</p>
                </div>

                <div class="group p-8 rounded-3xl border border-transparent hover:border-slate-100 hover:bg-slate-50/50 transition-all duration-500">
                    <div class="w-16 h-16 bg-white shadow-lg rounded-2xl flex items-center justify-center mb-8 group-hover:bg-emerald-600 group-hover:text-white transition-all">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 013 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4 uppercase tracking-tight">Verifikasi</h3>
                    <p class="text-slate-500 font-medium italic text-sm leading-relaxed">Setiap laporan akan diverifikasi oleh tim teknis Dishub untuk penanganan lebih lanjut.</p>
                </div>

                <div class="group p-8 rounded-3xl border border-transparent hover:border-slate-100 hover:bg-slate-50/50 transition-all duration-500">
                    <div class="w-16 h-16 bg-white shadow-lg rounded-2xl flex items-center justify-center mb-8 group-hover:bg-orange-500 group-hover:text-white transition-all">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4 uppercase tracking-tight">Selesai</h3>
                    <p class="text-slate-500 font-medium italic text-sm leading-relaxed">Proses perbaikan aset dilakukan dengan standar yang jelas dan hasil yang terdokumentasi.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Galeri Section --}}
    <section id="kegiatan" class="py-28 bg-slate-50/80">
        <div class="max-w-7xl mx-auto px-8">
            <div class="text-center mb-16">
                <span class="text-blue-600 font-bold text-xs uppercase tracking-[0.3em] bg-blue-600/10 px-4 py-1.5 rounded-full inline-block mb-6">Galeri Kegiatan</span>
                <h2 class="text-4xl lg:text-6xl font-black text-slate-900 tracking-tighter mb-6">
                    Dokumentasi <span class="text-blue-600">Dishub KBB</span>
                </h2>
                <p class="text-slate-500 text-lg max-w-2xl mx-auto">
                    Wujud nyata sinergi dan penanganan aset infrastruktur untuk masyarakat Bandung Barat.
                </p>
            </div>

            <div class="gallery-grid">
                <!-- Foto 1 -->
                <div class="gallery-card bg-white rounded-2xl overflow-hidden shadow-md">
                    <div class="h-64 overflow-hidden">
                        <img src="{{ asset('img/kegiatan_dishub_kbb1.png') }}" alt="Kegiatan Dishub 1" class="w-full h-full object-cover transition duration-500 hover:scale-105">
                    </div>
                    <div class="p-5">
                        <h4 class="font-bold text-slate-800">Inspeksi Jalan & Jembatan</h4>
                        <p class="text-slate-500 text-sm mt-1">Tim teknis melakukan pengecekan rutin infrastruktur.</p>
                    </div>
                </div>
                <!-- Foto 2 -->
                <div class="gallery-card bg-white rounded-2xl overflow-hidden shadow-md">
                    <div class="h-64 overflow-hidden">
                        <img src="{{ asset('img/kegiatan_dishub_kbb2.png') }}" alt="Kegiatan Dishub 2" class="w-full h-full object-cover transition duration-500 hover:scale-105">
                    </div>
                    <div class="p-5">
                        <h4 class="font-bold text-slate-800">Koordinasi Lintas Sektor</h4>
                        <p class="text-slate-500 text-sm mt-1">Sinergi untuk percepatan penanganan aset daerah.</p>
                    </div>
                </div>
                <!-- Foto 3 -->
                <div class="gallery-card bg-white rounded-2xl overflow-hidden shadow-md">
                    <div class="h-64 overflow-hidden">
                        <img src="{{ asset('img/kegiatan_dishub_kbb3.png') }}" alt="Kegiatan Dishub 3" class="w-full h-full object-cover transition duration-500 hover:scale-105">
                    </div>
                    <div class="p-5">
                        <h4 class="font-bold text-slate-800">Sosialisasi LINTAS</h4>
                        <p class="text-slate-500 text-sm mt-1">Edukasi publik tentang layanan pelaporan aset.</p>
                    </div>
                </div>
                <!-- Foto 4 -->
                <div class="gallery-card bg-white rounded-2xl overflow-hidden shadow-md">
                    <div class="h-64 overflow-hidden">
                        <img src="{{ asset('img/kegiatan_dishub_kbb4.png') }}" alt="Kegiatan Dishub 4" class="w-full h-full object-cover transition duration-500 hover:scale-105">
                    </div>
                    <div class="p-5">
                        <h4 class="font-bold text-slate-800">Monitoring & Evaluasi</h4>
                        <p class="text-slate-500 text-sm mt-1">Evaluasi berkala untuk meningkatkan kualitas layanan.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection