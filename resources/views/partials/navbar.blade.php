@php
    $user = Auth::user();
    if ($user && $user->foto) {
        $pathFoto = filter_var($user->foto, FILTER_VALIDATE_URL) ? $user->foto : asset('storage/' . $user->foto);
    } else {
        $pathFoto = 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?? 'Guest') . '&background=4f46e5&color=fff';
    }
    
    // Ambil data notifikasi dinamis
    $unreadCount = $user ? $user->unreadNotifications->count() : 0;
    $notifications = $user ? $user->notifications()->take(5)->get() : collect();
@endphp

<header class="h-16 bg-indigo-900 flex items-center justify-between px-4 md:px-6 shadow-lg shadow-indigo-950/20 sticky top-0 z-[1050] flex-shrink-0" 
        style="font-family: 'Inter', sans-serif;"
        x-data="{ notifOpen: false, profileOpen: false }">

    {{-- Kiri: Branding & Burger Menu --}}
    <div class="flex items-center gap-2 md:gap-4">
        <button @click="sidebarOpen = true" class="lg:hidden p-2 text-white hover:bg-white/10 rounded-lg transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>

        <div class="flex flex-col min-w-0">
            <h3 class="text-white font-bold text-xs md:text-sm tracking-tight leading-tight uppercase truncate">
                <span class="md:hidden">ASET DISHUB</span>
                <span class="hidden md:inline">Layanan Inventaris & Tata Aset Sistem</span>
            </h3>
            <span class="text-[8px] md:text-[10px] font-medium text-indigo-300 uppercase tracking-wider opacity-80 truncate">
                LINTAS DISHUB KBB
            </span>
        </div>
    </div>

    {{-- Kanan: Navigasi & Profile --}}
    <div class="flex items-center gap-3 md:gap-6 text-white">
        
        {{-- Notifikasi --}}
        <div class="relative">
            <button @click="notifOpen = !notifOpen; profileOpen = false" class="text-xl opacity-90 hover:opacity-100 relative p-1">
                🔔
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
                 x-transition:leave="transition ease-in duration-75"
                 class="absolute right-0 mt-3 w-72 md:w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 z-[1100] overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <span class="text-[10px] font-black text-slate-800 uppercase tracking-widest">Notifikasi</span>
                    @if($unreadCount > 0)
                        <span class="text-[9px] font-bold text-white bg-indigo-600 px-2 py-0.5 rounded-full">{{ $unreadCount }} Baru</span>
                    @endif
                </div>
                
                <div class="max-h-80 overflow-y-auto">
                    @forelse($notifications as $notif)
                        <a href="{{ route('notifications.read', $notif->id) }}" class="block px-5 py-4 border-b border-gray-50 hover:bg-indigo-50 transition-colors {{ $notif->read_at ? 'opacity-60' : '' }}">
                            <div class="flex gap-3">
                                <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm {{ $notif->data['type'] == 'success' ? 'bg-emerald-100' : 'bg-indigo-100' }}">
                                    {{ $notif->data['type'] == 'success' ? '✅' : '📢' }}
                                </div>
                                <div>
                                    <h5 class="text-[11px] font-black text-slate-800 leading-tight uppercase">{{ $notif->data['title'] }}</h5>
                                    <p class="text-[10px] text-slate-500 mt-1 leading-snug">{{ $notif->data['message'] }}</p>
                                    <span class="text-[8px] text-indigo-400 font-bold mt-2 block">{{ $notif->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-6 text-center">
                            <span class="text-2xl block mb-2 opacity-30">📭</span>
                            <h4 class="text-slate-800 font-bold text-[11px]">Belum Ada Notifikasi Baru</h4>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Profile --}}
        <div class="relative">
            <div @click="profileOpen = !profileOpen; notifOpen = false" 
                 class="flex items-center gap-2 pl-3 border-l border-white/10 group cursor-pointer">
                <div class="hidden sm:flex flex-col items-end">
                    <span class="text-[10px] font-extrabold text-white truncate max-w-[100px] uppercase">
                        {{ explode(' ', $user->name ?? 'Guest')[0] }}
                    </span>
                    <span class="text-[8px] font-black text-indigo-300 uppercase leading-none">
                        {{ $user->role ?? 'Masyarakat' }}
                    </span>
                </div>
                
                <div class="relative">
                    <img src="{{ $pathFoto }}" 
                         class="w-9 h-9 rounded-xl border-2 border-white/20 object-cover shadow-md group-hover:border-indigo-400 transition-all"
                         alt="Profile">
                    <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-indigo-900 rounded-full"></div>
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
                        <p class="text-[8px] text-indigo-500 font-bold uppercase">{{ $user->role }}</p>
                    </div>

                    <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-[11px] font-bold text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-all">
                        <span>⚙️</span> Pengaturan
                    </a>

                    <div class="border-t border-gray-50 my-1"></div>
                    
                    <button type="button" 
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                            class="w-full flex items-center gap-3 px-4 py-2.5 text-[11px] font-bold text-red-500 hover:bg-red-50 transition-all text-left">
                        <span>🚪</span> Keluar
                    </button>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-2.5 text-[11px] font-bold text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-all">
                        <span>🔑</span> Login Petugas
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