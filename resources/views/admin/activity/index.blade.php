@extends('layouts.app')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    * {
        font-family: 'Inter', sans-serif !important;
        font-style: normal !important;
    }
    .group:hover { transform: translateY(-2px); }
</style>

<div class="p-8 bg-[#F8FAFC] min-h-screen text-slate-900">

    {{-- 1. HEADER & AUDIT STATISTICS --}}
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black text-[#0B2A4A] uppercase tracking-tighter">
                    Log <span class="text-indigo-600">Aktivitas</span>
                </h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">Rekaman Transparansi & Kontrol Internal Sistem</p>
            </div>
            
            <div class="flex gap-2">
                <div class="relative group">
                    <input type="text" id="searchLog" placeholder="Cari log..." class="bg-white border border-slate-200 rounded-xl px-10 py-2.5 text-xs font-semibold w-64 focus:ring-2 focus:ring-indigo-500 outline-none transition-all shadow-sm">
                    <i class="fas fa-search absolute left-4 top-3.5 text-slate-400 text-xs"></i>
                </div>
                <button onclick="window.location.reload()" class="bg-white border border-slate-200 p-2.5 rounded-xl text-slate-500 hover:bg-slate-50 shadow-sm"><i class="fas fa-sync-alt"></i></button>
            </div>
        </div>

        {{-- AUDIT CARDS STATS --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Aktivitas</p>
                <h3 class="text-2xl font-black text-[#0B2A4A]">{{ $stats['total'] }}</h3>
                <p class="text-[8px] text-emerald-600 font-bold mt-1"><i class="fas fa-database mr-1"></i> Data realtime</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm border-l-4 border-l-rose-500">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Gagal/Error</p>
                <h3 class="text-2xl font-black text-rose-600">{{ $stats['failed'] }}</h3>
                <p class="text-[8px] text-slate-400 font-bold mt-1 uppercase">Butuh Atensi</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Data Dihapus</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $stats['danger'] }}</h3>
                <p class="text-[8px] text-slate-400 font-bold mt-1 uppercase">Aktivitas Hapus</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Validasi Laporan</p>
                <h3 class="text-2xl font-black text-indigo-600">{{ $stats['validate'] }}</h3>
                <p class="text-[8px] text-slate-400 font-bold mt-1 uppercase">Kinerja Admin</p>
            </div>
        </div>
    </div>

    {{-- 2. ACTIVITY LIST --}}
    <div class="space-y-4" id="logContainer">
        @forelse($logs as $log)
        @php
            $config = [
                'DELETE'   => ['border' => 'border-l-rose-500', 'bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'icon' => 'fa-trash-alt'],
                'UPDATE'   => ['border' => 'border-l-blue-500', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'icon' => 'fa-edit'],
                'CREATE'   => ['border' => 'border-l-emerald-500', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'icon' => 'fa-plus-circle'],
                'VALIDASI' => ['border' => 'border-l-amber-500', 'bg' => 'bg-amber-50', 'text' => 'text-amber-500', 'icon' => 'fa-check-double'],
                'LOGIN'    => ['border' => 'border-l-slate-400', 'bg' => 'bg-slate-50', 'text' => 'text-slate-500', 'icon' => 'fa-key'],
            ];
            $style = $config[$log['type']] ?? $config['LOGIN'];
            
            $riskLabel = match($log['risk']) {
                'CRITICAL' => 'RISIKO KRITIS',
                'HIGH'     => 'RISIKO TINGGI',
                'MEDIUM'   => 'RISIKO SEDANG',
                'LOW'      => 'RISIKO RENDAH',
                default    => 'RISIKO NORMAL',
            };

            $riskColor = match($log['risk']) {
                'HIGH', 'CRITICAL' => 'bg-rose-100 text-rose-700 border-rose-200',
                'MEDIUM' => 'bg-amber-100 text-amber-700 border-amber-200',
                default => 'bg-slate-100 text-slate-600 border-slate-200',
            };
        @endphp

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-all group log-item"
             data-search="{{ strtolower($log['user'] . ' ' . $log['action'] . ' ' . $log['target'] . ' ' . $log['modul']) }}">
            
            <div class="flex flex-col md:flex-row p-5 gap-4">
                {{-- Profile Section --}}
                <div class="flex items-center gap-4 md:w-1/4">
                    <div class="w-12 h-12 rounded-full {{ $style['bg'] }} {{ $style['text'] }} flex items-center justify-center text-lg">
                        <i class="fas {{ $style['icon'] }}"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase text-slate-900">{{ $log['user'] }}</p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase">{{ $log['role'] }}</p>
                    </div>
                </div>

                {{-- Content Section --}}
                <div class="md:flex-1 md:border-l md:border-slate-100 md:pl-6">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-[9px] font-bold bg-slate-900 text-white px-2 py-0.5 rounded uppercase">{{ $log['modul'] }}</span>
                        <span class="text-[9px] font-bold px-2 py-0.5 border {{ $riskColor }} rounded uppercase tracking-wider">{{ $riskLabel }}</span>
                    </div>
                    <p class="text-xs font-bold text-slate-800 mb-1">{{ $log['action'] }} <span class="text-indigo-600">{{ $log['target'] }}</span></p>
                    <p class="text-[11px] text-slate-500 italic">"{{ $log['desc'] }}"</p>
                </div>

                {{-- Status & Meta Section --}}
                <div class="flex md:flex-col justify-between md:justify-center items-end md:items-end gap-2 md:w-40 border-t md:border-t-0 pt-3 md:pt-0">
                    <p class="text-[10px] font-bold text-slate-400">{{ $log['time'] }} WIB</p>
                    <p class="text-[9px] font-bold text-slate-300">IP: {{ $log['ip'] }}</p>
                    @if($log['status'] === 'SUCCESS')
                        <span class="text-emerald-600 text-[10px] font-black uppercase tracking-widest"><i class="fas fa-check-circle mr-1"></i> Sukses</span>
                    @else
                        <span class="text-rose-600 text-[10px] font-black uppercase tracking-widest"><i class="fas fa-times-circle mr-1"></i> Gagal</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-md border border-slate-100 p-12 text-center">
            <i class="fas fa-clipboard-list text-5xl text-slate-300 mb-4"></i>
            <p class="text-slate-400 font-bold text-sm">Belum ada aktivitas yang tercatat</p>
        </div>
        @endforelse
    </div>

    {{-- 3. PAGINATION --}}
    @if($logs->hasPages())
    <div class="mt-8 flex justify-between items-center bg-white p-4 rounded-xl border border-slate-100 shadow-sm text-[10px] font-bold text-slate-400 uppercase">
        <p>Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} entri audit</p>
        <div class="flex gap-2">
            {{ $logs->links('pagination::tailwind') }}
        </div>
    </div>
    @endif
</div>

<script>
    document.getElementById('searchLog')?.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const logItems = document.querySelectorAll('.log-item');
        logItems.forEach(item => {
            const searchData = item.getAttribute('data-search') || '';
            item.style.display = (searchTerm === '' || searchData.includes(searchTerm)) ? '' : 'none';
        });
    });
</script>
@endsection