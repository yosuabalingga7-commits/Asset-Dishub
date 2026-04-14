@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F8FAFC] font-['Inter'] pb-12 text-left">
    {{-- Header --}}
    <div class="bg-white border-b border-slate-200 px-8 py-6 mb-8 shadow-sm">
        <div class="max-w-5xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <nav class="flex mb-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                    <a href="{{ route('admin.maintenance.index') }}" class="hover:text-[#0B2A4A] transition-colors">Monitoring</a>
                    <span class="mx-3 text-slate-300">/</span>
                    <span class="text-[#0B2A4A]">{{ isset($maintenance) ? 'Edit Penugasan' : 'Buat Tiket Baru' }}</span>
                </nav>
                <h1 class="text-2xl font-black text-[#0B2A4A] uppercase tracking-tighter">
                    {{ isset($maintenance) ? 'Update Petugas & Jadwal' : 'Registrasi Tiket Maintenance' }}
                </h1>
            </div>
            <a href="{{ url()->previous() }}" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-rose-500 transition-colors">
                ← Batal & Kembali
            </a>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-8">
        {{-- Form Path Dinamis --}}
        <form action="{{ isset($maintenance) ? route('admin.maintenance.update', $maintenance->id) : route('admin.maintenance.store') }}" 
              method="POST" class="space-y-6">
            @csrf
            @if(isset($maintenance)) @method('PUT') @endif
            
            {{-- DATA HIDDEN: Menyesuaikan data dari Laporan atau Maintenance existing --}}
            <input type="hidden" name="report_id" value="{{ isset($laporan) ? $laporan->id : ($maintenance->report_id ?? '') }}">
            <input type="hidden" name="location_address" value="{{ isset($laporan) ? $laporan->lokasi_koordinat : ($maintenance->location_address ?? '') }}">
            <input type="hidden" name="latitude" value="{{ isset($laporan) ? $laporan->lat : ($maintenance->latitude ?? '') }}">
            <input type="hidden" name="longitude" value="{{ isset($laporan) ? $laporan->lng : ($maintenance->longitude ?? '') }}">
            <input type="hidden" name="priority" value="{{ $maintenance->priority ?? 'Urgent' }}">

            {{-- Hidden Inputs untuk data yang didisabled agar tetap terkirim ke controller --}}
            @if(isset($maintenance))
                <input type="hidden" name="category_id" value="{{ $maintenance->category_id }}">
                <input type="hidden" name="jenis_aset" value="{{ $maintenance->jenis_aset }}">
                <input type="hidden" name="deadline" value="{{ \Carbon\Carbon::parse($maintenance->deadline)->format('Y-m-d') }}">
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Panel Kiri: Info Referensi Laporan --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-[#0B2A4A] rounded-[2.5rem] p-8 text-white shadow-2xl relative overflow-hidden">
                        <p class="text-[10px] font-black text-white/40 uppercase tracking-[0.2em] mb-6">Referensi Laporan</p>
                        
                        @php $ref = $laporan ?? ($maintenance->report ?? null); @endphp
                        
                        @if($ref)
                            <div class="space-y-4">
                                {{-- FOTO KONDISI --}}
                                <div class="relative group">
                                    <p class="text-[9px] font-black uppercase text-amber-500 mb-2">Foto Kondisi</p>
                                    @if($ref->foto)
                                        <img src="{{ asset('storage/' . $ref->foto) }}" class="w-full h-48 object-cover rounded-2xl border border-white/10 mb-4 shadow-lg">
                                    @else
                                        <div class="w-full h-32 bg-white/5 rounded-2xl border border-dashed border-white/20 flex items-center justify-center mb-4">
                                            <span class="text-[10px] text-white/30 uppercase font-bold">Tidak ada foto</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-4 bg-white/5 rounded-2xl border border-white/10">
                                    <p class="text-[9px] font-black uppercase text-blue-400 mb-1">Tiket & Judul</p>
                                    <p class="text-xs font-bold font-mono text-amber-400 mb-1">#{{ $ref->ticket_number ?? $ref->ticket_code }}</p>
                                    <p class="text-xs font-bold italic leading-relaxed">"{{ $ref->judul_laporan }}"</p>
                                </div>

                                <div class="p-4 bg-white/5 rounded-2xl border border-white/10">
                                    <p class="text-[9px] font-black uppercase text-emerald-400 mb-1">Lokasi & Koordinat</p>
                                    <p class="text-[10px] font-medium text-white/80 mb-2">{{ $ref->lokasi_koordinat ?? $ref->alamat ?? 'Alamat tidak tersedia' }}</p>
                                    <div class="flex gap-2">
                                        <span class="text-[9px] bg-white/10 px-2 py-1 rounded-md text-white/60 font-mono">LAT: {{ $ref->lat ?? '-' }}</span>
                                        <span class="text-[9px] bg-white/10 px-2 py-1 rounded-md text-white/60 font-mono">LNG: {{ $ref->lng ?? '-' }}</span>
                                    </div>
                                </div>
                                
                                <div class="p-4 bg-rose-500/10 rounded-2xl border border-rose-500/20">
                                    <p class="text-[9px] font-black uppercase text-rose-400 mb-1">Keluhan Masyarakat</p>
                                    <p class="text-[10px] leading-relaxed text-white/70">{{ $ref->deskripsi_keluhan ?? $ref->isi_laporan }}</p>
                                </div>
                            </div>
                        @else
                            <div class="p-6 text-center border-2 border-dashed border-white/10 rounded-3xl">
                                <p class="text-[10px] font-bold text-white/30 uppercase leading-relaxed">Input Internal (Manual)</p>
                            </div>
                        @endif
                    </div>

                    @if(isset($maintenance))
                        <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6">
                            <div class="flex items-center gap-3 text-amber-700 mb-2 font-black text-[10px] uppercase">
                                <i class="fas fa-user-edit"></i> Re-Assign Petugas
                            </div>
                            <p class="text-[10px] text-amber-600 font-bold leading-relaxed">Anda sedang mengubah penugasan. Harap berikan alasan yang jelas agar petugas memahami perubahan ini.</p>
                        </div>
                    @endif
                </div>

                {{-- Panel Kanan: Input Form --}}
                <div class="lg:col-span-2">
                    <div class="bg-white border border-slate-200 rounded-[2.5rem] p-10 shadow-sm space-y-8">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Kolom 1 --}}
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-4 block tracking-widest italic">1. Petugas Pelaksana</label>
                                    <select name="user_id" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-[#0B2A4A] text-xs font-bold focus:ring-2 focus:ring-emerald-500 outline-none transition-all shadow-inner">
                                        <option value="">-- Pilih Petugas --</option>
                                        @foreach($listPetugas as $petugas)
                                            <option value="{{ $petugas->id }}" 
                                                {{ (old('user_id', $maintenance->user_id ?? '') == $petugas->id) ? 'selected' : '' }}>
                                                👤 {{ strtoupper($petugas->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-4 block tracking-widest italic">2. Target Selesai (Deadline)</label>
                                    <input type="date" 
                                           value="{{ old('deadline', isset($maintenance->deadline) ? \Carbon\Carbon::parse($maintenance->deadline)->format('Y-m-d') : '') }}" 
                                           disabled
                                           class="w-full bg-slate-100 border border-slate-200 rounded-2xl p-4 text-slate-500 text-xs font-bold cursor-not-allowed">
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-4 block tracking-widest italic">Kepemilikan Aset</label>
                                    <div class="flex gap-4 opacity-60">
                                        @php $currentOwner = old('is_dishub', $maintenance->is_dishub ?? 1); @endphp
                                        <label class="flex-1">
                                            <div class="p-3 text-center border-2 {{ $currentOwner == 1 ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-slate-100 text-slate-400' }} rounded-xl text-[10px] font-black uppercase">Dishub</div>
                                        </label>
                                        <label class="flex-1">
                                            <div class="p-3 text-center border-2 {{ $currentOwner == 0 ? 'border-amber-500 bg-amber-50 text-amber-700' : 'border-slate-100 text-slate-400' }} rounded-xl text-[10px] font-black uppercase">Umum</div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom 2 --}}
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-4 block tracking-widest italic">3. Kategori Aset</label>
                                    <select disabled class="w-full bg-slate-100 border border-slate-200 rounded-2xl p-4 text-slate-500 text-xs font-bold cursor-not-allowed">
                                        <option>{{ old('category_id', $maintenance->category ?? ($laporan->kategori_laporan ?? '')) }}</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-4 block tracking-widest italic">4. Jenis Spesifik</label>
                                    <select disabled class="w-full bg-slate-100 border border-slate-200 rounded-2xl p-4 text-slate-500 text-xs font-bold cursor-not-allowed">
                                        <option>{{ old('jenis_aset', $maintenance->jenis_aset ?? '') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Instruksi --}}
                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase mb-4 block tracking-widest italic">5. Instruksi Perbaikan</label>
                            <textarea name="description" rows="3" required 
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-5 text-[#0B2A4A] text-xs font-medium outline-none focus:ring-2 focus:ring-indigo-500 transition-all resize-none shadow-inner"
                                placeholder="Tulis instruksi pengerjaan untuk petugas...">{{ old('description', $maintenance->description ?? ($laporan->deskripsi_keluhan ?? '')) }}</textarea>
                        </div>

                        {{-- Alasan Ganti Petugas / Catatan Edit --}}
                        <div>
                            <label class="text-[10px] font-black text-rose-500 uppercase mb-4 block tracking-widest italic">6. Alasan Perubahan Data (Wajib)</label>
                            <textarea name="edit_reason" rows="3" required 
                                class="w-full bg-amber-50/50 border-2 border-amber-200 rounded-2xl p-5 text-[#0B2A4A] text-xs font-bold outline-none focus:border-amber-500 transition-all resize-none shadow-sm"
                                placeholder="Jelaskan alasan perubahan petugas atau jadwal...">{{ old('edit_reason', $maintenance->edit_reason ?? '') }}</textarea>
                            <p class="text-[9px] text-slate-400 mt-3 italic font-bold flex items-center gap-2">
                                <i class="fas fa-info-circle text-amber-500"></i> Alasan ini akan tercatat permanen di Log Pengaduan Masyarakat.
                            </p>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex gap-4">
                            <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-lg transition-all active:scale-[0.98]">
                                <i class="fas fa-save mr-2"></i> SIMPAN PERUBAHAN TIKET
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    .font-black { font-weight: 900 !important; }
    /* Menghilangkan panah select pada yang disabled agar terlihat seperti label */
    select:disabled {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        opacity: 0.7;
    }
</style>
@endsection