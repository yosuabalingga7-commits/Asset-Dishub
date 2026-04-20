@extends('layouts.app')

@section('content')
<div class="p-8 bg-slate-50 min-h-screen">
    <div class="mb-10">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">TUGAS TERSEDIA</h1>
        <p class="text-slate-500 font-medium">Daftar kerusakan aset yang perlu segera ditangani oleh seksi Anda.</p>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @forelse($tasks as $task)
            <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all">
                <div class="flex flex-col md:flex-row justify-between gap-6">
                    <div class="flex gap-5">
                        <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center text-white text-2xl shadow-lg shadow-blue-200">
                            🛠️
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">#{{ $task->kode_tiket }}</span>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $task->status == 'Kritis' ? 'bg-red-100 text-red-600' : 'bg-orange-100 text-orange-600' }}">
                                    {{ $task->status }}
                                </span>
                            </div>
                            <h3 class="text-xl font-black text-slate-900 mb-1">{{ $task->nama_aset }}</h3>
                            <p class="text-slate-500 text-sm font-medium mb-4 italic">"{{ $task->deskripsi_laporan }}"</p>
                            
                            <div class="flex flex-wrap gap-4 text-xs font-bold text-slate-600 uppercase tracking-tighter">
                                <span class="flex items-center gap-1.5"><span class="text-lg">📍</span> {{ $task->lokasi }}</span>
                                <span class="flex items-center gap-1.5"><span class="text-lg">📅</span> {{ $task->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center shrink-0">
                        <button onclick="openModal('{{ $task->id }}')" class="w-full md:w-auto px-6 py-3 bg-slate-900 hover:bg-blue-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest transition-all shadow-xl shadow-slate-200">
                            Update Progres
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border-2 border-dashed border-slate-200 rounded-[3rem] py-20 text-center">
                <h3 class="text-slate-900 font-black text-xl uppercase italic">Tidak Tersedia Tugas.</h3>
                <p class="text-slate-400 font-medium uppercase text-xs tracking-[0.3em]">Tidak ada tugas menunggu saat ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection