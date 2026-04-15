@php
    // Ambil data user yang sedang login secara resmi lewat Laravel Auth
    $user = Auth::user();

    /** * LOGIKA FOTO PROFIL:
     * Menentukan path foto berdasarkan data di database (kolom 'foto')
     */
    if ($user && $user->foto) {
        // Jika isinya URL lengkap (misal: link eksternal), pakai langsung
        if (filter_var($user->foto, FILTER_VALIDATE_URL)) {
            $pathFoto = $user->foto;
        } else {
            // Jika isinya path lokal, arahkan ke folder storage
            $pathFoto = asset('storage/' . $user->foto);
        }
    } else {
        // Fallback: Gunakan inisial nama jika user tidak punya foto
        $pathFoto = 'https://ui-avatars.com/api/?name=' . urlencode($user->name ?? 'U') . '&background=4f46e5&color=fff';
    }
@endphp

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<header class="h-16 bg-indigo-900 flex items-center justify-between px-6 shadow-lg shadow-indigo-950/20" style="font-family: 'Inter', sans-serif;">

    {{-- Kiri: Branding & Burger Menu --}}
    <div class="flex items-center gap-4">
        {{-- Tombol Burger Khusus Mobile --}}
        <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-white hover:bg-white/10 rounded-lg transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>

        <div class="flex flex-col">
            <h3 class="text-white font-bold text-sm tracking-tight leading-tight uppercase">
                Pemetaan Aset Infrastruktur
            </h3>
            <span class="text-[10px] font-medium text-indigo-300 uppercase tracking-wider opacity-80">
                Dinas Perhubungan Kabupaten Bandung Barat
            </span>
        </div>
    </div>

    {{-- Kanan: Menu Navigasi --}}
    <div class="flex items-center gap-6 text-white">
        
        {{-- Tombol Dashboard Mode --}}
        <button class="hidden sm:block text-xl hover:scale-110 transition-transform duration-200 opacity-90 hover:opacity-100" title="Mode Tampilan">
            🗂️
        </button>

        {{-- Dropdown Notifikasi --}}
        <div class="relative">
            <button id="notifTrigger" class="text-xl opacity-90 hover:opacity-100 transition-opacity relative group" title="Notifikasi">
                🔔
                <span class="absolute -top-1 -right-1 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
            </button>

            <div id="notifMenu" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 z-[9999] overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <span class="text-[11px] font-black text-slate-800 uppercase tracking-widest">Notifikasi</span>
                    <span class="text-[9px] font-bold text-white bg-indigo-600 px-2 py-0.5 rounded-full">Baru</span>
                </div>
                
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl opacity-40">📭</span>
                    </div>
                    <h4 class="text-slate-800 font-bold text-xs text-center">Belum Ada Notifikasi</h4>
                </div>

                <a href="#" class="block py-3 text-center text-[10px] font-black text-indigo-600 uppercase tracking-widest border-t border-gray-50 hover:bg-indigo-50 transition-all">
                    Lihat Semua Aktivitas
                </a>
            </div>
        </div>

        {{-- User Profile Section --}}
        <div class="relative">
            <div id="profileTrigger" class="flex items-center gap-3 pl-4 border-l border-white/10 group cursor-pointer">
                <div class="hidden sm:flex flex-col items-end mr-1">
                    <span class="text-[11px] font-extrabold text-white tracking-tight group-hover:text-indigo-200 transition-colors uppercase">
                        {{ $user->name ?? 'User LINTAS' }}
                    </span>
                    <span class="text-[9px] font-black text-indigo-300 uppercase tracking-widest leading-none">
                        {{ str_replace('_', ' ', $user->role ?? 'Petugas') }}
                    </span>
                </div>
                
                <div class="relative">
                    <img src="{{ $pathFoto }}" 
                         class="w-10 h-10 rounded-xl border-2 border-white/20 object-cover shadow-md group-hover:border-indigo-400 transition-all duration-300"
                         alt="Profile"
                         onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=User&background=4f46e5&color=fff';">
                    
                    {{-- Status Online Indicator --}}
                    <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 border-2 border-indigo-900 rounded-full"></div>
                </div>
                
                <span class="text-[10px] opacity-40 group-hover:opacity-100 transition-opacity">▼</span>
            </div>

            {{-- DROPDOWN MENU PROFILE --}}
            <div id="dropdownMenu" class="hidden absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 py-2 z-[9999] overflow-hidden">
                <div class="px-4 py-2 border-b border-gray-50 mb-1">
                    <p class="text-[9px] text-gray-400 uppercase font-black tracking-widest">Menu Navigasi</p>
                </div>
                
                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-all">
                    <span class="text-sm">⚙️</span> Pengaturan Akun
                </a>

                <div class="border-t border-gray-50 my-1"></div>
                
                {{-- TOMBOL LOGOUT AKTIF --}}
                <button type="button" 
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-red-500 hover:bg-red-50 transition-all">
                    <span class="text-sm">🚪</span> Keluar Sesi
                </button>

                {{-- FORM LOGOUT --}}
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </div>

</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileTrigger = document.getElementById('profileTrigger');
        const profileMenu = document.getElementById('dropdownMenu');
        const notifTrigger = document.getElementById('notifTrigger');
        const notifMenu = document.getElementById('notifMenu');

        // Fungsi Toggle untuk Profile Dropdown
        if(profileTrigger && profileMenu) {
            profileTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                profileMenu.classList.toggle('hidden');
                if(notifMenu) notifMenu.classList.add('hidden');
            });
        }

        // Fungsi Toggle untuk Notification Dropdown
        if(notifTrigger && notifMenu) {
            notifTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                notifMenu.classList.toggle('hidden');
                if(profileMenu) profileMenu.classList.add('hidden');
            });
        }

        // Tutup dropdown jika klik di area luar
        document.addEventListener('click', function(e) {
            if (profileMenu && !profileTrigger.contains(e.target)) {
                profileMenu.classList.add('hidden');
            }
            if (notifMenu && !notifTrigger.contains(e.target)) {
                notifMenu.classList.add('hidden');
            }
        });
    });
</script>