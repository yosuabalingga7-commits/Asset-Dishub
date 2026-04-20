@extends('layouts.app')

@section('content')
<div class="p-6">
    {{-- Alert Section --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-emerald-500 text-white rounded-2xl font-bold shadow-lg shadow-emerald-200 animate-bounce">
            {{ session('success') }}
        </div>
    @endif

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Manajemen Akun Seksi</h1>
            <p class="text-slate-300 text-sm font-medium">Daftar seluruh akun seksi serta akses untuk menambahkan Seksi Baru</p>
        </div>
        <button onclick="document.getElementById('modalAddUser').classList.remove('hidden')" 
                class="px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold text-xs shadow-lg hover:bg-indigo-700 transition-all border-b-4 border-indigo-800 active:border-b-0 active:translate-y-1">
            + Tambah Seksi Baru
        </button>
    </div>

    {{-- Tabel Section --}}
    <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama / Username (NIP)</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Email & Bidang</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kontak</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Password</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-700 text-sm">{{ $user->name }}</span>
                                <span class="text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded-lg w-fit mt-1 uppercase">
                                    NIP: {{ $user->nip }} <span class="text-slate-400">({{ $user->role }})</span>
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-600">{{ $user->email }}</span>
                                <span class="text-[10px] text-indigo-500 font-black uppercase tracking-tighter mt-1">
                                    {{ $user->seksi->nama_seksi ?? 'BIDANG BELUM DISET' }}
                                </span>
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
                                <span class="text-[10px] font-bold text-slate-600 uppercase">{{ $user->status ?? 'Aktif' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('admin.users.settings', $user->id) }}" class="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition-all shadow-sm">
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
                                <p class="text-slate-400 font-bold text-sm tracking-tight">Belum ada akun seksi terdaftar.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH SEKSI --}}
<div id="modalAddUser" class="fixed inset-0 z-[100] hidden overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-md" onclick="document.getElementById('modalAddUser').classList.add('hidden')"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-[40px] w-full max-w-lg p-10 shadow-2xl border border-slate-100 relative z-10">
            <div class="flex justify-between items-center mb-8">
                <h3 class="text-2xl font-black text-slate-800 tracking-tighter">Tambah Seksi</h3>
                <button onclick="document.getElementById('modalAddUser').classList.add('hidden')" class="text-slate-400 hover:text-rose-500">
                    <i class="fas fa-times-circle text-2xl"></i>
                </button>
            </div>

            {{-- ERROR VALIDASI TAMPIL DI SINI --}}
            @if ($errors->any())
                <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-2xl">
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
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama Lengkap" class="w-full bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl p-4 outline-none transition-all text-slate-900 font-semibold" required>
                    </div>

                    <div class="relative">
                        <input type="text" name="nip" value="{{ old('nip') }}" placeholder="NIP / Username" class="w-full bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl p-4 outline-none transition-all text-slate-900 font-semibold uppercase" required>
                    </div>

                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="Email Instansi" class="w-full bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl p-4 outline-none transition-all text-slate-900 font-semibold" required>
                    </div>
                    
                    <div class="relative">
                        <select name="seksi_id" class="w-full bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl p-4 outline-none transition-all text-slate-900 font-semibold appearance-none" required>
                            <option value="" class="text-slate-400">-- Pilih Bidang --</option>
                            @foreach($daftar_seksi as $seksi)
                                <option value="{{ $seksi->id }}" {{ old('seksi_id') == $seksi->id ? 'selected' : '' }}>
                                    {{ $seksi->nama_seksi }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <i class="fas fa-chevron-down text-xs"></i>
                        </div>
                    </div>
                    
                    <div class="relative">
                        <input type="text" name="no_wa" value="{{ old('no_wa') }}" placeholder="No. WhatsApp (Contoh: 0812...)" class="w-full bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl p-4 outline-none transition-all text-slate-900 font-semibold" required>
                    </div>

                    <div class="relative">
                        <input type="password" name="password" placeholder="Password Akun" class="w-full bg-slate-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white rounded-2xl p-4 outline-none transition-all text-slate-900 font-semibold" required>
                    </div>
                </div>

                <div class="flex gap-3 mt-8">
                    <button type="button" onclick="document.getElementById('modalAddUser').classList.add('hidden')" 
                        class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-[20px] font-black text-sm uppercase tracking-widest hover:bg-slate-200 transition-all">
                        Batal
                    </button>
                    <button type="submit" class="flex-[2] py-4 bg-indigo-600 text-white rounded-[20px] font-black text-sm uppercase tracking-widest shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 transition-all active:translate-y-0">
                        Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT AUTO-OPEN MODAL JIKA ERROR --}}
@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('modalAddUser').classList.remove('hidden');
    });
</script>
@endif

@endsection