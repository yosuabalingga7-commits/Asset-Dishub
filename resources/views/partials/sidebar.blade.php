<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<aside class="w-64 bg-slate-900 text-white flex flex-col h-screen border-r border-white/5" style="font-family: 'Inter', sans-serif;">
    
    <div class="p-6 mb-2">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center shadow-lg shadow-indigo-500/20">
                <span class="text-white font-black text-xs">DB</span>
            </div>
            <div class="flex flex-col">
                <span class="text-lg font-black tracking-tighter leading-none">DISHUB <span class="text-indigo-400">KBB</span></span>
                <span class="text-[9px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-1">Smart City GIS</span>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-4 overflow-y-auto custom-scrollbar">
        <div class="space-y-6">
            
            {{-- Group: Main Menu --}}
            <div>
                <p class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3">Menu Utama</p>
                <ul class="space-y-1.5">
                    {{-- Dashboard Utama --}}
                    <li>
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->is('/') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="text-lg group-hover:scale-110 transition-transform">🏠</span>
                            <span class="text-sm font-semibold tracking-tight">Dashboard</span>
                        </a>
                    </li>

                    {{-- GIS Monitoring --}}
                    <li>
                        <a href="{{ route('gis.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->is('admin/gis*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="text-lg group-hover:scale-110 transition-transform">🗺️</span>
                            <span class="text-sm font-semibold tracking-tight">Manajemen Aset & GIS</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Group: System Configuration --}}
            <div>
                <p class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3">Konfigurasi</p>
                <ul class="space-y-1.5">
                    {{-- Link Modul Settings Map --}}
                    <li>
                        <a href="{{ route('admin.map.settings') }}" 
                           class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->is('admin/map-settings*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <span class="text-lg group-hover:rotate-45 transition-transform">⚙️</span>
                                <span class="text-sm font-semibold tracking-tight">Konfigurasi Map</span>
                            </div>
                            <span class="text-[10px] bg-slate-800 text-slate-400 py-0.5 px-2 rounded-md font-bold group-hover:bg-white/10 group-hover:text-white transition-all">11/18</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Group: Reports & Maintenance --}}
            <div>
                <p class="px-3 text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] mb-3">Laporan & Layanan</p>
                <ul class="space-y-1.5">
                    {{-- Form Laporan Khusus Petugas (Internal) --}}
                    <li>
                        <a href="{{ route('laporan.petugas.create') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->is('admin/laporan-petugas*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="text-lg group-hover:scale-110 transition-transform">📸</span>
                            <span class="text-sm font-semibold tracking-tight">Monitoring Temuan</span>
                        </a>
                    </li>

                    {{-- Form Laporan Publik --}}
                    <li>
                        <a href="{{ route('lapor.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->is('lapor*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="text-lg group-hover:scale-110 transition-transform">📋</span>
                            <span class="text-sm font-semibold tracking-tight">Form Laporan Umum</span>
                        </a>
                    </li>

                    {{-- Daftar Tiket/Laporan Admin --}}
                    <li>
                        <a href="{{ route('admin.tiket.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->is('admin/tiket*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="text-lg group-hover:scale-110 transition-transform">📑</span>
                            <span class="text-sm font-semibold tracking-tight">Daftar Laporan</span>
                        </a>
                    </li>

                    {{-- Maintenance --}}
                    <li>
                        <a href="{{ route('admin.maintenance') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->is('admin/maintenance*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="text-lg group-hover:scale-110 transition-transform">🛠️</span>
                            <span class="text-sm font-semibold tracking-tight">Tiket Maintenance</span>
                        </a>
                    </li>

                    {{-- Log Aktivitas --}}
                    <li>
                        <a href="{{ route('admin.activity.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group {{ request()->is('admin/activity*') ? 'bg-indigo-600 shadow-lg shadow-indigo-600/20 text-white' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            <span class="text-lg group-hover:scale-110 transition-transform">📜</span>
                            <span class="text-sm font-semibold tracking-tight">Log Aktivitas</span>
                        </a>
                    </li>
                </ul>
            </div>

            <hr class="border-white/5 mx-3">

            {{-- Future Updates Section --}}
            <div class="pb-10">
                <p class="px-3 text-[10px] font-black text-slate-600 uppercase tracking-[0.15em] mb-3">Sistem (Segera)</p>
                <ul class="space-y-1">
                    <li class="flex items-center gap-3 px-3 py-2 text-slate-600 cursor-not-allowed italic">
                        <span class="grayscale opacity-50">📊</span>
                        <span class="text-[11px] font-medium tracking-tight">Monitoring & Dashboard</span>
                    </li>
                    <li class="flex items-center gap-3 px-3 py-2 text-slate-600 cursor-not-allowed italic">
                        <span class="grayscale opacity-50">👥</span>
                        <span class="text-[11px] font-medium tracking-tight">Manajemen User</span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</aside>

<style>
    /* Styling Scrollbar Sidebar agar tetap minimalis */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
    }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
    }
</style>