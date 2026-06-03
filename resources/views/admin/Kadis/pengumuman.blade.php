@extends('layouts.app')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    * {
        font-family: 'Inter', sans-serif !important;
    }
    
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .card-pengumuman {
        transition: all 0.3s ease;
        animation: fadeInUp 0.4s ease forwards;
        opacity: 0;
        border: 1px solid #e2e8f0;
    }
    
    .card-pengumuman:nth-child(1) { animation-delay: 0.05s; }
    .card-pengumuman:nth-child(2) { animation-delay: 0.10s; }
    .card-pengumuman:nth-child(3) { animation-delay: 0.15s; }
    .card-pengumuman:nth-child(4) { animation-delay: 0.20s; }
    .card-pengumuman:nth-child(5) { animation-delay: 0.25s; }
    
    .card-pengumuman:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -12px rgba(0, 0, 0, 0.15);
    }
    
    .badge-penting {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        animation: pulse 1.5s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.85; transform: scale(0.98); }
    }
    
    .btn-action {
        transition: all 0.2s ease;
    }
    
    .btn-action:hover {
        transform: scale(1.05);
    }
    
    .btn-action:active {
        transform: scale(0.95);
    }
</style>

<div class="min-h-screen bg-[#F8FAFC] py-6 px-4 md:px-8">
    
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-black text-[#1E293B] tracking-tight">
                    Pengumuman <span class="text-[#3B82F6]">Kepala Dinas</span>
                </h1>
                <p class="text-slate-500 text-sm mt-1 flex items-center gap-2">
                    <span class="inline-block w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span>
                    Kelola pengumuman untuk petugas, kepala seksi, dan admin
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('kadis.pengumuman.create') }}" class="bg-[#3B82F6] hover:bg-[#2563EB] text-white px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 shadow-md hover:shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat Pengumuman Baru
                </a>
                <div class="text-right bg-white/60 backdrop-blur-sm px-4 py-2 rounded-xl shadow-sm">
                    <p class="text-[10px] text-slate-400 font-bold">TOTAL PENGUMUMAN</p>
                    <p class="text-[11px] font-bold text-slate-600">{{ $pengumuman->total() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6">
        <form method="GET" action="{{ route('kadis.pengumuman.index') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-[9px] font-bold text-slate-500 ml-1">Status</label>
                <select name="status" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>✅ Aktif</option>
                    <option value="arsip" {{ request('status') == 'arsip' ? 'selected' : '' }}>📦 Arsip</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-bold text-slate-500 ml-1">Target</label>
                <select name="target" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold">
                    <option value="">Semua Target</option>
                    <option value="semua" {{ request('target') == 'semua' ? 'selected' : '' }}>👥 Semua</option>
                    <option value="petugas_lapangan" {{ request('target') == 'petugas_lapangan' ? 'selected' : '' }}>👮 Petugas Lapangan</option>
                    <option value="kepala_seksi" {{ request('target') == 'kepala_seksi' ? 'selected' : '' }}>👔 Kepala Seksi</option>
                    <option value="admin" {{ request('target') == 'admin' ? 'selected' : '' }}>🖥️ Admin</option>
                </select>
            </div>
            <div>
                <label class="text-[9px] font-bold text-slate-500 ml-1">Jenis</label>
                <select name="jenis" class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold">
                    <option value="">Semua Jenis</option>
                    <option value="perubahan_layanan" {{ request('jenis') == 'perubahan_layanan' ? 'selected' : '' }}>📋 Perubahan Layanan</option>
                    <option value="info_operasional" {{ request('jenis') == 'info_operasional' ? 'selected' : '' }}>🚌 Info Operasional</option>
                    <option value="kebijakan_baru" {{ request('jenis') == 'kebijakan_baru' ? 'selected' : '' }}>📜 Kebijakan Baru</option>
                    <option value="instruksi_petugas" {{ request('jenis') == 'instruksi_petugas' ? 'selected' : '' }}>⚠️ Instruksi Petugas</option>
                    <option value="info_proyek" {{ request('jenis') == 'info_proyek' ? 'selected' : '' }}>🏗️ Info Proyek</option>
                    <option value="surat_edaran" {{ request('jenis') == 'surat_edaran' ? 'selected' : '' }}>📧 Surat Edaran</option>
                </select>
            </div>
            <div>
                <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition-all">
                    🔍 Filter
                </button>
                <a href="{{ route('kadis.pengumuman.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition-all inline-block">
                    ↺ Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-semibold flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- List Pengumuman --}}
    <div class="space-y-4">
        @forelse($pengumuman as $item)
        <div class="card-pengumuman bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2 flex-wrap mb-2">
                        <span class="text-[10px] font-black px-2 py-1 rounded-full {{ $item->jenis == 'instruksi_petugas' ? 'bg-red-100 text-red-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $item->label_jenis }}
                        </span>
                        <span class="text-[10px] font-black px-2 py-1 rounded-full bg-blue-100 text-blue-700">
                            {{ $item->label_target }}
                        </span>
                        @if($item->penting)
                        <span class="badge-penting text-[10px] font-black px-2 py-1 rounded-full text-white">
                            ⚠️ PRIORITAS
                        </span>
                        @endif
                        @if($item->status == 'aktif')
                        <span class="text-[10px] font-black px-2 py-1 rounded-full bg-emerald-100 text-emerald-700">
                            ✅ Aktif
                        </span>
                        @else
                        <span class="text-[10px] font-black px-2 py-1 rounded-full bg-slate-100 text-slate-600">
                            📦 Arsip
                        </span>
                        @endif
                    </div>
                    <h3 class="text-base font-black text-[#1E293B] mb-2">{{ $item->judul }}</h3>
                    <p class="text-xs text-slate-600 leading-relaxed line-clamp-2">{{ Str::limit($item->isi, 150) }}</p>
                    <div class="flex items-center gap-4 mt-3 text-[9px] text-slate-400">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ $item->pembuat->name ?? 'Tidak diketahui' }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            {{ $item->created_at->format('d/m/Y H:i') }}
                        </span>
                        @if($item->tanggal_selesai)
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Berlaku s/d {{ $item->tanggal_selesai->format('d/m/Y') }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('kadis.pengumuman.show', $item->id) }}" class="btn-action bg-slate-100 hover:bg-slate-200 text-slate-700 p-2 rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </a>
                    <a href="{{ route('kadis.pengumuman.edit', $item->id) }}" class="btn-action bg-amber-100 hover:bg-amber-200 text-amber-700 p-2 rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                    <form action="{{ route('kadis.pengumuman.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus pengumuman ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-xl transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @if($item->lampiran)
            <div class="mt-3 pt-3 border-t border-slate-100">
                <a href="{{ Storage::url($item->lampiran) }}" target="_blank" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                    </svg>
                    📎 Lihat Lampiran
                </a>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 text-center">
            <svg class="w-16 h-16 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
            </svg>
            <p class="text-slate-500 font-semibold">Belum ada pengumuman</p>
            <p class="text-slate-400 text-sm mt-1">Klik "Buat Pengumuman Baru" untuk memulai</p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $pengumuman->appends(request()->query())->links() }}
    </div>
</div>
@endsection