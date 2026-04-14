@extends('layouts.app') {{-- Pastikan mengarah ke layout master kamu --}}

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
            <a href="{{ url('/') }}" class="bg-white/10 hover:bg-white/20 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-all backdrop-blur-sm border border-white/10">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-4xl mx-auto -mt-16 px-6">
        
        {{-- Notifikasi Sukses --}}
        @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-500 text-white rounded-2xl shadow-lg shadow-emerald-200 flex items-center gap-3 animate-bounce">
            <span>✅</span>
            <span class="font-bold text-sm">{{ session('success') }}</span>
        </div>
        @endif

        {{-- Notifikasi Error Validasi --}}
        @if($errors->any())
        <div class="mb-6 p-4 bg-rose-500 text-white rounded-2xl shadow-lg shadow-rose-200">
            <ul class="list-disc list-inside text-sm font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Kolom Kiri: Foto Profil --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 text-center sticky top-6">
                        <div class="relative inline-block group">
                            {{-- Preview Foto Dinamis --}}
                            <img id="preview-foto" 
                                 src="{{ $user->foto ? asset('storage/' . $user->foto) : 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=150' }}" 
                                 class="w-32 h-32 rounded-3xl object-cover border-4 border-slate-50 shadow-xl group-hover:opacity-90 transition-all duration-300"
                                 alt="Profile Photo">
                            
                            {{-- Tombol Upload --}}
                            <label for="foto-input" class="absolute -bottom-2 -right-2 bg-indigo-600 text-white w-10 h-10 flex items-center justify-center rounded-xl cursor-pointer hover:bg-indigo-700 shadow-lg hover:scale-110 transition-all">
                                <span class="text-lg">📷</span>
                                <input type="file" id="foto-input" name="foto" class="hidden" accept="image/*" onchange="previewImage(this)">
                            </label>
                        </div>

                        <div class="mt-6">
                            <h2 class="font-black text-slate-800 text-xl tracking-tight">{{ $user->name }}</h2>
                            <span class="inline-block mt-1 px-3 py-1 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest rounded-full">
                                {{ $user->role ?? 'Petugas Lapangan' }}
                            </span>
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-slate-100 flex flex-col gap-2">
                            <p class="text-slate-400 text-[10px] italic font-medium leading-relaxed">
                                Disarankan foto format JPG/PNG dengan ukuran maksimal 2MB.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Form Detail --}}
                <div class="lg:col-span-2 space-y-6">
                    
                    {{-- Box 1: Informasi Identitas (Read-Only) --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600">🆔</div>
                            <h3 class="text-slate-800 font-extrabold tracking-tight">Identitas Pegawai</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nama Lengkap</label>
                                <input type="text" value="{{ $user->name }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-500 font-bold focus:outline-none cursor-not-allowed" readonly>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">NIP / Username</label>
                                <input type="text" value="{{ $user->nip ?? '19920815XXXXXX' }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-500 font-bold focus:outline-none cursor-not-allowed" readonly>
                            </div>
                        </div>
                        <div class="mt-4 flex items-start gap-2 text-[10px] text-amber-600 bg-amber-50 p-3 rounded-lg border border-amber-100">
                            <span>⚠️</span>
                            <p class="font-medium">Data Nama dan NIP sudah diverifikasi sistem. Hubungi Admin Dishub untuk perubahan data master.</p>
                        </div>
                    </div>

                    {{-- Box 2: Kontak --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600">📞</div>
                            <h3 class="text-slate-800 font-extrabold tracking-tight">Kontak & Akses</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-tight mb-2">Alamat Email</label>
                                <input type="email" name="email" value="{{ $user->email }}" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium" required>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-tight mb-2">Nomor WhatsApp/HP</label>
                                <input type="text" name="no_wa" value="{{ $user->no_wa ?? '' }}" placeholder="Contoh: 08123456789" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                            </div>
                        </div>
                    </div>

                    {{-- Box 3: Keamanan --}}
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center text-rose-600">🔒</div>
                            <h3 class="text-slate-800 font-extrabold tracking-tight">Keamanan Password</h3>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-tight mb-2">Password Baru</label>
                                <input type="password" name="password" placeholder="Isi hanya jika ingin ganti password" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-tight mb-2">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" placeholder="Ulangi password baru" class="w-full border border-slate-200 rounded-xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-medium">
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center justify-end gap-4 pt-4">
                        <button type="reset" class="px-8 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-100 transition-all">
                            Batal
                        </button>
                        <button type="submit" class="px-12 py-3 rounded-xl bg-indigo-600 text-white font-black text-sm shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 active:translate-y-0 transition-all">
                            Simpan Perubahan Akun
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Live Preview Image
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-foto').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection