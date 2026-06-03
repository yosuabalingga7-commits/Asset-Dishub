{{-- Link font --}}
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<aside 
    x-cloak
    @toggle-sidebar.window="sidebarOpen = !sidebarOpen"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-[60] w-64 bg-[#1E293B] text-white flex flex-col h-screen border-r border-white/5 transition-transform duration-300 ease-in-out lg:static lg:flex" 
    style="font-family: 'Inter', sans-serif;">
    
    <div class="p-6 mb-2 flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
           <img src="{{ asset('img/logo_dishub_kbb.png') }}" alt="Logo" class="w-12 h-12 md:w-14 md:h-14 object-contain">
            <div class="flex flex-col">
                <span class="text-lg font-black tracking-tighter leading-none uppercase">DISHUB <span class="text-[#3B82F6]">KBB</span></span>
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
                {{-- MENU KEPALA DINAS (EKSEKUTIF) --}}
                @if(Auth::user()->role == 'kadis')
                <div>
                    <p class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3">Eksekutif</p>
                    <ul class="space-y-1.5">
                        <li>
                            <a href="{{ route('kadis.dashboard') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('kadis/dashboard*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Dashboard Eksekutif</span>
                            </a>
                        </li>
                        {{-- MENU PENGUMUMAN UNTUK KEPALA DINAS --}}
                        <li>
                            <a href="{{ route('kadis.pengumuman.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('kadis/pengumuman*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Pengumuman</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('gis.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/gis*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Monitoring GIS</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('assets.list') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/assets/list*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h12a2 2 0 012 2z M12 3v4 M8 3v4 M16 3v4 M4 11h16 M7 15h.01 M12 15h.01 M17 15h.01" />
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Daftar Aset</span>
                            </a>
                        </li>
                    </ul>
                </div>
                @endif

                {{-- SEKSI MENU --}}
                @if(Auth::user()->role == 'seksi')
                <div>
                    <p class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3">Tugas Lapangan</p>
                    <ul class="space-y-1.5">
                        <li>
                            <a href="{{ route('petugas.tersedia') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('seksi/tugas-tersedia*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Tugas Tersedia</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('petugas.selesai') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('seksi/tugas-selesai*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Tugas Selesai</span>
                            </a>
                        </li>
                    </ul>
                </div>
                @endif

                {{-- ADMIN MENU --}}
                @if(Auth::user()->role == 'admin')
                <div>
                    <p class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3">Menu Utama</p>
                    <ul class="space-y-1.5">
                        <li>
                            <a href="{{ route('dashboard') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('map-view*') || Request::is('dashboard*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('gis.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/gis*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Peta Sebaran Aset</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3">Laporan & Layanan</p>
                    <ul class="space-y-1.5">
                        <li>
                            <a href="{{ route('laporan.petugas.create') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/laporan-petugas*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Monitoring Temuan</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.tiket.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/tiket*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Daftar Laporan</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.maintenance') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/maintenance*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"></path>
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Daftar Penugasan</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('assets.list') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/assets*') || Request::is('assets*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h12a2 2 0 012 2z M12 3v4 M8 3v4 M16 3v4 M4 11h16 M7 15h.01 M12 15h.01 M17 15h.01" />
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Daftar Aset</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <p class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3">Konfigurasi</p>
                    <ul class="space-y-1.5">
                        <li>
                            <a href="{{ route('admin.users.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/users*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Manajemen User</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.map.settings') }}" 
                               class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/map-settings*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 group-hover:rotate-45 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span class="text-sm font-semibold tracking-tight">Konfigurasi Map</span>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('settings.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/settings*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:rotate-45 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Pengaturan</span>
                            </a>
                        </li>
                        {{-- MENU ACTIVITY LOG (RIWAYAT AKTIVITAS) --}}
                        <li>
                            <a href="{{ route('admin.activity.index') }}" 
                               class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ Request::is('admin/activity*') ? 'bg-[#3B82F6] shadow-lg shadow-blue-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span class="text-sm font-semibold tracking-tight">Log Aktivitas</span>
                            </a>
                        </li>
                    </ul>
                </div>
                @endif

                <hr class="border-white/5 mx-3">

                <div class="pb-10">
                    <p class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3">Sistem</p>
                    <ul class="space-y-1">
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-red-400 hover:bg-red-500/10 rounded-xl transition-all font-bold text-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    <span>Keluar Sistem</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                {{-- Tampilan jika belum login (Optional: Link Login) --}}
                <div class="p-3">
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-2.5 bg-white/5 rounded-xl text-slate-400 hover:text-white transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 11-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        <span class="text-sm font-semibold">Login Petugas</span>
                    </a>
                </div>
            @endauth

        </div>
    </nav>
</aside>

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 3px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.05); border-radius: 10px; }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.15); }
    [x-cloak] { display: none !important; }
</style>