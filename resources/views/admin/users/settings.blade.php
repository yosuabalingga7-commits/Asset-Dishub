@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<div class="min-h-screen bg-slate-50 pb-12" style="font-family: 'Inter', sans-serif;">
    {{-- Header Section --}}
    <div class="bg-indigo-900 pt-10 pb-24 px-8">
        <div class="max-w-4xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight">Pengaturan Akun</h1>
                <p class="text-indigo-200 text-sm mt-1 opacity-80">Kelola informasi profil dan keamanan akun petugas lapangan.</p>
            </div>
            
            {{-- Tombol Kembali Dinamis --}}
            @if(Auth::user()->role == 'super_admin')
                <a href="{{ route('admin.users.index') }}" class="bg-white/10 hover:bg-white/20 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-all backdrop-blur-sm border border-white/10">
                    ← Kembali ke Manajemen User
                </a>
            @else
                <a href="{{ url('admin/petugas/tugas-tersedia') }}" class="bg-white/10 hover:bg-white/20 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-all backdrop-blur-sm border border-white/10">
                    ← Kembali ke Tugas
                </a>
            @endif
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-4xl mx-auto -mt-16 px-6">
        
        @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-500 text-white rounded-2xl shadow-lg shadow-emerald-200 flex items-center gap-3">
            <span>✅</span>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 p-4 bg-rose-500 text-white rounded-2xl shadow-lg shadow-rose-200">
            <ul class="list-disc list-inside text-sm font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Form Action Dinamis --}}
        @php
            $formRoute = (Auth::user()->role == 'super_admin' && Auth::user()->id != $user->id) 
                         ? route('admin.users.update', $user->id) 
                         : route('settings.update');
        @endphp

        <form action="{{ $formRoute }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Kolom Kiri --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 text-center sticky top-6">
                        <div class="relative inline-block">
                            <img id="preview-foto" 
                                 src="{{ $user->foto ? asset('storage/' . $user->foto) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=4f46e5&color=fff&size=128' }}" 
                                 class="w-32 h-32 rounded-3xl object-cover border-4 border-slate-50 shadow-xl"
                                 alt="Profile Photo">
                            
                            <div class="absolute -bottom-2 -right-2 bg-slate-100 text-slate-400 w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2-2 0 002-2v-6a2-2 0 00-2-2H6a2-2 0 00-2 2v6a2-2 0 00-2 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h2 class="font-black text-slate-800 text-xl tracking-tight">{{ $user->name }}</h2>
                            <span class="inline-block mt-1 px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-full">
                                {{ $user->role ?? 'Seksi' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 font-bold">ID</div>
                            <h3 class="text-slate-800 font-extrabold tracking-tight">Identitas Pegawai</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                                <input type="text" value="{{ $user->name }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-500 font-bold focus:outline-none cursor-not-allowed" readonly>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Username / NIP</label>
                                <input type="text" value="{{ $user->nip }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-500 font-bold focus:outline-none cursor-not-allowed" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian Kontak --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center">📞</div>
                            <h3 class="text-slate-800 font-extrabold tracking-tight">Kontak & Akses</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-tight mb-2">Alamat Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-tight mb-2">Nomor WhatsApp</label>
                                <input type="text" name="no_wa" value="{{ old('no_wa', $user->no_wa) }}" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center">🔒</div>
                            <h3 class="text-slate-800 font-extrabold tracking-tight">Keamanan Password</h3>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-tight mb-2">Password Baru</label>
                                <input type="password" name="password" placeholder="Kosongkan jika tidak ganti" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-tight mb-2">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" placeholder="Ulangi password baru" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row items-center justify-end gap-4 pt-4">
                        @if(Auth::user()->role == 'super_admin')
                            <a href="{{ route('admin.users.index') }}" class="w-full sm:w-auto text-center px-8 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-100 transition-all">
                                Batal
                            </a>
                        @else
                            <a href="{{ url('admin/petugas/tugas-tersedia') }}" class="w-full sm:w-auto text-center px-8 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-100 transition-all">
                                Batal
                            </a>
                        @endif

                        <button type="submit" class="w-full sm:w-auto px-12 py-3 rounded-xl bg-indigo-600 text-white font-black text-sm shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 transition-all">
                            Simpan Perubahan Akun
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>
@endsection