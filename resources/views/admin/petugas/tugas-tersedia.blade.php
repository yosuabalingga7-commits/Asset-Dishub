@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#F4F7FA] min-h-screen font-['Inter']">
    
    {{-- HEADER --}}
    <div class="mb-10">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase">TUGAS TERSEDIA</h1>
        <p class="text-slate-500 font-medium mt-1 text-sm tracking-tight">
            Yth. {{ Auth::user()->name }}, mohon untuk melaksanakan tugas ini dengan dedikasi dan profesionalisme tinggi.
        </p>
    </div>

    {{-- GRID TIKET --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($tasks as $index => $task)
            <div class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-200 shadow-sm hover:shadow-2xl transition-all duration-500 group relative">
                
                {{-- BAGIAN ATAS: GAMBAR & BADGE --}}
                <div class="relative h-64 overflow-hidden">
                    {{-- Perbaikan Baris 21: Menggunakan -> --}}
                    <img src="{{ $task->foto }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    
                    {{-- Badge Prioritas --}}
                    <div class="absolute top-6 left-6">
                        <span class="px-5 py-2 bg-white/90 backdrop-blur-md text-indigo-600 rounded-full text-[10px] font-black uppercase tracking-[0.2em] shadow-sm">
                            {{ $task->prioritas }}
                        </span>
                    </div>

                    {{-- Badge DISHUB --}}
                    <div class="absolute top-6 right-6">
                        <span class="px-5 py-2 bg-indigo-600 text-white rounded-full text-[10px] font-black tracking-[0.2em] uppercase shadow-lg shadow-indigo-200">
                            {{ $task->source }}
                        </span>
                    </div>

                    {{-- Info ID Tiket --}}
                    <div class="absolute bottom-6 left-10 text-white z-10">
                        <p class="text-[9px] font-bold opacity-80 uppercase mb-1 tracking-widest">ID TIKET</p>
                        <h4 class="text-base font-black tracking-[0.1em]">{{ $task->ticket_code }}</h4>
                    </div>

                    <div class="absolute bottom-6 right-10 z-10">
                        <div class="bg-white/20 backdrop-blur-md border border-white/30 px-4 py-2 rounded-2xl">
                            <p class="text-[11px] font-black text-white">NO. {{ $index + 1 }}</p>
                        </div>
                    </div>

                    {{-- Gradient Overlay --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                </div>

                {{-- BAGIAN BAWAH: DETAIL --}}
                <div class="p-10">
                    <div class="flex items-center gap-2 mb-6">
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $task->tgl_laporan }}</span>
                        <div class="w-1 h-1 bg-slate-300 rounded-full"></div>
                        <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">MAINTENANCE</span>
                    </div>

                    {{-- Asset & Deskripsi --}}
                    <div class="flex items-start gap-5 mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center flex-shrink-0 border border-slate-100 group-hover:bg-indigo-50 transition-colors">
                            <i class="fas fa-tools text-slate-400 group-hover:text-indigo-500 text-sm"></i>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-[9px] font-black text-slate-400 uppercase mb-1.5 tracking-widest">Asset & Deskripsi</p>
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight leading-relaxed line-clamp-2">
                                {{-- Jika asset adalah object, sesuaikan pemanggilannya --}}
                                {{ is_object($task->asset) ? ($task->asset->nama_aset ?? 'N/A') : $task->asset }}
                            </h3>
                        </div>
                    </div>

                    {{-- Lokasi --}}
                    <div class="flex items-start gap-5 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center flex-shrink-0 border border-slate-100 group-hover:bg-rose-50 transition-colors">
                            <i class="fas fa-map-marker-alt text-slate-400 group-hover:text-rose-500 text-sm"></i>
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-[9px] font-black text-slate-400 uppercase mb-1.5 tracking-widest">Lokasi Perbaikan</p>
                            <p class="text-[10px] font-bold text-slate-500 leading-relaxed italic uppercase line-clamp-2">
                                {{ $task->lokasi }}
                            </p>
                        </div>
                    </div>

                    {{-- Info Petugas --}}
                    <div class="bg-slate-50 rounded-[1.5rem] p-5 border border-slate-100 mb-8 group-hover:bg-white group-hover:border-indigo-100 transition-all">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center text-white text-xs font-black shadow-lg border-4 border-white">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase mb-0.5 tracking-widest">Nama Petugas</p>
                                <p class="text-[11px] font-black text-slate-900 uppercase tracking-wider">{{ Auth::user()->name }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mb-10">
                        <div class="flex justify-between items-end mb-3">
                            <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">
                                Status: <span class="text-slate-900 ml-1">{{ strtoupper($task->status) }}</span>
                            </span>
                            <span class="text-[10px] font-black text-slate-900 uppercase tracking-widest">
                                {{ $task->progress_percent }}%
                            </span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden p-[2px]">
                            <div class="bg-indigo-600 h-full rounded-full transition-all duration-1000 shadow-[0_0_10px_rgba(79,70,229,0.4)]" 
                                 style="width: {{ $task->progress_percent }}%"></div>
                        </div>
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="grid grid-cols-2 gap-4">
                        <a href="{{ route('admin.maintenance.show', $task->id) }}" 
                           class="flex items-center justify-center px-6 py-4 bg-slate-100 hover:bg-slate-200 text-slate-900 rounded-[1.2rem] font-black text-[10px] uppercase tracking-widest transition-all">
                            <i class="fas fa-eye mr-2 text-[8px]"></i> Detail
                        </a>
                        
                        <a href="{{ route('admin.maintenance.show', $task->id) }}" 
                           class="flex items-center justify-center px-6 py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-[1.2rem] font-black text-[10px] uppercase tracking-widest transition-all shadow-xl shadow-indigo-100">
                            <i class="fas fa-edit mr-2 text-[8px]"></i> Update
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white border-4 border-dashed border-slate-100 rounded-[3rem] py-40 text-center">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-clipboard-check text-4xl text-slate-200"></i>
                </div>
                <h3 class="text-slate-900 font-black text-2xl uppercase italic tracking-tighter">Belum Ada Tugas Tersedia!</h3>
                <p class="text-slate-400 font-bold uppercase text-[10px] tracking-[0.4em] mt-2">Belum ada tugas baru yang ditugaskan kepada Anda.</p>
            </div>
        @endforelse
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap');
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endsection