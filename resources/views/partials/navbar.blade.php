@php
    $user = Auth::user();
    
    // Logika pengambilan foto profil yang sudah diupdate
    if ($user && $user->profile_photo_path) {
        $pathFoto = asset('storage/' . $user->profile_photo_path);
    } elseif ($user && $user->foto) {
        $pathFoto = filter_var($user->foto, FILTER_VALIDATE_URL) ? $user->foto : asset('storage/' . $user->foto);
    } else {
        $pathFoto = 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?? 'Guest') . '&background=2563EB&color=fff';
    }
    
    // Ambil data notifikasi dinamis
    $unreadCount = $user ? $user->unreadNotifications->count() : 0;
    $notifications = $user ? $user->notifications()->take(5)->get() : collect();
@endphp

<header class="h-16 bg-[#1E293B] flex items-center justify-between px-4 md:px-6 shadow-lg sticky top-0 z-[1050] flex-shrink-0" 
        style="font-family: 'Inter', sans-serif;"
        x-data="{ notifOpen: false, profileOpen: false }">

    {{-- Kiri: Tombol Hamburger (untuk SEMUA DEVICE) & Branding --}}
    <div class="flex items-center gap-2 md:gap-4">
        {{-- Tombol Hamburger untuk SEMUA DEVICE (Desktop & Mobile) - Bisa buka/tutup sidebar --}}
        <button @click="$store.sidebar.toggle()" class="p-2 text-white hover:bg-white/10 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <div class="flex flex-col min-w-0">
            <h3 class="text-white font-bold text-xs md:text-sm tracking-tight leading-tight uppercase truncate">
                <span class="md:hidden">ASET DISHUB</span>
                <span class="hidden md:inline">Layanan Inventaris & Sistem Tata Aset</span>
            </h3>
            <span class="text-[8px] md:text-[10px] font-medium text-slate-400 uppercase tracking-wider truncate">
                LINTAS DISHUB KBB
            </span>
        </div>
    </div>

    {{-- Kanan: Navigasi & Profile --}}
    <div class="flex items-center gap-3 md:gap-6 text-white">
        
        {{-- Notifikasi --}}
        <div class="relative">
            <button @click="notifOpen = !notifOpen; profileOpen = false" class="text-xl opacity-90 hover:opacity-100 relative p-1">
                <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.89 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/>
                </svg>
                @if($unreadCount > 0)
                <span class="absolute top-0 right-0 flex h-4 w-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-red-500 text-[10px] flex items-center justify-center font-bold text-white">{{ $unreadCount }}</span>
                </span>
                @endif
            </button>

            {{-- Dropdown Notif --}}
            <div x-show="notifOpen" 
                 @click.away="notifOpen = false"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute right-0 mt-3 w-72 md:w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 z-[1100] overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <span class="text-[10px] font-black text-slate-800 uppercase tracking-widest">Notifikasi</span>
                    @if($unreadCount > 0)
                        <span class="text-[9px] font-bold text-white bg-[#1E293B] px-2 py-0.5 rounded-full">{{ $unreadCount }} Baru</span>
                    @endif
                </div>
                
                <div class="max-h-80 overflow-y-auto">
                    @forelse($notifications as $notif)
                        <a href="{{ route('notifications.read', $notif->id) }}" class="block px-5 py-4 border-b border-gray-50 hover:bg-slate-50 transition-colors {{ $notif->read_at ? 'opacity-60' : '' }}">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm {{ $notif->data['type'] == 'success' ? 'bg-emerald-100' : 'bg-slate-100' }}">
                                    @if($notif->data['type'] == 'success')
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                    @endif
                                </div>
                                <div>
                                    <h5 class="text-[11px] font-black text-slate-800 leading-tight uppercase">{{ $notif->data['title'] }}</h5>
                                    <p class="text-[10px] text-slate-500 mt-1 leading-snug">{{ $notif->data['message'] }}</p>
                                    <span class="text-[8px] text-slate-400 font-bold mt-2 block">{{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-6 text-center">
                            <svg class="w-10 h-10 mx-auto text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 002 2H6a2 2 0 00-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <h4 class="text-slate-800 font-bold text-[11px]">Belum Ada Notifikasi Baru</h4>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Profile --}}
        <div class="relative">
            <div @click="profileOpen = !profileOpen; notifOpen = false" 
                 class="flex items-center gap-2 pl-3 border-l border-white/20 group cursor-pointer">
                <div class="hidden sm:flex flex-col items-end">
                    <span class="text-[10px] font-extrabold text-white truncate max-w-[100px] uppercase">
                        {{ explode(' ', $user->name ?? 'Guest')[0] }}
                    </span>
                    <span class="text-[8px] font-black text-slate-300 uppercase leading-none">
                        {{ $user->role ?? 'Masyarakat' }}
                    </span>
                </div>
                
                <div class="relative">
                    <img src="{{ $pathFoto }}" 
                         class="w-9 h-9 rounded-xl border-2 border-white/20 object-cover shadow-md group-hover:border-[#2563EB] transition-all"
                         alt="Profile">
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-[#1E293B] rounded-full"></div>
                </div>
            </div>

            {{-- Dropdown Profile --}}
            <div x-show="profileOpen" 
                 @click.away="profileOpen = false"
                 x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 z-[1100]">
                
                @auth
                    <div class="px-4 py-2 border-b border-gray-50 mb-1 sm:hidden">
                        <p class="text-[10px] font-black text-slate-800 truncate uppercase">{{ $user->name }}</p>
                        <p class="text-[8px] text-[#2563EB] font-bold uppercase">{{ $user->role }}</p>
                    </div>

                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-[11px] font-bold text-gray-600 hover:bg-slate-50 hover:text-[#1E293B] transition-all">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Pengaturan
                    </a>

                    <div class="border-t border-gray-50 my-1"></div>
                    
                    <button type="button" 
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-[11px] font-bold text-red-500 hover:bg-red-50 transition-all text-left">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Keluar
                    </button>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-2.5 text-[11px] font-bold text-gray-600 hover:bg-slate-50 hover:text-[#1E293B] transition-all">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Login Petugas
                    </a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Form Logout (Tetap di Luar) --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>
</header>