@extends('layouts.app')

@section('content')
<div class="p-8 bg-slate-50 min-h-screen text-left">
    <div class="mb-10 text-left">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase">RIWAYAT TUGAS</h1>
        <p class="text-slate-500 font-medium tracking-tight">Aset yang telah berhasil diperbaiki.</p>
    </div>

    <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Info Aset</th>
                        <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Lokasi</th>
                        <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tanggal Selesai</th>
                        <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                        <th class="px-8 py-6 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($tasks as $task)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                @if($task->asset && $task->asset->foto)
                                    <img src="{{ asset('storage/' . $task->asset->foto) }}" class="w-12 h-12 rounded-xl object-cover shadow-sm">
                                @elseif(isset($task->foto) && $task->foto)
                                     <img src="{{ asset('storage/' . $task->foto) }}" class="w-12 h-12 rounded-xl object-cover shadow-sm">
                                @else
                                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center text-xl">🏗️</div>
                                @endif
                                <div>
                                    <div class="font-black text-slate-900 uppercase text-sm leading-none mb-1">{{ $task->asset->nama ?? $task->jenis_aset }}</div>
                                    <div class="text-[10px] text-blue-500 font-black uppercase tracking-widest">{{ $task->ticket_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-xs font-bold text-slate-600 uppercase line-clamp-1 max-w-[200px]">
                                {{ $task->location_address ?? 'N/A' }}
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <div class="text-xs font-black text-slate-900 uppercase">
                                {{ $task->finished_at ? $task->finished_at->format('d M Y') : $task->updated_at->format('d M Y') }}
                            </div>
                            <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $task->finished_at ? $task->finished_at->format('H:i') : '' }} WIB</div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="inline-flex items-center px-4 py-1.5 bg-emerald-100 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full me-2"></span>
                                SELESAI
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            <a href="{{ route('admin.maintenance.show', $task->id) }}" class="inline-flex items-center justify-center px-4 py-2 bg-slate-900 hover:bg-blue-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest transition-all">
                                Lihat Laporan
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-32 text-center">
                            <p class="text-slate-400 font-black uppercase text-xs tracking-[0.3em]">Belum ada riwayat tugas selesai.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection