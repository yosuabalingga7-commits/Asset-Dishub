@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
{{-- Tambahan CSS Cropper --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">

<style>
    .card-shadow {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
    }
    .focus-ring-blue:focus {
        ring-color: #3B82F6;
        border-color: #3B82F6;
    }
</style>

<div class="min-h-screen bg-slate-50 pb-12" style="font-family: 'Inter', sans-serif;">
    {{-- Header Section - Deep Navy #0f172a --}}
    <div class="bg-[#0f172a] pt-10 pb-24 px-8">
        <div class="max-w-4xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Pengaturan Akun</h1>
                <p class="text-slate-300 text-sm mt-1">Kelola informasi profil dan keamanan akun petugas lapangan.</p>
            </div>
            
            {{-- Tombol Kembali Dinamis berdasarkan Role --}}
            @if(Auth::user()->role == 'admin')
                <a href="{{ route('admin.users.index') }}" class="bg-transparent hover:bg-white/10 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-all border border-white/20">
                    ← Kembali ke Manajemen User
                </a>
            @elseif(Auth::user()->role == 'kadis')
                <a href="{{ route('kadis.dashboard') }}" class="bg-transparent hover:bg-white/10 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-all border border-white/20">
                    ← Kembali ke Dashboard
                </a>
            @else
                <a href="{{ route('petugas.tersedia') }}" class="bg-transparent hover:bg-white/10 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition-all border border-white/20">
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
            <span class="font-semibold text-sm">{{ session('success') }}</span>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- Kolom Kiri (Foto Profil) --}}
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl card-shadow border border-slate-100 p-8 text-center sticky top-6">
                    <div class="relative inline-block group">
                        <img id="preview-foto" 
                             src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=3B82F6&color=fff&size=128' }}" 
                             class="w-32 h-32 rounded-3xl object-cover border-4 border-slate-50 shadow-xl"
                             alt="Profile Photo">
                        
                        {{-- Input File Hidden --}}
                        <input type="file" id="profile_photo_input" class="hidden" accept="image/*">
                        
                        {{-- Tombol Hover untuk Upload --}}
                        <label for="profile_photo_input" class="absolute -bottom-2 -right-2 bg-[#3B82F6] text-white w-10 h-10 flex items-center justify-center rounded-xl border border-white shadow-lg cursor-pointer hover:bg-[#2563EB] transition-all">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </label>
                    </div>
                    <p class="text-xs text-slate-400 mt-4 font-medium">Klik ikon kamera untuk ganti foto</p>

                    <div class="mt-6">
                        <h2 class="font-bold text-slate-800 text-xl tracking-tight">{{ $user->name }}</h2>
                        <span class="inline-block mt-2 px-3 py-1 bg-blue-100 text-[#1e293b] text-xs font-semibold rounded-full">
                            {{ $user->role ?? 'Seksi' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan (Data Akun) --}}
            <div class="lg:col-span-2 space-y-6">
                
                @php
                    $formRoute = (Auth::user()->role == 'admin' && Auth::user()->id != $user->id) 
                                 ? route('admin.users.update', $user->id) 
                                 : route('settings.update');
                @endphp

                <form action="{{ $formRoute }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- Section Identitas Pegawai --}}
                    <div class="bg-white rounded-3xl card-shadow border border-slate-100 p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <span class="text-[#1e293b] font-bold text-sm">Id</span>
                            </div>
                            <h3 class="text-slate-800 font-bold text-base">Identitas Pegawai</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-2">Nama Lengkap</label>
                                <input type="text" value="{{ $user->name }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-medium focus:outline-none cursor-not-allowed" readonly>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-2">Username / Nip</label>
                                <input type="text" value="{{ $user->nip }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-medium focus:outline-none cursor-not-allowed" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- Bagian Kontak --}}
                    <div class="bg-white rounded-3xl card-shadow border border-slate-100 p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-lg">📞</div>
                            <h3 class="text-slate-800 font-bold text-base">Kontak & Akses</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-2">Alamat Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-[#3B82F6] transition-all" required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-2">Nomor Whatsapp</label>
                                <input type="text" name="no_wa" value="{{ old('no_wa', $user->no_wa) }}" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-slate-700 font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-[#3B82F6] transition-all">
                            </div>
                        </div>
                    </div>

                    {{-- Bagian Keamanan Password --}}
                    <div class="bg-white rounded-3xl card-shadow border border-slate-100 p-8">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-lg">🔒</div>
                            <h3 class="text-slate-800 font-bold text-base">Keamanan Password</h3>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-2">Password Baru</label>
                                <div class="relative">
                                    <input type="password" id="password" name="password" placeholder="Kosongkan jika tidak ganti" class="w-full border border-slate-200 rounded-xl px-4 py-3 pr-12 text-slate-700 font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-[#3B82F6] transition-all">
                                    <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <svg id="password-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-2">Konfirmasi Password Baru</label>
                                <div class="relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru" class="w-full border border-slate-200 rounded-xl px-4 py-3 pr-12 text-slate-700 font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-[#3B82F6] transition-all">
                                    <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <svg id="password_confirmation-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col sm:flex-row items-center justify-end gap-4 pt-2">
                        @php
                            $backRoute = (Auth::user()->role == 'admin') ? route('admin.users.index') : ((Auth::user()->role == 'kadis') ? route('kadis.dashboard') : route('petugas.tersedia'));
                        @endphp
                        
                        <a href="{{ $backRoute }}" class="w-full sm:w-auto text-center px-8 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-semibold text-sm hover:bg-slate-50 transition-all shadow-sm">
                            Batal
                        </a>

                        <button type="submit" class="w-full sm:w-auto px-12 py-3 rounded-xl bg-[#3B82F6] text-white font-semibold text-sm shadow-lg shadow-blue-200 hover:bg-[#2563EB] hover:-translate-y-1 transition-all">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal Editor Zoom & Crop --}}
<div id="modal-crop" class="fixed inset-0 z-[2000] hidden bg-slate-900/90 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl">
        <div class="p-5 border-b flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800 text-sm tracking-wide w-full text-center ml-6">Atur Posisi & Zoom Foto</h3>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-red-500 text-2xl">&times;</button>
        </div>
        <div class="p-6 bg-slate-100 text-center">
            <div class="max-h-[400px] flex justify-center items-center overflow-hidden rounded-2xl bg-white shadow-inner">
                <img id="image-to-crop" class="max-w-full block">
            </div>
            <p class="text-[11px] text-slate-500 mt-4 font-medium">Gunakan scroll mouse atau cubit layar untuk zoom</p>
        </div>
        <div class="p-5 bg-white flex gap-3 justify-center">
            <button type="button" onclick="closeModal()" class="px-6 py-2.5 text-xs font-bold text-slate-400">Batal</button>
            <button type="button" id="btn-crop" class="px-10 py-2.5 text-xs font-bold bg-[#3B82F6] text-white rounded-xl shadow-lg shadow-blue-500/30">Simpan Foto</button>
        </div>
    </div>
</div>

{{-- Hidden Form untuk Upload --}}
<form id="form-foto-final" action="{{ route('settings.updateFoto') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="cropped_image" id="cropped_image_input">
</form>

{{-- Script Cropper --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<script>
let cropper;
const inputFoto = document.getElementById('profile_photo_input');
const imageToCrop = document.getElementById('image-to-crop');
const modalCrop = document.getElementById('modal-crop');

inputFoto.addEventListener('change', function (e) {
    const files = e.target.files;
    if (files && files.length > 0) {
        const reader = new FileReader();
        reader.onload = function (event) {
            imageToCrop.src = event.target.result;
            modalCrop.classList.remove('hidden');
            
            if (cropper) cropper.destroy();
            cropper = new Cropper(imageToCrop, {
                aspectRatio: 1, 
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 1,
                zoomable: true,
            });
        };
        reader.readAsDataURL(files[0]);
    }
});

document.getElementById('btn-crop').addEventListener('click', function() {
    const canvas = cropper.getCroppedCanvas({ width: 512, height: 512 });
    document.getElementById('cropped_image_input').value = canvas.toDataURL('image/jpeg', 0.9);
    document.getElementById('form-foto-final').submit();
});

function closeModal() {
    modalCrop.classList.add('hidden');
    inputFoto.value = "";
}

function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    const eyeIcon = document.getElementById(fieldId + '-eye');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
    } else {
        input.type = 'password';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
    }
}
</script>
@endsection