{{-- Link font --}}
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<aside 
    x-cloak
    @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-[60] w-64 bg-slate-900 text-white flex flex-col h-screen border-r border-white/5 transition-transform duration-300 ease-in-out lg:static lg:flex" 
    style="font-family: 'Inter', sans-serif;">
    
    <div class="p-6 mb-2 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <span class="text-white font-black text-xs">DB</span>
            </div>
            <div class="flex flex-col">
                <span class="text-lg font-black tracking-tighter leading-none uppercase">DISHUB <span class="text-indigo-400">KBB</span></span>
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-1">LINTAS</span>
            </div>
        </div>
        
        <button @click="sidebarOpen = false" class="lg:hidden p-2 -mr-2 text-slate-400 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="flex-1 px-4 overflow-y-auto custom-scrollbar">
        <div class="space-y-6">
            
            {{-- CEK APAKAH USER SUDAH LOGIN --}}
            @auth
                {{-- SEKSI MENU --}}
                @if(Auth::user()->role == 'seksi')
                <div>
                    <p class="px-3 text-[10px] font-black text-emerald-500 uppercase tracking-[0.15em] mb-3">Tugas Lapangan</p>
                    <ul class="space-y-1.5">
                        <li>
                            <a href="{{ route('petugas.tersedia') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('seksi/tugas-tersedia*') ? 'bg-emerald-600 shadow-lg shadow-emerald-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <span class="text-lg group-hover:scale-110 transition-transform">📋</span>
                                <span class="text-sm font-semibold tracking-tight">Tugas Tersedia</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('petugas.selesai') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('seksi/tugas-selesai*') ? 'bg-blue-600 shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <span class="text-lg group-hover:scale-110 transition-transform">✅</span>
                                <span class="text-sm font-semibold tracking-tight">Tugas Selesai</span>
                            </a>
                        </li>
                    </ul>
                </div>
                @endif

                {{-- ADMIN MENU --}}
                @if(Auth::user()->role == 'super_admin')
                <div>
                    <p class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3">Menu Utama</p>
                    <ul class="space-y-1.5">
                        <li>
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('map-view*') || Request::is('dashboard*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <span class="text-lg group-hover:scale-110 transition-transform">🏠</span>
                                <span class="text-sm font-semibold tracking-tight">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('gis.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/gis*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <span class="text-lg group-hover:scale-110 transition-transform">🗺️</span>
                                <span class="text-sm font-semibold tracking-tight">Manajemen Aset & GIS</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3">Laporan & Layanan</p>
                    <ul class="space-y-1.5">
                        <li>
                            <a href="{{ route('laporan.petugas.create') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/laporan-petugas*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <span class="text-lg group-hover:scale-110 transition-transform">📸</span>
                                <span class="text-sm font-semibold tracking-tight">Monitoring Temuan</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.tiket.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/tiket*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <span class="text-lg group-hover:scale-110 transition-transform">📑</span>
                                <span class="text-sm font-semibold tracking-tight">Daftar Laporan</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.maintenance') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/maintenance*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <span class="text-lg group-hover:scale-110 transition-transform">🛠️</span>
                                <span class="text-sm font-semibold tracking-tight">Maintenance</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3">Konfigurasi</p>
                    <ul class="space-y-1.5">
                        {{-- MENU MANAJEMEN USER --}}
                        <li>
                            <a href="{{ route('admin.users.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/users*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <span class="text-lg group-hover:scale-110 transition-transform">👥</span>
                                <span class="text-sm font-semibold tracking-tight">Manajemen User</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.map.settings') }}" 
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/map-settings*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg group-hover:rotate-45 transition-transform">⚙️</span>
                                    <span class="text-sm font-semibold tracking-tight">Konfigurasi Map</span>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
                @endif

                <hr class="border-white/5 mx-3">

                <div class="pb-10">
                    <p class="px-3 text-[10px] font-black text-slate-600 uppercase tracking-[0.15em] mb-3">Sistem</p>
                    <ul class="space-y-1">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-red-400 hover:bg-red-500/10 rounded-xl transition-all font-bold text-sm">
                                    <span>🚪</span> Keluar Sistem
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                {{-- Tampilan jika belum login (Optional: Link Login) --}}
                <div class="p-3">
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-2.5 bg-white/5 rounded-xl text-slate-400 hover:text-white transition-all">
                        <span>🔑</span>
                        <span class="text-sm font-semibold">Login Petugas</span>
                    </a>
                </div>
            @endauth

        </div>
    </nav>

    {{-- User Profile Mini Card --}}
    @auth
    <div class="p-4 border-t border-white/5 bg-slate-900/50 shrink-0">
        <div class="flex items-center gap-3 p-2 bg-white/5 rounded-2xl">
            <div class="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="flex flex-col min-w-0">
                <span class="text-[11px] font-bold text-white truncate">{{ Auth::user()->name }}</span>
                <span class="text-[9px] text-slate-500 uppercase font-black tracking-tighter">{{ Auth::user()->role }}</span>
            </div>
        </div>
    </div>
    @endauth
</aside>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.05); border-radius: 10px; }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); }
    [x-cloak] { display: none !important; }
</style>