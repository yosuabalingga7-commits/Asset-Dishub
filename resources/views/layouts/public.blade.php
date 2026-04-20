<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LINTAS - Layanan Inventaris & Tata Aset Sistem</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
        
        :root { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        html { scroll-behavior: smooth; }

        ::-webkit-scrollbar { width: 10px; }
        ::-webkit-scrollbar-track { background: #f8fafc; }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            border: 2px solid #f8fafc;
        }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-white text-slate-900 antialiased" x-data="{ mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">

    <nav :class="scrolled ? 'bg-white/95 backdrop-blur-xl border-b border-slate-100 py-3 shadow-md' : 'bg-transparent py-6'" 
         class="fixed w-full z-[1000] transition-all duration-500">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between items-center">
                
                <div class="flex items-center gap-4">
                    <img src="{{ asset('img/logo kbb.png') }}" alt="Logo KBB" class="h-12 w-auto">
                    <div class="flex flex-col border-l-2 border-slate-200 pl-4">
                        <span class="text-2xl font-black tracking-tighter leading-none text-slate-900">LINTAS</span>
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-[0.2em]">Bandung Barat</span>
                    </div>
                </div>

                <div class="hidden md:flex items-center">
                    <a href="{{ route('login') }}" 
                       class="px-6 py-2.5 bg-slate-900 hover:bg-blue-600 text-white text-xs font-bold rounded-full transition-all duration-300 shadow-lg shadow-slate-900/20 uppercase tracking-widest">
                        Login Portal
                    </a>
                </div>

                <div class="md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-slate-900 p-2 transition-colors">
                        <svg x-show="!mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                        <svg x-show="mobileMenuOpen" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="mobileMenuOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="md:hidden bg-white border-b border-slate-100 absolute w-full px-6 py-8 shadow-2xl">
            <a href="{{ route('login') }}" class="block text-center py-4 bg-blue-600 text-white rounded-xl text-sm font-bold tracking-widest uppercase">LOGIN PORTAL</a>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="bg-slate-50 text-slate-900 pt-24 pb-12 border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 mb-20">
                <div class="lg:col-span-5">
                    <div class="flex items-center gap-4 mb-8">
                        <img src="{{ asset('img/logodishub.png') }}" alt="Logo Dishub" class="h-10">
                        <span class="text-3xl font-black tracking-tighter italic text-slate-900">LINTAS</span>
                    </div>
                    <p class="text-slate-500 text-lg leading-relaxed mb-10 font-medium">
                        Layanan Inventaris & Tata Aset Sistem (LINTAS) merupakan instrumen digital strategis milik Dinas Perhubungan Kabupaten Bandung Barat.
                    </p>
                </div>

                <div class="lg:col-span-3 lg:offset-1">
                    <h4 class="text-slate-900 font-bold mb-8 uppercase tracking-[0.2em] text-xs">Akses Navigasi</h4>
                    <ul class="space-y-5">
                        <li><a href="{{ route('landing') }}" class="text-slate-500 hover:text-blue-600 transition font-semibold text-sm">Beranda Utama</a></li>
                        <li><a href="{{ route('lapor.public') }}" class="text-slate-500 hover:text-blue-600 transition font-semibold text-sm">Pelaporan Publik</a></li>
                        <li><a href="{{ route('login') }}" class="text-slate-500 hover:text-blue-600 transition font-semibold text-sm">Login Petugas</a></li>
                    </ul>
                </div>

                <div class="lg:col-span-3">
                    <h4 class="text-slate-900 font-bold mb-8 uppercase tracking-[0.2em] text-xs">Kontak & Lokasi</h4>
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center shrink-0 border border-slate-200">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <p class="text-slate-500 text-xs leading-relaxed font-medium">Ciwaruga, Parongpong, KBB.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-slate-200 pt-12 text-center">
                <p class="text-slate-400 text-[10px] font-bold uppercase tracking-[0.4em]">&copy; {{ date('Y') }} LINTAS KBB.</p>
            </div>
        </div>
    </footer>
</body>
</html>