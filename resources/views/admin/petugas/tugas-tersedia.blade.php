@extends('layouts.app')

@section('content')
<div class="p-8 bg-[#F4F7FA] min-h-screen font-['Inter']">
    {{-- HEADER & FILTER --}}
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black tracking-tighter">
                <span class="text-[#1E293B]">Tugas</span>
                <span class="text-[#2563EB]">Tersedia</span>
            </h1>
            <p class="text-slate-500 font-medium mt-1 text-sm tracking-tight">
                Yth. {{ Auth::user()->name }}, mohon untuk melaksanakan tugas ini dengan dedikasi dan profesionalisme tinggi.
            </p>
        </div>
        <form action="{{ url()->current() }}" method="GET" class="flex flex-nowrap items-center lg:justify-end gap-2 flex-grow lg:max-w-4xl">
            <div class="relative flex-shrink-0">
                <select name="prioritas" class="appearance-none pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-2xl text-[10px] font-black tracking-widest text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2563EB]/20 shadow-sm cursor-pointer transition-all">
                    <option value="" class="text-slate-900">Prioritas: Semua</option>
                    <option value="urgent" {{ request('prioritas') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('prioritas') == 'high' ? 'selected' : '' }}>Tinggi</option>
                    <option value="normal" {{ request('prioritas') == 'normal' ? 'selected' : '' }}>Normal</option>
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-900 text-[8px] pointer-events-none"></i>
            </div>
            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-2xl px-3 shadow-sm flex-shrink-0">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="py-3 bg-transparent text-[10px] font-black text-slate-900 focus:outline-none cursor-pointer">
                <span class="text-slate-900 font-black text-[8px]">S/D</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="py-3 bg-transparent text-[10px] font-black text-slate-900 focus:outline-none cursor-pointer">
            </div>
            <button type="submit" class="bg-[#1E293B] text-white px-6 py-3 rounded-2xl text-[10px] font-black tracking-widest hover:bg-[#2563EB] transition-all shadow-lg shadow-slate-200 flex items-center flex-shrink-0">
                <i class="fas fa-search mr-2"></i> Filter
            </button>
            @if(request()->filled('prioritas') || request()->filled('start_date') || request()->filled('end_date'))
                <a href="{{ url()->current() }}" class="text-[10px] font-black text-rose-500 tracking-widest hover:underline ml-1 flex-shrink-0">Reset</a>
            @endif
        </form>
    </div>

    {{-- GRID TIKET --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($tasks as $index => $task)
            <div class="bg-white rounded-[2.5rem] overflow-hidden border border-slate-200 shadow-sm hover:shadow-2xl transition-all duration-500 group relative flex flex-col">
                
                {{-- BAGIAN ATAS --}}
                <div class="relative h-64 overflow-hidden">
                    <img src="{{ $task->foto }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    <div class="absolute top-6 left-6">
                        <span class="px-5 py-2 bg-white/90 backdrop-blur-md text-[#2563EB] rounded-full text-[10px] font-black tracking-[0.2em] shadow-sm uppercase">
                            {{ $task->prioritas }}
                        </span>
                    </div>
                    <div class="absolute top-6 right-6">
                        <span class="px-5 py-2 bg-[#2563EB] text-white rounded-full text-[10px] font-black tracking-[0.2em] shadow-lg shadow-blue-200 uppercase">
                            {{ $task->source }}
                        </span>
                    </div>
                    <div class="absolute bottom-6 left-10 text-white z-10">
                        <p class="text-[9px] font-bold opacity-80 mb-1 tracking-widest">ID TIKET</p>
                        <h4 class="text-base font-black tracking-[0.1em]">{{ $task->ticket_code }}</h4>
                    </div>
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>
                </div>

                {{-- BAGIAN BAWAH (Menggunakan Flex-col & Flex-grow agar tombol tetap sejajar) --}}
                <div class="p-10 flex flex-col flex-grow">
                    
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-[9px] font-black bg-slate-100 text-slate-500 px-3 py-1 rounded-full tracking-widest uppercase">{{ $task->kategori_aset }}</span>
                        <span class="text-[10px] font-black text-slate-400 tracking-widest">{{ $task->tgl_laporan }}</span>
                    </div>

                    <div class="mb-8">
                        <p class="text-[9px] font-black text-slate-400 mb-2 tracking-widest uppercase">Nama Aset</p>
                        <h3 class="text-base font-black text-slate-900 tracking-tight leading-relaxed">
                            {{ $task->nama_aset }}
                        </h3>
                    </div>

                    <div class="mb-8 flex-grow">
                        <p class="text-[9px] font-black text-slate-400 mb-2 tracking-widest uppercase">Lokasi Perbaikan</p>
                        <p class="text-[12px] font-bold text-slate-600 leading-relaxed italic">{{ $task->lokasi }}</p>
                    </div>

                    <div class="mb-8 border-t border-slate-100 pt-6">
                        <p class="text-[9px] font-black text-slate-400 mb-3 tracking-widest uppercase">Nama Petugas</p>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#1E293B] flex items-center justify-center text-white text-[10px] font-black">{{ substr(Auth::user()->name, 0, 1) }}</div>
                            <span class="text-[11px] font-black text-slate-900 tracking-wider">{{ Auth::user()->name }}</span>
                        </div>
                    </div>

                    <div class="mb-10">
                        <div class="flex justify-between items-end mb-3">
                            <span class="text-[10px] font-black text-[#2563EB] tracking-widest">Status: <span class="text-slate-900 ml-1 uppercase">{{ $task->status }}</span></span>
                            <span class="text-[10px] font-black text-slate-900 tracking-widest">{{ $task->progress_percent }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden p-[2px]">
                            <div class="bg-[#2563EB] h-full rounded-full transition-all duration-1000" style="width: {{ $task->progress_percent }}%"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-auto">
                        <a href="{{ route('admin.maintenance.show', $task->id) }}" class="flex items-center justify-center px-6 py-4 bg-slate-100 hover:bg-slate-200 text-slate-900 rounded-[1.2rem] font-black text-[10px] tracking-widest transition-all">Detail</a>
                        <a href="{{ route('admin.maintenance.show', $task->id) }}" class="flex items-center justify-center px-6 py-4 bg-[#2563EB] hover:bg-[#1E293B] text-white rounded-[1.2rem] font-black text-[10px] tracking-widest transition-all shadow-xl shadow-blue-100">Update</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white border-4 border-dashed border-slate-100 rounded-[3rem] py-40 text-center">
                <h3 class="text-slate-900 font-black text-2xl italic tracking-tighter">Belum Ada Tugas Tersedia!</h3>
            </div>
        @endforelse
    </div>
</div>
@endsection