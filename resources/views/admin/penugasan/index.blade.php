@extends('layouts.app')

@section('content')
<div class="p-6 bg-slate-50 min-h-screen font-['Inter'] text-slate-700">
    <div class="max-w-7xl mx-auto">
        
        {{-- 1. HEADER INFORMASI TIKET --}}
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 mb-6 flex flex-wrap justify-between items-center gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-2xl font-black text-slate-800 tracking-tight">#{{ $ticket->ticket_number ?? 'TK-'.$ticket->id }}</h1>
                    @php
                        $statusColor = [
                            'Menunggu' => 'bg-slate-100 text-slate-600',
                            'Ditugaskan' => 'bg-blue-100 text-blue-600',
                            'Dalam Proses' => 'bg-amber-100 text-amber-600',
                            'Selesai' => 'bg-emerald-100 text-emerald-600',
                            'Ditolak' => 'bg-rose-100 text-rose-600',
                        ];
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $statusColor[$ticket->status] ?? 'bg-slate-100' }}">
                        {{ $ticket->status }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-500 border border-rose-100 uppercase tracking-widest">
                        {{ $ticket->priority ?? 'High' }}
                    </span>
                </div>
                <p class="text-sm text-slate-400 font-medium italic">Dibuat pada: {{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y, H:i') }}</p>
            </div>

            <div class="flex gap-4 items-center">
                <div class="text-right">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">SLA / Deadline</p>
                    {{-- PERBAIKAN: Pengecekan aman agar tidak Error Undefined Property --}}
                    <p class="text-sm font-black {{ ($ticket->deadline && \Carbon\Carbon::now()->gt($ticket->deadline)) ? 'text-rose-500' : 'text-slate-700' }}">
                        {{ $ticket->deadline ? \Carbon\Carbon::parse($ticket->deadline)->format('d M Y') : 'Belum Atur' }}
                    </p>
                </div>
                <a href="{{ route('admin.tickets.index') }}" class="p-3 bg-slate-100 rounded-2xl hover:bg-slate-200 transition-all text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- KOLOM KIRI: EKSEKUSI --}}
            <div class="lg:col-span-2 space-y-6">
                
                {{-- 2. INFORMASI PENUGASAN --}}
                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="font-black text-slate-800 uppercase text-xs tracking-[0.2em]">Informasi Penugasan</h3>
                        @if($ticket->assigned_to)
                            <button onclick="document.getElementById('modalAssign').classList.remove('hidden')" class="text-indigo-600 font-bold text-xs hover:underline">Ubah Petugas</button>
                        @endif
                    </div>

                    @if(!$ticket->assigned_to)
                        <div class="text-center py-6">
                            <button onclick="document.getElementById('modalAssign').classList.remove('hidden')" class="px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all">
                                ➕ Tugaskan Petugas
                            </button>
                        </div>
                    @else
                        <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 font-bold text-lg">
                                {{ substr($ticket->petugas_name ?? 'P', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-sm font-black text-slate-800">{{ $ticket->petugas_name ?? 'Nama Petugas' }}</p>
                                <p class="text-[11px] text-slate-400 font-medium">Ditugaskan pada: {{ $ticket->assigned_at ? \Carbon\Carbon::parse($ticket->assigned_at)->format('d M Y') : '-' }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- 4. UPDATE PROGRES --}}
                @if($ticket->assigned_to && $ticket->status != 'Selesai')
                <div class="bg-indigo-900 rounded-[2rem] p-8 text-white shadow-xl shadow-indigo-200">
                    <h3 class="font-black uppercase text-xs tracking-[0.2em] mb-6 opacity-80">Update Progres Lapangan</h3>
                    <form action="{{ route('admin.penugasan.update', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="text-[10px] font-bold uppercase opacity-60 mb-2 block">Ubah Status</label>
                                <select name="status_to" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-sm font-bold text-white outline-none focus:ring-2 focus:ring-white/50">
                                    <option class="text-slate-800" value="Dalam Proses">Dalam Proses</option>
                                    <option class="text-slate-800" value="Selesai">Selesai / Rampung</option>
                                    <option class="text-slate-800" value="Ditolak">Dibatalkan / Ditolak</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[10px] font-bold uppercase opacity-60 mb-2 block">Foto Bukti (Opsional)</label>
                                <input type="file" name="attachment" class="text-xs">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="text-[10px] font-bold uppercase opacity-60 mb-2 block">Catatan Teknis Pekerjaan</label>
                            <textarea name="note" rows="3" required class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-sm font-medium text-white outline-none focus:ring-2 focus:ring-white/50 placeholder:text-white/30" placeholder="Ceritakan hasil perbaikan..."></textarea>
                        </div>
                        <button type="submit" class="w-full py-4 bg-white text-indigo-900 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-50 transition-all shadow-xl">
                            Simpan & Update Progres
                        </button>
                    </form>
                </div>
                @endif

                {{-- 3. TIMELINE TINDAK LANJUT --}}
                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-slate-200">
                    <h3 class="font-black text-slate-800 uppercase text-xs tracking-[0.2em] mb-8">Timeline Tindak Lanjut</h3>
                    
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-slate-100"></div>
                        
                        <div class="space-y-8">
                            @forelse($logs as $log)
                            <div class="relative pl-12">
                                <div class="absolute left-2.5 top-1 w-3.5 h-3.5 bg-indigo-500 rounded-full border-4 border-white shadow-sm z-10"></div>
                                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-[10px] font-black uppercase text-indigo-600 tracking-tighter">{{ $log->action ?? 'LOG' }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium italic">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700 mb-1">{{ $log->note }}</p>
                                    <p class="text-[11px] text-slate-400">Oleh: <span class="font-bold text-slate-500">{{ $log->user_name }}</span></p>
                                    
                                    @if(isset($log->photo_evidence) && $log->photo_evidence)
                                        <div class="mt-3">
                                            <a href="{{ asset('storage/'.$log->photo_evidence) }}" target="_blank" class="inline-block p-1 bg-white border border-slate-200 rounded-lg shadow-sm">
                                                <img src="{{ asset('storage/'.$log->photo_evidence) }}" class="w-20 h-20 object-cover rounded-md">
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @empty
                            <p class="text-center text-slate-400 text-xs font-bold uppercase tracking-widest py-4">Belum ada aktivitas</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- 5. SIDEBAR: INFORMASI ASET --}}
            <div class="space-y-6">
                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-200 overflow-hidden">
                    <h3 class="font-black text-slate-800 uppercase text-xs tracking-[0.2em] mb-4">Informasi Aset</h3>
                    
                    <div class="rounded-2xl overflow-hidden mb-4 border border-slate-100">
                        <img src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=400&auto=format&fit=crop" class="w-full h-32 object-cover">
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Nama Aset</p>
                            <p class="text-sm font-black text-slate-800">{{ $ticket->asset_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Lokasi</p>
                            <p class="text-xs font-medium text-slate-500 leading-relaxed">{{ $ticket->location ?? 'Lokasi tidak tersedia' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 p-3 bg-slate-50 rounded-xl">
                            <div>
                                <p class="text-[8px] font-bold text-slate-400 uppercase">Latitude</p>
                                <p class="text-[10px] font-mono font-bold">{{ $ticket->asset_lat ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[8px] font-bold text-slate-400 uppercase">Longitude</p>
                                <p class="text-[10px] font-mono font-bold">{{ $ticket->asset_lng ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Deskripsi Laporan Awal --}}
                <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-slate-200">
                    <h3 class="font-black text-slate-800 uppercase text-xs tracking-[0.2em] mb-4">Keluhan Awal</h3>
                    <p class="text-sm text-slate-600 leading-relaxed bg-amber-50 p-4 rounded-xl border border-amber-100">
                        "{{ $ticket->description }}"
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TUGASKAN (Sederhana) --}}
<div id="modalAssign" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white rounded-[2rem] p-8 w-full max-w-md shadow-2xl">
        <h2 class="text-xl font-black text-slate-800 mb-2 uppercase tracking-tighter italic">Pilih Petugas</h2>
        <p class="text-xs text-slate-400 mb-6 font-medium">Tentukan petugas lapangan dan batas waktu penyelesaian.</p>
        
        <form action="{{ route('admin.penugasan.assign', $ticket->id) }}" method="POST">
            @csrf
            <div class="space-y-4 mb-8">
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block tracking-widest">Pilih Petugas Lapangan</label>
                    <select name="assigned_to" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 outline-none focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        <option value="">-- Pilih Petugas --</option>
                        @foreach($petugasList as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-[10px] font-black text-slate-400 uppercase mb-2 block tracking-widest">Batas Waktu (Deadline)</label>
                    <input type="date" name="deadline" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm font-bold text-slate-700 outline-none">
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('modalAssign').classList.add('hidden')" class="flex-1 py-4 bg-slate-100 text-slate-500 rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-slate-200 transition-all">Batal</button>
                <button type="submit" class="flex-1 py-4 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-200 transition-all">Simpan Tugas</button>
            </div>
        </form>
    </div>
</div>
@endsection