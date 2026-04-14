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
                    <input type="text" placeholder="Cari log..." class="bg-white border border-slate-200 rounded-xl px-10 py-2.5 text-xs font-semibold w-64 focus:ring-2 focus:ring-indigo-500 outline-none transition-all shadow-sm">
                    <i class="fas fa-search absolute left-4 top-3.5 text-slate-400 text-xs"></i>
                </div>
                <button class="bg-white border border-slate-200 p-2.5 rounded-xl text-slate-500 hover:bg-slate-50 shadow-sm"><i class="fas fa-filter"></i></button>
            </div>
        </div>

        {{-- AUDIT CARDS STATS --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Aktivitas</p>
                <h3 class="text-2xl font-black text-[#0B2A4A]">{{ $stats['total'] }}</h3>
                <p class="text-[8px] text-emerald-600 font-bold mt-1"><i class="fas fa-caret-up mr-1"></i> 12% Dari bulan lalu</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm border-l-4 border-l-rose-500">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Gagal Login</p>
                <h3 class="text-2xl font-black text-rose-600">{{ $stats['failed'] }}</h3>
                <p class="text-[8px] text-slate-400 font-bold mt-1 uppercase">Butuh Atensi Keamanan</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Data Dihapus</p>
                <h3 class="text-2xl font-black text-slate-800">{{ $stats['danger'] }}</h3>
                <p class="text-[8px] text-slate-400 font-bold mt-1 uppercase">Aktivitas Irreversibel</p>
            </div>
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Validasi Berkas</p>
                <h3 class="text-2xl font-black text-indigo-600">{{ $stats['validate'] }}</h3>
                <p class="text-[8px] text-slate-400 font-bold mt-1 uppercase">Kinerja Admin</p>
            </div>
        </div>
    </div>

    {{-- 2. ACTIVITY LIST --}}
    <div class="space-y-3">
        @foreach($logs as $log)
        @php
            $config = [
                'DELETE'   => ['border' => 'border-l-rose-500', 'bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'icon' => 'fa-trash-alt'],
                'UPDATE'   => ['border' => 'border-l-blue-500', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'icon' => 'fa-edit-alt'],
                'CREATE'   => ['border' => 'border-l-emerald-500', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'icon' => 'fa-plus-circle'],
                'VALIDASI' => ['border' => 'border-l-amber-500', 'bg' => 'bg-amber-50', 'text' => 'text-amber-500', 'icon' => 'fa-check-double'],
                'LOGIN'    => ['border' => 'border-l-slate-400', 'bg' => 'bg-slate-50', 'text' => 'text-slate-400', 'icon' => 'fa-key'],
            ];
            $style = $config[$log['type']] ?? $config['LOGIN'];
            
            // Logika Nama Risiko Bahasa Indonesia
            $riskLabel = match($log['risk']) {
                'CRITICAL' => 'RISIKO KRITIS',
                'HIGH'     => 'RISIKO TINGGI',
                'MEDIUM'   => 'RISIKO SEDANG',
                'LOW'      => 'RISIKO RENDAH',
                default    => 'RISIKO NORMAL',
            };

            // Warna Risiko
            $riskColor = match($log['risk']) {
                'HIGH', 'CRITICAL' => 'bg-rose-100 text-rose-700 border-rose-200',
                'MEDIUM' => 'bg-amber-100 text-amber-700 border-amber-200',
                default => 'bg-slate-100 text-slate-600 border-slate-200',
            };
        @endphp

        <div class="bg-white border border-slate-100 border-l-[3.5px] {{ $style['border'] }} rounded-xl shadow-sm hover:shadow-md transition-all group cursor-pointer">
            <div class="flex flex-col lg:flex-row lg:items-center p-5 gap-6">
                
                {{-- LEFT: USER --}}
                <div class="flex items-center gap-4 min-w-[200px]">
                    <div class="w-10 h-10 rounded-lg {{ $style['bg'] }} {{ $style['text'] }} flex items-center justify-center text-sm shadow-inner">
                        <i class="fas {{ $style['icon'] }}"></i>
                    </div>
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-tighter">{{ $log['user'] }}</p>
                        <p class="text-[9px] font-bold text-slate-400 uppercase">{{ $log['role'] }}</p>
                    </div>
                </div>

                {{-- CENTER: NARRATIVE & MODUL BADGE --}}
                <div class="flex-1 lg:border-l lg:border-slate-50 lg:pl-6">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-[8px] font-black px-1.5 py-0.5 bg-slate-800 text-white rounded">[{{ $log['modul'] }}]</span>
                        <span class="text-[8px] font-black px-1.5 py-0.5 border {{ $riskColor }} rounded uppercase tracking-widest">{{ $riskLabel }}</span>
                        <p class="text-xs font-bold text-slate-700 ml-1">{{ $log['action'] }} <span class="text-indigo-600 underline decoration-indigo-200 underline-offset-4">{{ $log['target'] }}</span></p>
                    </div>
                    <p class="text-[10px] font-medium text-slate-400 leading-relaxed truncate max-w-xl">
                        "{{ $log['desc'] }}"
                    </p>
                </div>

                {{-- RIGHT: TIME & STATUS --}}
                <div class="flex items-center justify-between lg:justify-end gap-8 border-t lg:border-t-0 pt-3 lg:pt-0">
                    <div class="text-left lg:text-right">
                        <p class="text-[9px] font-black text-slate-700 uppercase tracking-tighter">{{ $log['time'] }} <span class="text-slate-300 ml-1">WIB</span></p>
                        <p class="text-[8px] font-bold text-slate-400 tracking-widest uppercase">IP: {{ $log['ip'] }}</p>
                    </div>

                    <div class="min-w-[80px] text-right">
                        @if($log['status'] === 'SUCCESS')
                            <span class="text-emerald-500 text-[10px] font-black uppercase tracking-widest">
                                <i class="fas fa-check-circle mr-1"></i> Sukses
                            </span>
                        @else
                            <span class="text-rose-500 text-[10px] font-black uppercase tracking-widest">
                                <i class="fas fa-times-circle mr-1"></i> Gagal
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- 3. ENTERPRISE FOOTER --}}
    <div class="mt-8 flex justify-between items-center bg-white p-4 rounded-xl border border-slate-100 shadow-sm text-[10px] font-bold text-slate-400 uppercase">
        <p>Menampilkan 5 aktivitas terbaru dari <span class="text-slate-900">{{ $stats['total'] }}</span> entri audit</p>
        <div class="flex gap-2">
            <button class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">Sebelumnya</button>
            <button class="px-3 py-1.5 rounded-lg bg-[#0B2A4A] text-white font-black">1</button>
            <button class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-[#0B2A4A] hover:bg-slate-50 transition-colors">2</button>
            <button class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-[#0B2A4A] hover:bg-slate-50 transition-colors">3</button>
            <button class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">Berikutnya</button>
        </div>
    </div>
</div>
@endsection