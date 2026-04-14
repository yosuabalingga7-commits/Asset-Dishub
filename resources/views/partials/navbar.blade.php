@php
    // BYPASS LOGIN: Ambil data user pertama secara manual agar tidak error 500
    // Gunakan optional() agar jika database kosong, aplikasi tidak crash
    $userDemo = \App\Models\User::first();
@endphp

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<header class="h-16 bg-indigo-900 flex items-center justify-between px-6 shadow-lg shadow-indigo-950/20" style="font-family: 'Inter', sans-serif;">

    <div class="flex flex-col">
        <h3 class="text-white font-bold text-sm tracking-tight leading-tight">
            Pemetaan Aset Infrastruktur
        </h3>
        <span class="text-[10px] font-medium text-indigo-300 uppercase tracking-wider opacity-80">
            Dinas Perhubungan Kabupaten Bandung Barat
        </span>
    </div>

    <div class="flex items-center gap-6 text-white">
        
        {{-- Tombol Mode --}}
        <button class="text-xl hover:scale-110 transition-transform duration-200 opacity-90 hover:opacity-100" title="Mode Tampilan">
            🗂️
        </button>

        {{-- Notifikasi --}}
        <div class="relative">
            <button id="notifTrigger" class="text-xl opacity-90 hover:opacity-100 transition-opacity relative group" title="Notifikasi">
                🔔
                <span class="absolute -top-1 -right-1 flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
            </button>

            {{-- DROPDOWN NOTIFIKASI --}}
            <div id="notifMenu" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-gray-100 z-[9999] overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <span class="text-[11px] font-black text-slate-800 uppercase tracking-widest">Notifikasi</span>
                    <span class="text-[9px] font-bold text-white bg-indigo-600 px-2 py-0.5 rounded-full">Baru</span>
                </div>
                
                <div class="p-8 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <span class="text-3xl opacity-40">📭</span>
                    </div>
                    <h4 class="text-slate-800 font-bold text-xs">Belum Ada Notifikasi</h4>
                    <p class="text-slate-400 text-[10px] mt-2 leading-relaxed px-4">
                        Pemberitahuan terkait tiket laporan dan status aset akan muncul di sini!
                    </p>
                </div>

                <a href="#" class="block py-3 text-center text-[10px] font-black text-indigo-600 uppercase tracking-widest border-t border-gray-50 hover:bg-indigo-50 transition-all">
                    Lihat Semua Aktivitas
                </a>
            </div>
        </div>

        {{-- User Profile Section --}}
        <div class="relative">
            <div id="profileTrigger" class="flex items-center gap-3 pl-4 border-l border-white/10 group cursor-pointer">
                <div class="flex flex-col items-end mr-1">
                    <span class="text-[11px] font-bold text-white tracking-tight group-hover:text-indigo-200 transition-colors">
                        {{ $userDemo->name ?? 'Naufal Paman' }}
                    </span>
                    <span class="text-[9px] font-black text-indigo-300 uppercase tracking-widest leading-none">
                        {{ $userDemo->role ?? 'Petugas Lapangan' }}
                    </span>
                </div>
                
                <div class="relative">
                    {{-- FOTO PROFIL DINAMIS DENGAN BYPASS --}}
                    @php
                        $pathFoto = ($userDemo && $userDemo->foto) 
                                    ? asset('storage/' . $userDemo->foto) 
                                    : 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=150&auto=format&fit=crop';
                    @endphp
                    <img src="{{ $pathFoto }}" 
                         class="w-10 h-10 rounded-xl border-2 border-white/20 object-cover shadow-md group-hover:border-indigo-400 transition-all duration-300"
                         alt="Admin Profile">
                    
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
                
                <button onclick="alert('Fungsi Logout akan aktif setelah sistem Login diimplementasikan.')" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-bold text-red-500 hover:bg-red-50 transition-all">
                    <span class="text-sm">🚪</span> Keluar Sesi
                </button>
            </div>
        </div>
    </div>

</header>

{{-- SCRIPT UNTUK DROPDOWN --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dropdown Profile
        const profileTrigger = document.getElementById('profileTrigger');
        const profileMenu = document.getElementById('dropdownMenu');

        // Dropdown Notification
        const notifTrigger = document.getElementById('notifTrigger');
        const notifMenu = document.getElementById('notifMenu');

        // Fungsi Toggle Profile
        if(profileTrigger && profileMenu) {
            profileTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                profileMenu.classList.toggle('hidden');
                if(notifMenu) notifMenu.classList.add('hidden'); // Tutup notif jika profile dibuka
            });
        }

        // Fungsi Toggle Notif
        if(notifTrigger && notifMenu) {
            notifTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                notifMenu.classList.toggle('hidden');
                if(profileMenu) profileMenu.classList.add('hidden'); // Tutup profile jika notif dibuka
            });
        }

        // Klik di luar untuk menutup semua dropdown
        document.addEventListener('click', function(e) {
            if (profileTrigger && !profileTrigger.contains(e.target)) {
                profileMenu.classList.add('hidden');
            }
            if (notifTrigger && !notifTrigger.contains(e.target)) {
                notifMenu.classList.add('hidden');
            }
        });
    });
</script>