@extends('layouts.app')

@section('content')
{{-- State Alpine.js --}}
<div class="p-8 bg-[#F4F7FA] min-h-screen font-['Inter']" 
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
             class="mb-6 flex items-center justify-between p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 rounded-xl shadow-sm transition-all">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-lg"></i>
                <span class="text-xs font-black uppercase tracking-widest">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-8 border-b-2 border-slate-200 pb-6">
            <div>
                <nav class="flex mb-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                    <span>Manajemen Aset</span>
                    <span class="mx-2">/</span>
                    <span class="text-slate-600">Maintenance Log</span>
                </nav>
                <h1 class="text-3xl font-black text-[#0B2A4A] uppercase tracking-tighter">
                    Monitoring <span class="text-indigo-700">Pemeliharaan</span>
                </h1>
                <p class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-widest flex items-center gap-2">
                    <span class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></span>
                    Sistem Pemantauan Internal Dishub KBB
                </p>
            </div>
            
            <div class="bg-white px-5 py-3 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
                <i class="fas fa-filter text-slate-400 text-[10px]"></i>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Filter Status:</span>
                <select x-model="filter" class="text-[10px] font-black uppercase outline-none bg-transparent text-[#0B2A4A] cursor-pointer">
                    <option value="semua">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="process">Diproses</option>
                    <option value="finished">Selesai</option>
                </select>
            </div>
        </div>

        {{-- SUMMARY STATS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-2xl border-b-4 border-slate-300 shadow-sm">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mb-2">Total Tiket Aktif</p>
                <p class="text-3xl font-black text-slate-900">{{ $tickets->total() }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border-b-4 border-indigo-600 shadow-sm">
                <p class="text-[9px] font-black text-indigo-400 uppercase tracking-[0.2em] mb-2">Sedang Dikerjakan</p>
                <p class="text-3xl font-black text-indigo-700">{{ $tickets->where('status', 'process')->count() }}</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border-b-4 border-amber-500 shadow-sm">
                <p class="text-[9px] font-black text-amber-400 uppercase tracking-[0.2em] mb-2">Belum Ditugaskan</p>
                <p class="text-3xl font-black text-amber-600">{{ $tickets->where('status', 'pending')->count() }}</p>
            </div>
        </div>
    </div>

    {{-- 2. TICKETING GRID --}}
    @if($tickets->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($tickets as $index => $ticket)
        @php
            $prio = strtoupper($ticket['prioritas'] ?? 'NORMAL');
            
            $riskColor = match(true) {
                str_contains($prio, 'URGENT') || str_contains($prio, 'KRITIS') => 'border-l-rose-600',
                str_contains($prio, 'TINGGI') => 'border-l-orange-500',
                str_contains($prio, 'NORMAL') || str_contains($prio, 'SEDANG') => 'border-l-blue-500',
                default => 'border-l-emerald-500',
            };

            $badgeStyle = match(true) {
                str_contains($prio, 'URGENT') || str_contains($prio, 'KRITIS') => 'bg-rose-600 text-white border-rose-600 animate-pulse',
                str_contains($prio, 'TINGGI') => 'bg-orange-100 text-orange-700 border-orange-200',
                str_contains($prio, 'NORMAL') || str_contains($prio, 'SEDANG') => 'bg-blue-100 text-blue-700 border-blue-200',
                default => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            };

            $status = $ticket['status'];
            $progress = $ticket['progress_percent'] ?? 0;
            $hasPetugas = ($ticket['petugas'] !== 'Belum Ditugaskan' && $ticket['petugas'] !== 'Proses Validasi');
            
            // Rumus Nomor Urut
            $noUrut = ($tickets->currentPage() - 1) * $tickets->perPage() + $loop->iteration;
        @endphp
        
        <div x-show="filter === 'semua' || filter === '{{ $status }}'" 
             class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/40 border-2 border-slate-100 {{ $riskColor }} border-l-[8px] flex flex-col transition-all duration-300 hover:-translate-y-2 overflow-hidden relative">
            
            {{-- Badge Kepemilikan --}}
            <div class="absolute top-4 right-4 z-10">
                <span class="px-3 py-1.5 rounded-xl bg-indigo-600 text-white text-[8px] font-black uppercase tracking-widest shadow-lg flex items-center gap-2">
                    <i class="fas fa-building"></i> {{ $ticket['source'] }}
                </span>
            </div>

            <div class="relative h-40 w-full overflow-hidden bg-slate-200">
                <img src="{{ $ticket['foto_sebelum'] }}" alt="Kondisi Aset" class="w-full h-full object-cover">

                <div class="absolute top-4 left-4">
                    <span class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase shadow-sm border {{ $badgeStyle }}">
                         {{ $prio }}
                    </span>
                </div>
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-5 flex justify-between items-end">
                    <div>
                        <p class="text-[8px] font-black text-white/70 uppercase tracking-widest leading-none mb-1">ID TIKET</p>
                        <h3 class="text-sm font-black text-white tracking-tighter leading-none">#{{ $ticket['ticket_code'] }}</h3>
                    </div>
                    {{-- PENOMORAN (NO URUT) --}}
                    <div class="bg-white/20 backdrop-blur-sm px-2 py-1 rounded-md border border-white/30">
                        <span class="text-[10px] font-black text-white uppercase tracking-tighter">NO. {{ $noUrut }}</span>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="flex items-center gap-2 text-[9px] font-bold text-slate-400 mb-4">
                    <span class="flex items-center gap-1">
                        <i class="far fa-calendar-alt"></i> 
                        {{ $ticket['tgl_laporan'] }}
                    </span>
                </div>

                <div class="space-y-3 mb-6">
                    <div class="flex items-start gap-3">
                        <div class="p-2.5 bg-slate-100 rounded-xl text-[#0B2A4A]">
                            <i class="fas fa-tools text-xs"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Asset & Deskripsi</p>
                            <p class="text-xs font-black text-slate-800 uppercase leading-tight">
                                {{ $ticket['asset'] }} 
                            </p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="p-2.5 bg-slate-100 rounded-xl text-indigo-600">
                            <i class="fas fa-map-marker-alt text-xs"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Lokasi</p>
                            <p class="text-[10px] font-bold text-slate-500 italic leading-tight">
                                {{ \Illuminate\Support\Str::limit($ticket['lokasi'], 45) }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mb-6 p-4 {{ $hasPetugas ? 'bg-slate-50 border-slate-200' : 'bg-amber-50 border-amber-200' }} rounded-2xl border transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full {{ $hasPetugas ? 'bg-[#0B2A4A]' : 'bg-amber-500' }} flex items-center justify-center text-white border-4 border-white shadow-sm">
                                <i class="fas {{ $hasPetugas ? 'fa-user-tie' : 'fa-user-clock' }} text-xs"></i>
                            </div>
                            <div>
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-tighter">Kepala Seksi / Status Petugas</p>
                                <p class="text-[11px] font-black {{ $hasPetugas ? 'text-slate-800' : 'text-amber-700' }} uppercase leading-none mt-1">
                                    {{ $ticket['petugas'] }}
                                </p>
                            </div>
                        </div>
                        
                        @if(!$hasPetugas && $ticket['id'])
                            <a href="{{ route('admin.maintenance.edit', $ticket['id']) }}" 
                               class="flex items-center gap-1 px-3 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-all shadow-md shadow-indigo-100">
                                <i class="fas fa-user-plus text-[9px]"></i>
                                <span class="text-[8px] font-black uppercase">Tugaskan</span>
                            </a>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-3 border-t border-slate-100 pt-5">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex flex-col">
                            <span class="text-[8px] font-black text-slate-400 uppercase leading-none mb-1">Status: <span class="text-indigo-600">{{ strtoupper($status) }}</span></span>
                            <span class="text-[10px] font-black uppercase text-[#0B2A4A]">{{ $progress }}% Teratasi</span>
                        </div>
                        <div class="flex-1 max-w-[80px] bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-indigo-600 h-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        @if($ticket['id'])
                            <a href="{{ route('admin.maintenance.show', $ticket['id']) }}" 
                               class="flex-1 bg-slate-100 text-slate-700 py-3 rounded-xl text-[10px] font-black uppercase tracking-[0.1em] hover:bg-slate-200 transition-all text-center">
                                Detail
                            </a>
                            <a href="{{ route('admin.maintenance.edit', $ticket['id']) }}" 
                               class="flex-1 bg-indigo-600 text-white py-3 rounded-xl text-[10px] font-black uppercase tracking-[0.1em] flex items-center justify-center gap-2 transition-all shadow-lg shadow-indigo-100">
                                <i class="fas fa-pen text-[8px]"></i> Update
                            </a>
                        @else
                            <a href="{{ route('admin.maintenance.create', ['report_id' => $ticket['id']]) }}" 
                               class="w-full bg-indigo-600 text-white py-3 rounded-xl text-[10px] font-black uppercase tracking-[0.1em] flex items-center justify-center gap-2 transition-all shadow-lg shadow-indigo-100">
                                <i class="fas fa-plus text-[8px]"></i> Buat Tiket Maintenance
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- 3. PAGINATION --}}
    <div class="mt-12 flex flex-col items-center gap-4">
        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
            Menampilkan {{ $tickets->firstItem() }} - {{ $tickets->lastItem() }} dari {{ $tickets->total() }} Tiket
        </p>
        <div class="bg-white p-2 rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 flex items-center gap-1">
            {!! $tickets->links() !!}
        </div>
    </div>

    @else
    <div class="flex flex-col items-center justify-center py-20 bg-white rounded-[3rem] border-2 border-dashed border-slate-200">
        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
            <i class="fas fa-folder-open text-3xl text-slate-200"></i>
        </div>
        <h3 class="text-lg font-black text-[#0B2A4A] uppercase tracking-tighter">Tidak Ada Tiket Aktif</h3>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Belum ada penugasan pemeliharaan aset Dishub saat ini</p>
    </div>
    @endif
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap');
    [x-cloak] { display: none !important; }
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 10px; }
    
    .pagination svg { width: 1rem; height: 1rem; display: inline; }
</style>

{{-- WHATSAPP AUTO-OPENER SCRIPT - VERSI FINAL ANTI-BLOCK --}}
@if(session('open_wa'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: 'Tiket Berhasil Dibuat!',
            text: 'Klik tombol di bawah untuk mengirim detail tugas ke WhatsApp petugas.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#25D366',
            cancelButtonColor: '#d33',
            confirmButtonText: '<i class="fab fa-whatsapp"></i> Kirim WhatsApp',
            cancelButtonText: 'Tutup',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Membuka WA melalui aksi klik user (Aman dari popup blocker)
                window.open("{!! session('open_wa') !!}", '_blank');
            }
        });
    });
</script>
@endif

@endsection