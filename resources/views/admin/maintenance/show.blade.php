@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F8FAFC] font-['Inter'] pb-12 text-left">
    {{-- Header --}}
    <div class="bg-white border-b border-slate-200 px-8 py-6 mb-8 shadow-sm">
        <div class="max-w-5xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <nav class="flex mb-3 text-[10px] font-black  tracking-[0.2em] text-slate-400">
                    <a href="{{ url()->previous() }}" class="hover:text-[#0B2A4A] transition-colors">Kembali</a>
                    <span class="mx-3 text-slate-300">/</span>
                    <span class="text-[#0B2A4A]">Detail Penugasan</span>
                </nav>
                <h1 class="text-2xl font-black text-[#0B2A4A]  tracking-tighter">
                    Tiket #{{ $ticket->ticket_code ?? 'MNT-' . str_pad($ticket->id, 5, '0', STR_PAD_LEFT) }}
                </h1>
            </div>
            <div class="flex items-center gap-4">
                {{-- Status Badge Dinamis --}}
                @php
                    $statusValue = strtolower($ticket->status);
                    $statusColor = match($statusValue) {
                        'pending' => 'bg-amber-500',
                        'proses perbaikan', 'process', 'proses' => 'bg-blue-500',
                        'baik', 'finished', 'selesai' => 'bg-emerald-600',
                        'rusak' => 'bg-rose-500',
                        'kritis' => 'bg-rose-700',
                        default => 'bg-slate-500',
                    };
                @endphp
                <span class="{{ $statusColor }} text-white text-[10px] font-black tracking-widest px-4 py-2 rounded-full shadow-lg">
                    Status: {{ strtoupper($ticket->status) }}
                </span>
                <a href="{{ url()->previous() }}" class="text-[10px] font-black  tracking-widest text-slate-400 hover:text-[#0B2A4A] transition-colors">
                    ← Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- Panel Kiri: Foto & Lokasi --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-[#0B2A4A] rounded-[2.5rem] p-8 text-white shadow-2xl relative overflow-hidden">
                    <p class="text-[10px] font-black text-white/40  tracking-[0.2em] mb-6">Dokumentasi Laporan</p>
                    
                    <div class="space-y-4">
                        <div class="relative group">
                            @php
                                $fotoLaporan = ($ticket->report && $ticket->report->foto) 
                                               ? asset('storage/' . $ticket->report->foto) 
                                               : ($ticket->foto_awal ? asset('storage/' . $ticket->foto_awal) : null);
                            @endphp

                            @if($fotoLaporan)
                                <img src="{{ $fotoLaporan }}" class="w-full h-56 object-cover rounded-2xl border border-white/10 mb-4 shadow-lg transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="w-full h-32 bg-white/5 rounded-2xl border border-dashed border-white/20 flex items-center justify-center mb-4">
                                    <span class="text-[10px] text-white/30 font-bold text-center px-4">Foto Tidak Tersedia</span>
                                </div>
                            @endif
                        </div>

                        <div class="p-4 bg-white/5 rounded-2xl border border-white/10">
                            <p class="text-[9px] font-black  text-blue-400 mb-1">Lokasi Laporan</p>
                            <p class="text-[10px] font-bold text-white mb-2 leading-relaxed">
                                {{ $ticket->report->location_address ?? ($ticket->location_address ?? 'Alamat tidak spesifik') }}
                            </p>
                            
                            @php
                                $lat = $ticket->report->latitude ?? $ticket->latitude;
                                $lng = $ticket->report->longitude ?? $ticket->longitude;
                            @endphp

                            @if($lat && $lng)
                            <a href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}" target="_blank" 
                               class="text-[9px] bg-blue-500 hover:bg-blue-600 px-3 py-1 rounded-md text-white font-black  transition-all inline-block">
                                <i class="fas fa-map-marker-alt mr-1"></i> Lihat di Google Maps
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 p-4 rounded-2xl text-[11px] font-bold  tracking-tight">
                    <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                </div>
                @endif
            </div>

            {{-- Panel Kanan: Detail & Form Aksi --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white border border-slate-200 rounded-[2.5rem] p-10 shadow-sm">
                    <div class="flex items-center justify-between mb-8 pb-6 border-b border-slate-100">
                        <div>
                            <p class="text-[10px] font-black text-slate-400  tracking-widest mb-1">
                                {{ $ticket->user->jabatan ?? 'Seksi Terkait' }}
                            </p>
                            <p class="text-sm font-black text-[#0B2A4A] italic">
                                👤 {{ $ticket->user->name ?? 'Belum Ditentukan' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400  tracking-widest mb-1">Target Selesai</p>
                            <p class="text-sm font-black text-rose-500 ">
                                <i class="far fa-calendar-alt mr-1"></i> {{ $ticket->deadline ? \Carbon\Carbon::parse($ticket->deadline)->format('d M Y') : '-' }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div>
                            <p class="text-[10px] font-black text-slate-400 tracking-widest mb-2">Kategori Aset</p>
                            <p class="text-xs font-bold text-[#0B2A4A] bg-slate-50 p-3 rounded-xl border border-slate-100">
                                @if($ticket->report && $ticket->report->category)
                                    {{ strtoupper($ticket->report->category) }}
                                @else
                                    {{ strtoupper(is_object($ticket->category) ? $ticket->category->name : ($ticket->category ?? 'UMUM')) }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-slate-400  tracking-widest mb-2">Jenis Spesifik</p>
                            <p class="text-xs font-bold text-[#0B2A4A] bg-slate-50 p-3 rounded-xl border border-slate-100">
                                {{ strtoupper($ticket->report->jenis_aset ?? ($ticket->jenis_aset ?? '-')) }}
                            </p>
                        </div>
                    </div>

                    <div class="mb-8">
                        <p class="text-[10px] font-black text-slate-400  tracking-widest mb-2">Prioritas Kerja</p>
                        <div class="flex items-center gap-2">
                            @php
                                $prioLabel = strtolower($ticket->priority ?? 'normal');
                                $prioStyle = in_array($prioLabel, ['urgent', 'tinggi', 'kritis']) 
                                             ? 'text-red-600 bg-red-50 border-red-100' 
                                             : 'text-indigo-600 bg-indigo-50 border-indigo-100';
                            @endphp
                            <span class="px-4 py-1.5 rounded-lg border {{ $prioStyle }} text-[10px] font-black  tracking-tighter">
                                {{ strtoupper($prioLabel) }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-10">
                        <p class="text-[10px] font-black text-slate-400  tracking-widest mb-2">Isi Laporan / Keluhan Masyarakat</p>
                        <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 min-h-[100px]">
                            <p class="text-xs text-slate-600 leading-relaxed italic">
                                "{{ $ticket->report->description ?? ($ticket->report->isi_laporan ?? ($ticket->description ?? 'Tidak ada detail keluhan.')) }}"
                            </p>
                        </div>
                    </div>

                    <!-- {{-- TOMBOL SHARE WA (Hanya muncul jika belum selesai) --}}
                    @if(!in_array(strtolower($ticket->status), ['finished', 'selesai', 'baik']))
                    <div class="mb-10 pt-6 border-t border-dashed border-slate-200">
                        @php
                            $waMessage = "📢 *INSTRUKSI PERBAIKAN ASSET*\n\n"
                                     . "🆔 *Tiket:* #" . ($ticket->ticket_code ?? 'MNT-' . $ticket->id) . "\n"
                                     . "🏗️ *Aset:* " . ($ticket->report->jenis_aset ?? 'Aset Dishub') . "\n"
                                     . "📍 *Alamat:* " . ($ticket->report->location_address ?? 'Cek Map') . "\n"
                                     . "⚠️ *Laporan:* " . ($ticket->report->description ?? 'Perbaikan segera') . "\n\n"
                                     . "📍 *Link Lokasi (Google Maps):* \n"
                                     . "https://www.google.com/maps?q=" . $lat . "," . $lng . "\n\n"
                                     . "Mohon segera ditindaklanjuti. Terimakasih.";
                                     
                            $waEncoded = "https://api.whatsapp.com/send?text=" . urlencode($waMessage);
                        @endphp
                        
                        <a href="{{ $waEncoded }}" target="_blank" 
                           class="w-full bg-[#25D366] hover:bg-[#128C7E] text-white font-black text-[11px] py-4 rounded-xl shadow-lg transition-all tracking-widest flex items-center justify-center gap-3">
                            <i class="fab fa-whatsapp text-lg"></i> Teruskan ke Petugas Lapangan
                        </a>
                    </div> -->

                    {{-- Form Penyelesaian (Support Petugas/Admin) --}}
                    <div class="pt-8 border-t border-slate-100">
                        <p class="text-[11px] font-black text-[#0B2A4A]  tracking-widest mb-6">Laporan Penyelesaian Seksi</p>
                        @php
                            $routeAction = Auth::user()->hasRole('admin') 
                                           ? route('admin.maintenance.updateStatus', $ticket->id) 
                                           : route('petugas.tugas.updateStatus', $ticket->id);
                        @endphp
                        <form action="{{ $routeAction }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="Baik">

                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400  mb-2">Upload Foto Hasil (Sesudah)</label>
                                    <input type="file" name="foto_perbaikan" required 
                                        class="w-full text-xs text-slate-900 font-medium file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file: file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black text-slate-400  mb-2">Catatan Teknis / Material Diganti</label>
                                    {{-- PERBAIKAN: Tambah text-slate-900 dan bg-white agar teks terlihat --}}
                                    <textarea name="completion_notes" rows="3" required placeholder="Contoh: Lampu diganti baru..."
                                        class="w-full bg-white border border-slate-200 rounded-xl p-4 text-xs text-slate-900 font-medium focus:ring-2 focus:ring-blue-500 transition-all outline-none"></textarea>
                                </div>
                                <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black  text-[11px] py-4 rounded-xl shadow-lg transition-all tracking-widest">
                                    <i class="fas fa-check-circle mr-2"></i> Konfirmasi Selesai
                                </button>
                            </div>
                        </form>
                    </div>
                    @else
                    {{-- Bukti Selesai --}}
                    <div class="pt-8 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-6">
                            <p class="text-[11px] font-black text-[#0B2A4A]  tracking-widest flex items-center gap-2">
                                <span class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center text-white text-[10px]">
                                    <i class="fas fa-check"></i>
                                </span>
                                Bukti Hasil Pengerjaan
                            </p>
                        </div>
                        <div class="space-y-4">
                            @if($ticket->foto_perbaikan)
                                <img src="{{ asset('storage/' . $ticket->foto_perbaikan) }}" class="w-full max-h-96 object-cover rounded-2xl border border-slate-200 shadow-md">
                            @endif
                            <div class="bg-emerald-50 p-6 rounded-2xl border border-emerald-100">
                                <p class="text-[9px] font-black text-emerald-600  mb-2">Catatan Teknisi:</p>
                                <p class="text-xs text-slate-700 italic">"{{ $ticket->completion_notes ?? 'Selesai diperbaiki.' }}"</p>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .font-black { font-weight: 900 !important; }
</style>
@endsection