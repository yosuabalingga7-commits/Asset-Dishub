@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#F8FAFC] min-h-screen font-['Inter']" 
      x-data="{ 
        filter: 'semua', 
        selectedTicket: null,
        showAssignModal: false,
        assigningTicketId: null
      }">
    
    {{-- 1. HEADER & OPERATIONAL SUMMARY --}}
    <div class="mb-10">
        {{-- NOTIFIKASI SUCCESS --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
             class="mb-6 flex items-center justify-between p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded-xl shadow-sm transition-all">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-lg"></i>
                <span class="text-xs font-black tracking-widest">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-green-500 hover:text-green-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-8">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-1 h-6 bg-[#2563EB] rounded-full"></div>
                    <nav class="flex text-[10px] font-black tracking-[0.2em] text-slate-400">
                        <span>Manajemen Aset</span>
                        <span class="mx-2">/</span>
                        <span class="text-[#1E293B]">Maintenance Log</span>
                    </nav>
                </div>
                {{-- Judul Utama - Navy Gelap #1E293B --}}
                <h1 class="text-2xl md:text-3xl font-black text-[#1E293B] tracking-tight">
                    Monitoring <span class="text-[#2563EB]">Pemeliharaan Tiket</span>
                </h1>
                <p class="text-xs font-medium text-slate-500 mt-1">
                    Sistem Pemantauan Internal Dishub KBB
                </p>
            </div>
            
            {{-- Filter Status - Border halus, teks Navy --}}
            <div class="bg-white px-5 py-3 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
                <i class="fas fa-filter text-slate-400 text-[10px]"></i>
                <span class="text-[10px] font-bold text-slate-500">Filter Status:</span>
                <select x-model="filter" class="text-[10px] font-bold outline-none bg-transparent text-[#1E293B] cursor-pointer">
                    <option value="semua">Semua Status</option>
                    <option value="process">Diproses</option>
                    <option value="finished">Selesai</option>
                </select>
            </div>
        </div>

        {{-- SUMMARY STATS - Angka menggunakan Navy #1E293B --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 mb-1">Total Tiket</p>
                <p class="text-2xl font-black text-[#1E293B]">{{ $tickets->total() }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 mb-1">Sedang Dikerjakan</p>
                <p class="text-2xl font-black text-[#1E293B]">{{ $tickets->where('status_slug', 'process')->count() }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 mb-1">Selesai</p>
                <p class="text-2xl font-black text-[#1E293B]">{{ $tickets->where('status_slug', 'finished')->count() }}</p>
            </div>
        </div>
    </div>

    {{-- 2. TICKETING GRID --}}
    @if($tickets->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($tickets as $index => $ticket)
        @php
            $prio = strtoupper($ticket['prioritas'] ?? 'NORMAL');
            
            // Badge Prioritas - Tinggi: Orange Bata, Normal: Navy muda/Slate
            $badgeStyle = match(true) {
                str_contains($prio, 'URGENT') || str_contains($prio, 'KRITIS') => 'bg-red-100 text-red-700 border-red-200',
                str_contains($prio, 'TINGGI') => 'bg-amber-100 text-amber-700 border-amber-200',
                str_contains($prio, 'NORMAL') || str_contains($prio, 'SEDANG') => 'bg-slate-100 text-slate-700 border-slate-200',
                default => 'bg-gray-100 text-gray-600 border-gray-200',
            };

            // Status Badge - Proses: Biru Royal dengan opasitas rendah, Selesai: Hijau Emerald gelap
            $statusSlug = $ticket['status_slug'] ?? 'process';
            $statusText = strtoupper($ticket['status'] ?? 'PROSES');
            
            $statusBadgeColor = ($statusSlug == 'finished') 
                ? 'bg-emerald-100 text-emerald-700 border-emerald-200' 
                : 'bg-blue-100 text-[#2563EB] border-blue-200';
            
            // Foto Kondisi
            $foto = $ticket['foto_sebelum'] ?? asset('img/default-asset.jpg');
            
            $ticketCode = $ticket['ticket_code'] ?? 'MNT-NEW';
            $namaAset = $ticket['asset'] ?? '-';
            $lokasi = $ticket['lokasi'] ?? '-';
            $pemilik = $ticket['source'] ?? 'DISHUB KBB';
            $progress = $ticket['progress_percent'] ?? 0;
        @endphp
        
        <div x-show="filter === 'semua' || filter === '{{ $statusSlug }}'" 
             class="bg-white rounded-2xl shadow-md border border-slate-100 flex flex-col transition-all duration-300 hover:shadow-lg overflow-hidden">
            
            {{-- Header Card dengan Gambar --}}
            <div class="relative h-40 w-full overflow-hidden bg-slate-100">
                <img src="{{ $foto }}" alt="Foto Aset" class="w-full h-full object-cover">
                <div class="absolute top-3 left-3">
                    <span class="px-2 py-1 rounded-lg text-[9px] font-bold shadow-sm border {{ $badgeStyle }}">
                        {{ $prio }}
                    </span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-3">
                    <p class="text-[8px] font-bold text-white/70 tracking-wider mb-0.5">TIKET MAINTENANCE</p>
                    <p class="text-[10px] font-mono font-bold text-white">{{ $ticketCode }}</p>
                </div>
            </div>

            {{-- Body Card --}}
            <div class="p-4">
                <div class="flex items-center justify-between text-[9px] text-slate-400 mb-3">
                    <span><i class="far fa-calendar-alt mr-1"></i> {{ $ticket['tgl_laporan'] }}</span>
                    <span class="px-2 py-0.5 rounded-full border {{ $statusBadgeColor }} text-[8px] font-bold">
                        {{ $statusText }}
                    </span>
                </div>

                <div class="mb-3">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Jenis Aset</p>
                    <p class="text-sm font-bold text-[#1E293B] leading-tight">{{ $namaAset }}</p>
                </div>

                <div class="mb-3">
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Lokasi Perbaikan</p>
                    <p class="text-[10px] text-slate-600">{{ \Illuminate\Support\Str::limit($lokasi, 60) }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Seksi / Petugas</p>
                        <p class="text-[10px] font-bold text-slate-700 truncate">
                            👤 {{ $ticket['petugas'] }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kepemilikan</p>
                        <p class="text-[10px] font-bold text-slate-700 flex items-center gap-1">
                            <i class="fas fa-building text-slate-500 text-[8px]"></i>
                            {{ $pemilik }}
                        </p>
                    </div>
                </div>

                {{-- Progress Bar - Fill menggunakan Biru Royal #2563EB --}}
                <div class="mb-4">
                    <div class="flex justify-between text-[8px] font-bold text-slate-500 mb-1">
                        <span>Penyelesaian Tahap Ini</span>
                        <span>{{ $progress }}%</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5">
                        <div class="bg-[#2563EB] h-1.5 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex gap-2">
                    {{-- Tombol Detail - Outline dengan border Biru Royal atau background abu-abu muda --}}
                    <a href="{{ route('admin.maintenance.show', $ticket['id']) }}" 
                       class="flex-1 bg-slate-100 text-slate-700 py-2.5 rounded-xl text-[9px] font-bold text-center hover:bg-slate-200 transition-all">
                        Detail
                    </a>
                    {{-- Tombol Edit/Update - Biru Royal #2563EB, hover Navy #1E293B --}}
                    <a href="{{ route('admin.maintenance.edit', $ticket['id']) }}" 
                       class="flex-1 bg-[#2563EB] text-white py-2.5 rounded-xl text-[9px] font-bold text-center hover:bg-[#1E293B] transition-all">
                        Edit / Update
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- 3. PAGINATION --}}
    <div class="mt-8 flex flex-col items-center gap-3">
        <p class="text-[9px] font-medium text-slate-400">
            Menampilkan {{ $tickets->firstItem() }} - {{ $tickets->lastItem() }} dari {{ $tickets->total() }} Tiket
        </p>
        <div class="bg-white p-2 rounded-xl shadow-sm border border-slate-100">
            {!! $tickets->links() !!}
        </div>
    </div>

    @else
    <div class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-slate-100">
        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
            <i class="fas fa-folder-open text-2xl text-slate-300"></i>
        </div>
        <h3 class="text-base font-bold text-slate-600">Tidak Ada Tiket</h3>
        <p class="text-[10px] text-slate-400 mt-1">Belum ada penugasan pemeliharaan aset Dishub saat ini</p>
    </div>
    @endif
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
    * { font-family: 'Inter', sans-serif; }
    [x-cloak] { display: none !important; }
    
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: #f1f1f1; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    
    .pagination { display: flex; gap: 4px; list-style: none; margin: 0; padding: 0; }
    .pagination li { display: inline-block; }
    .pagination a, .pagination span {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        border-radius: 8px;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        color: #64748b;
        background: #f8fafc;
        transition: all 0.2s;
    }
    .pagination a:hover { background: #e2e8f0; color: #334155; }
    .pagination .active span { background: #2563EB; color: white; }
    .pagination .disabled span { opacity: 0.5; background: #f1f5f9; }
</style>
@endsection