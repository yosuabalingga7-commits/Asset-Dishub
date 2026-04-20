@extends('layouts.app')

@section('content')
<div class="p-8 bg-slate-50 min-h-screen">
    <div class="mb-10">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">RIWAYAT TUGAS</h1>
        <p class="text-slate-500 font-medium">Aset yang telah berhasil diperbaiki dan dinyatakan berfungsi baik.</p>
    </div>

    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-100">
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Info Aset</th>
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Tanggal Selesai</th>
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Status Akhir</th>
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Opsi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($tasks as $task)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-6 py-6">
                        <div class="font-black text-slate-900">{{ $task->nama_aset }}</div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tight">{{ $task->kode_tiket }}</div>
                    </td>
                    <td class="px-6 py-6 text-sm font-bold text-slate-600">
                        {{ $task->updated_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-6">
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-600 rounded-full text-[10px] font-black uppercase tracking-widest">
                            Selesai (Baik)
                        </span>
                    </td>
                    <td class="px-6 py-6">
                        <button class="text-blue-600 hover:text-blue-800 font-black text-xs uppercase tracking-tighter">Detail Laporan</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-20 text-center">
                        <p class="text-slate-400 font-bold uppercase text-xs tracking-widest">Belum ada riwayat tugas selesai.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection