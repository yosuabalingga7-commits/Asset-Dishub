@extends('layouts.app')

@section('content')
<div class="p-8 bg-slate-50 min-h-screen text-left font-['Inter']">
    <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black tracking-tighter ">
                <span class="text-[#1E293B]">Riwayat</span>
                <span class="text-[#2563EB]">Tugas</span>
            </h1>
            <p class="text-slate-500 font-medium tracking-tight mt-1 text-sm">Aset yang telah berhasil diperbaiki.</p>
        </div>

        <form action="{{ url()->current() }}" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <select name="prioritas" class="appearance-none pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-2xl text-[10px] font-black tracking-widest text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2563EB]/20 shadow-sm cursor-pointer transition-all">
                    <option value="" class="text-slate-900">Prioritas: Semua</option>
                    <option value="urgent" {{ request('prioritas') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('prioritas') == 'high' ? 'selected' : '' }}>Tinggi</option>
                    <option value="normal" {{ request('prioritas') == 'normal' ? 'selected' : '' }}>Normal</option>
                </select>
                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-[8px] pointer-events-none"></i>
            </div>

            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-2xl px-3 shadow-sm">
                <input type="date" name="start_date" value="{{ request('start_date') }}" 
                       class="py-3 bg-transparent text-[10px] font-black text-slate-900 focus:outline-none cursor-pointer">
                <span class="text-slate-400 font-black text-[8px]">S/D</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" 
                       class="py-3 bg-transparent text-[10px] font-black text-slate-900 focus:outline-none cursor-pointer">
            </div>

            <button type="submit" class="bg-[#1E293B] text-white px-6 py-3 rounded-2xl text-[10px] font-black  tracking-widest hover:bg-[#2563EB] transition-all shadow-lg shadow-slate-100">
                <i class="fas fa-search mr-2"></i> Filter
            </button>

            @if(request()->filled('prioritas') || request()->filled('start_date') || request()->filled('end_date'))
                <a href="{{ url()->current() }}" class="text-[10px] font-black text-rose-500 tracking-widest hover:underline ml-2">Reset</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#1E293B] border-b border-slate-800">
                        <th class="px-8 py-6 text-[10px] font-black text-white tracking-[0.2em]">Info Aset</th>
                        <th class="px-8 py-6 text-[10px] font-black text-white tracking-[0.2em]">Prioritas</th>
                        <th class="px-8 py-6 text-[10px] font-black text-white tracking-[0.2em]">Lokasi</th>
                        <th class="px-8 py-6 text-[10px] font-black text-white tracking-[0.2em]">Tanggal Selesai</th>
                        <th class="px-8 py-6 text-[10px] font-black text-white tracking-[0.2em] text-center">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($tasks as $task)
                    <tr class="hover:bg-slate-50/50 transition-colors group text-left">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                @if($task->asset && $task->asset->foto)
                                    <img src="{{ asset('storage/' . $task->asset->foto) }}" class="w-12 h-12 rounded-xl object-cover shadow-sm">
                                @elseif(isset($task->report) && $task->report->foto)
                                     <img src="{{ asset('storage/' . $task->report->foto) }}" class="w-12 h-12 rounded-xl object-cover shadow-sm">
                                @else
                                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-xl"></div>
                                @endif
                                <div>
                                    <div class="font-black text-slate-900 text-sm leading-none mb-1.5">{{ $task->asset->nama ?? $task->jenis_aset ?? 'Aset' }}</div>
                                    <div class="text-[10px] text-[#2563EB] font-black tracking-widest">{{ $task->ticket_code ?? 'MNT-'.$task->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="text-[9px] font-black px-3 py-1 bg-slate-100 rounded-full text-slate-600 tracking-widest">
                                {{ $task->priority ?? 'NORMAL' }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-left">
                            <div class="text-xs font-bold text-slate-500 leading-relaxed italic min-w-[200px] break-words">
                                {{ $task->location_address ?? ($task->report->alamat ?? 'N/A') }}
                            </div>
                        </td>
                        <td class="px-8 py-6 text-left">
                            <div class="text-xs font-black text-slate-900 mb-0.5">
                                {{ $task->finished_at ? \Carbon\Carbon::parse($task->finished_at)->format('d M Y') : \Carbon\Carbon::parse($task->updated_at)->format('d M Y') }}
                            </div>
                            <div class="text-[10px] text-slate-400 font-bold tracking-widest">
                                {{ $task->finished_at ? \Carbon\Carbon::parse($task->finished_at)->format('H:i') : '' }} WIB
                            </div>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <a href="{{ route('admin.maintenance.show', $task->id) }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-[#1E293B] hover:bg-[#2563EB] text-white rounded-xl font-black text-[10px] tracking-widest transition-all shadow-lg shadow-slate-200 whitespace-nowrap">
                                <i class="fas fa-file-alt mr-2 text-[8px]"></i> Lihat Hasil
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-32 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-folder-open text-slate-200 text-2xl"></i>
                            </div>
                            <p class="text-slate-400 font-black text-xs tracking-[0.3em] uppercase">Data tidak ditemukan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-8 py-6 border-t border-slate-100">
            {{ $tasks->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap');
</style>
@endsection