@extends('layouts.app')

@section('content')
<div class="p-6 min-h-screen bg-[#F8FAFC]">
    {{-- Alert Section --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-emerald-500 text-white rounded-2xl font-bold shadow-lg shadow-emerald-200 animate-bounce">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-black tracking-tighter ">
                <span class="text-[#1E293B]">Manajemen</span>
                <span class="text-[#2563EB]">Akun Petugas</span>
        </h1>
            <p class="text-slate-500 text-sm font-medium">Daftar seluruh akun Petugas akses untuk menambahkan Petugas Baru</p>
        </div>
        {{-- TOMBOL TAMBAH PETUGAS - Royal Blue #2563EB --}}
        <button onclick="document.getElementById('modalAddUser').classList.remove('hidden')" 
                class="px-6 py-3 bg-[#2563EB] text-white rounded-2xl font-black text-xs tracking-widest shadow-lg hover:bg-[#1D4ED8] transition-all active:scale-95">
            + Tambah Petugas Baru
        </button>
    </div>

    {{-- Tabel Section --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-[#1E293B] border-b border-slate-700">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-white tracking-widest">Nama & (NIP)</th>
                        <th class="px-6 py-4 text-[10px] font-black text-white tracking-widest">Email & Bidang</th>
                        <th class="px-6 py-4 text-[10px] font-black text-white tracking-widest">Kontak</th>
                        <th class="px-6 py-4 text-[10px] font-black text-white tracking-widest">Password</th>
                        <th class="px-6 py-4 text-[10px] font-black text-white tracking-widest">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-white tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-[#1E293B] text-sm">{{ $user->name }}</span>
                                <span class="text-[10px] text-slate-600 font-bold bg-slate-100 px-2 py-0.5 rounded-lg w-fit mt-1 ">
                                    NIP: {{ $user->nip }} <span class="text-slate-400">({{ $user->role }})</span>
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-600">{{ $user->email }}</span>
                                @if($user->role == 'seksi')
                                    <span class="text-[10px] text-[#2563EB] font-black bg-blue-50 px-2 py-0.5 rounded-lg w-fit mt-1 tracking-tighter">
                                        {{ $user->seksi->nama_seksi ?? 'BIDANG BELUM DISET' }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                @if($user->no_wa)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $user->no_wa) }}" target="_blank" class="flex items-center gap-2 group">
                                    <span class="text-xs font-medium text-slate-600 group-hover:text-green-600 transition-colors">{{ $user->no_wa }}</span>
                                    <i class="fab fa-whatsapp text-green-500 text-sm"></i>
                                </a>
                                @else
                                <span class="text-xs font-medium text-slate-400">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-lg text-xs font-mono font-bold border border-slate-200">
                                {{ $user->password_plain ?? '******' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full {{ strtolower($user->status ?? 'aktif') == 'aktif' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                <span class="text-[10px] font-bold {{ strtolower($user->status ?? 'aktif') == 'aktif' ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $user->status ?? 'Aktif' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('admin.users.settings', $user->id) }}" class="p-2 border border-slate-200 text-[#2563EB] rounded-xl hover:bg-[#2563EB] hover:text-white hover:border-[#2563EB] transition-all shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-500 hover:text-white transition-all shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-4xl mb-4">👥</span>
                                <p class="text-slate-400 font-bold text-sm tracking-tight">Belum ada akun Petugas terdaftar.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
              </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH PETUGAS --}}
<div id="modalAddUser" class="fixed inset-0 z-[100] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-[#1E293B]/80 backdrop-blur-sm" onclick="document.getElementById('modalAddUser').classList.add('hidden')"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-100 relative z-10">
            <div class="sticky top-0 bg-white z-20 px-8 pt-8 pb-2 border-b border-slate-100 rounded-t-2xl">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-2xl font-black text-[#1E293B] tracking-tight">Tambah Petugas</h3>
                        <p class="text-xs text-slate-400 font-medium mt-1">Isi data akun Petugas di bawah ini</p>
                    </div>
                    <button onclick="document.getElementById('modalAddUser').classList.add('hidden')" class="text-slate-400 hover:text-rose-500 transition-colors p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="px-8 py-6">
                @if ($errors->any())
                    <div class="mb-5 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-2xl">
                        <p class="text-rose-600 text-[10px] font-black mb-2 tracking-widest">⚠️ Ada kesalahan pengisian:</p>
                        <ul class="list-disc list-inside text-rose-600 text-xs font-bold space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div class="relative">
                            <label class="block text-xs font-black text-slate-400  tracking-widest mb-2 ml-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: John Doe" class="w-full bg-slate-50 border-2 border-transparent focus:border-[#2563EB] focus:bg-white rounded-xl p-4 outline-none transition-all text-[#1E293B] font-semibold" required>
                        </div>

                        <div class="relative">
                            <label class="block text-xs font-black text-slate-400  tracking-widest mb-2 ml-1">NIP / Username</label>
                            <input type="text" name="nip" value="{{ old('nip') }}" placeholder="Contoh: 199208..." class="w-full bg-slate-50 border-2 border-transparent focus:border-[#2563EB] focus:bg-white rounded-xl p-4 outline-none transition-all text-[#1E293B] font-semibold" required>
                        </div>

                        <div class="relative">
                            <label class="block text-xs font-black text-slate-400  tracking-widest mb-2 ml-1">Email Instansi</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" class="w-full bg-slate-50 border-2 border-transparent focus:border-[#2563EB] focus:bg-white rounded-xl p-4 outline-none transition-all text-[#1E293B] font-semibold" required>
                        </div>
                        
                        {{-- DROPDOWN ROLE --}}
                        <div class="relative">
                            <label class="block text-xs font-black text-slate-400  tracking-widest mb-2 ml-1">Hak Akses (Role)</label>
                            <select id="roleSelect" name="role" class="w-full bg-slate-50 border-2 border-transparent focus:border-[#2563EB] focus:bg-white rounded-xl p-4 outline-none transition-all text-[#1E293B] font-semibold appearance-none cursor-pointer" required onchange="toggleBidang()">
                                <option value="" class="text-slate-400">-- Pilih Hak Akses --</option>
                                <option value="seksi" {{ old('role') == 'seksi' ? 'selected' : '' }}>Seksi / Petugas</option>
                                <option value="petugas_lapangan" {{ old('role') == 'petugas_lapangan' ? 'selected' : '' }}>Petugas Lapangan</option>
                                <option value="kadis" {{ old('role') == 'kadis' ? 'selected' : '' }}>Kepala Dinas</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                            </select>
                            <div class="absolute right-4 top-[70%] -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        {{-- DROPDOWN BIDANG --}}
                        <div id="bidangWrapper" class="relative">
                            <label class="block text-xs font-black text-slate-400  tracking-widest mb-2 ml-1">Bidang / Seksi</label>
                            <select name="seksi_id" class="w-full bg-slate-50 border-2 border-transparent focus:border-[#2563EB] focus:bg-white rounded-xl p-4 outline-none transition-all text-[#1E293B] font-semibold appearance-none cursor-pointer">
                                <option value="" class="text-slate-400">-- Pilih Bidang --</option>
                                @foreach($daftar_seksi as $seksi)
                                    <option value="{{ $seksi->id }}" {{ old('seksi_id') == $seksi->id ? 'selected' : '' }}>
                                        {{ $seksi->nama_seksi }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute right-4 top-[70%] -translate-y-1/2 pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        
                        <div class="relative">
                            <label class="block text-xs font-black text-slate-400  tracking-widest mb-2 ml-1">No. WhatsApp</label>
                            <input type="text" name="no_wa" value="{{ old('no_wa') }}" placeholder="Contoh: 0812..." class="w-full bg-slate-50 border-2 border-transparent focus:border-[#2563EB] focus:bg-white rounded-xl p-4 outline-none transition-all text-[#1E293B] font-semibold" required>
                        </div>

                        <div class="relative">
                            <label class="block text-xs font-black text-slate-400  tracking-widest mb-2 ml-1">Password</label>
                            <input type="text" name="password" placeholder="Masukkan Password Baru" class="w-full bg-slate-50 border-2 border-transparent focus:border-[#2563EB] focus:bg-white rounded-xl p-4 outline-none transition-all text-[#1E293B] font-bold" required>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-8 pb-2">
                        <button type="button" onclick="document.getElementById('modalAddUser').classList.add('hidden')" 
                            class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-xl font-black text-sm tracking-widest hover:bg-slate-200 transition-all cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" class="flex-[2] py-4 bg-[#2563EB] text-white rounded-xl font-black text-sm tracking-widest shadow-lg shadow-blue-500/20 hover:bg-[#1D4ED8] hover:-translate-y-1 transition-all active:translate-y-0 cursor-pointer">
                            Simpan Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Fungsi untuk menyembunyikan/menampilkan Bidang
    function toggleBidang() {
        const role = document.getElementById('roleSelect').value;
        const bidangWrapper = document.getElementById('bidangWrapper');
        
        if (role === 'admin' || role === 'kadis' || role === 'petugas_lapangan') {
            bidangWrapper.classList.add('hidden');
            if (bidangWrapper.querySelector('select')) {
                bidangWrapper.querySelector('select').value = "";
            }
        } else {
            bidangWrapper.classList.remove('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Jalankan saat halaman load (untuk menangani 'old' value)
        toggleBidang();

        @if($errors->any())
            document.getElementById('modalAddUser').classList.remove('hidden');
        @endif
    });
</script>

@endsection