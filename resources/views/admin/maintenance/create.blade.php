@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F8FAFC] font-['Inter'] pb-12 text-left">
    {{-- Header --}}
    <div class="bg-white border-b border-slate-200 px-8 py-6 mb-8 shadow-sm">
        <div class="max-w-5xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <nav class="flex mb-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">
                    <a href="{{ route('admin.maintenance.index') }}" class="hover:text-[#0B2A4A] transition-colors">Monitoring</a>
                    <span class="mx-3 text-slate-300">/</span>
                    <span class="text-[#0B2A4A]">{{ isset($maintenance) ? 'Edit Penugasan' : 'Buat Tiket Baru' }}</span>
                </nav>
                <h1 class="text-2xl font-black text-[#0B2A4A] uppercase tracking-tighter">
                    {{ isset($maintenance) ? 'Update Penugasan Seksi' : 'Registrasi Tiket Maintenance' }}
                </h1>
            </div>
            <a href="{{ url()->previous() }}" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-rose-500 transition-colors">
                ← Batal & Kembali
            </a>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-8">
        <form id="formMaintenance" action="{{ isset($maintenance) ? route('admin.maintenance.update', $maintenance->id) : route('admin.maintenance.store') }}" 
              method="POST" class="space-y-6">
            @csrf
            @if(isset($maintenance)) @method('PUT') @endif
            
            {{-- DATA HIDDEN --}}
            <input type="hidden" name="report_id" value="{{ $laporan ? $laporan->id : ($maintenance->report_id ?? '') }}">
            <input type="hidden" name="location_address" value="{{ $laporan->alamat ?? ($maintenance->location_address ?? '') }}">
            <input type="hidden" name="latitude" value="{{ $laporan->lat ?? ($maintenance->latitude ?? '') }}">
            <input type="hidden" name="longitude" value="{{ $laporan->lng ?? ($maintenance->longitude ?? '') }}">
            <input type="hidden" name="category_id" id="category_id_hidden" value="{{ old('category_id', $maintenance->category_id ?? '') }}">
            
            {{-- CRITICAL FIX --}}
            <input type="hidden" name="send_wa" id="input_send_wa" value="0">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Panel Kiri: Info Referensi Laporan --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-[#0B2A4A] rounded-[2.5rem] p-8 text-white shadow-2xl relative overflow-hidden">
                        <p class="text-[10px] font-black text-white/40 uppercase tracking-[0.2em] mb-4">Referensi Laporan</p>
                        
                        <div class="p-4 bg-amber-500 rounded-2xl border border-white/10 shadow-lg mb-6">
                            <p class="text-[9px] font-black uppercase text-white/80 mb-1">ID Tiket / No. Urut</p>
                            <p class="text-2xl font-black text-white">#{{ isset($maintenance) ? $maintenance->id : ($laporan->id ?? 'NEW') }}</p>
                        </div>
                        
                        @php $ref = $laporan ?? ($maintenance->report ?? null); @endphp
                        
                        @if($ref)
                            <div class="space-y-4">
                                <div class="relative group">
                                    <p class="text-[9px] font-black uppercase text-amber-500 mb-2">Foto Kondisi</p>
                                    @if($ref->foto)
                                        <img src="{{ asset('storage/' . $ref->foto) }}" class="w-full h-48 object-cover rounded-2xl border border-white/10 mb-4 shadow-lg">
                                    @else
                                        <div class="w-full h-32 bg-white/5 rounded-2xl border border-dashed border-white/20 flex items-center justify-center mb-4">
                                            <span class="text-[10px] text-white/30 uppercase font-bold">Tidak ada foto</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="p-4 bg-white/5 rounded-2xl border border-white/10">
                                    <p class="text-[9px] font-black uppercase text-blue-400 mb-1">Tiket & Judul</p>
                                    <p class="text-xs font-bold font-mono text-amber-400 mb-1">#{{ $ref->ticket_number ?? 'N/A' }}</p>
                                    <p class="text-xs font-bold italic leading-relaxed">"{{ $ref->judul_laporan ?? ($maintenance->jenis_aset ?? 'Laporan Tanpa Judul') }}"</p>
                                </div>

                                <div class="p-4 bg-white/5 rounded-2xl border border-white/10">
                                    <p class="text-[9px] font-black uppercase text-emerald-400 mb-1">Lokasi</p>
                                    <p class="text-[10px] font-bold text-white mb-2 leading-relaxed tracking-tight">{{ $ref->alamat ?? ($maintenance->location_address ?? 'Alamat tidak tersedia') }}</p>
                                </div>
                                
                                <div class="p-4 bg-rose-500/10 rounded-2xl border border-rose-500/20">
                                    <p class="text-[9px] font-black uppercase text-rose-400 mb-1">Keluhan Masyarakat</p>
                                    <p class="text-[10px] leading-relaxed text-white/70">{{ $ref->deskripsi_keluhan ?? ($maintenance->description ?? 'Tidak ada deskripsi keluhan') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Panel Kanan: Input Form --}}
                <div class="lg:col-span-2">
                    <div class="bg-white border border-slate-200 rounded-[2.5rem] p-10 shadow-sm space-y-8">
                        
                        <div class="pb-6 border-b border-slate-100">
                            <label class="text-[10px] font-black text-slate-400 uppercase mb-4 block tracking-widest italic">0. Status Kepemilikan Aset</label>
                            @php
                                $valKepemilikan = old('source_type', $maintenance->kepemilikan ?? ($laporan->kepemilikan ?? 'DISHUB'));
                                $isDishub = (strtoupper($valKepemilikan) === 'DISHUB');
                            @endphp
                            <input type="hidden" name="source_type" value="{{ $valKepemilikan }}">
                            <div class="flex items-center gap-4 bg-slate-50 p-5 rounded-2xl border border-slate-200 opacity-90">
                                <div class="w-12 h-12 {{ $isDishub ? 'bg-indigo-600' : 'bg-slate-700' }} rounded-xl flex items-center justify-center text-white shadow-lg">
                                    <i class="fas {{ $isDishub ? 'fa-shield-alt' : 'fa-globe-asia' }} text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-[11px] font-black text-[#0B2A4A] uppercase tracking-wider mb-0.5">{{ $isDishub ? 'DISHUB KBB' : 'UMUM / PIHAK 3' }}</p>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-tight italic">Status Terkunci</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-4 block tracking-widest italic">1. Petugas Pelaksana (Seksi)</label>
                                    <select name="user_id" id="seksi_select" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-[#0B2A4A] text-xs font-bold focus:ring-2 focus:ring-[#25D366] outline-none transition-all">
                                        <option value="">-- Pilih Orang Yang Ditugaskan --</option>
                                        @foreach($listSeksi as $petugas)
                                            <option value="{{ $petugas->id }}" 
                                                {{ (old('user_id', $maintenance->user_id ?? '') == $petugas->id) ? 'selected' : '' }}>
                                                👤 {{ strtoupper($petugas->name) }} | BIDANG: {{ strtoupper($petugas->seksi->nama_seksi ?? 'UMUM') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-4 block tracking-widest italic">2. Target Selesai (Deadline)</label>
                                    <input type="date" name="deadline" id="wa_deadline"
                                           value="{{ old('deadline', isset($maintenance->deadline) ? \Carbon\Carbon::parse($maintenance->deadline)->format('Y-m-d') : '') }}" required
                                           class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-[#0B2A4A] text-xs font-bold focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-4 block tracking-widest italic">3. Kategori Aset</label>
                                    <select name="category" id="kat_select" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-[#0B2A4A] text-xs font-bold focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                        <option value="">-- Pilih Kategori --</option>
                                        @php $activeKat = old('category', $maintenance->category ?? ($laporan->kategori_laporan ?? '')); @endphp
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->name }}" data-id="{{ $cat->id }}" {{ $activeKat == $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-slate-400 uppercase mb-4 block tracking-widest italic">4. Jenis Spesifik</label>
                                    <select name="jenis_aset" id="jenis_select" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-[#0B2A4A] text-xs font-bold focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                        <option value="">-- Pilih Kategori Dahulu --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase mb-4 block tracking-widest italic">5. Tingkat Prioritas</label>
                            <select name="priority" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-[#0B2A4A] text-xs font-bold focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                @foreach(['Urgent', 'Tinggi', 'Normal', 'Rendah'] as $prio)
                                    <option value="{{ $prio }}" {{ old('priority', $maintenance->priority ?? 'Normal') == $prio ? 'selected' : '' }}>{{ strtoupper($prio) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-400 uppercase mb-4 block tracking-widest italic">6. Instruksi Perbaikan</label>
                            <textarea name="description" id="wa_instruksi" rows="4" required 
                                class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-5 text-[#0B2A4A] text-xs font-medium outline-none focus:ring-2 focus:ring-indigo-500 transition-all resize-none"
                                placeholder="Tulis instruksi pengerjaan...">{{ old('description', $maintenance->description ?? ($laporan->deskripsi_keluhan ?? '')) }}</textarea>
                        </div>

                        {{-- TOMBOL ACTION --}}
                        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row gap-4">
                            <button type="submit" onclick="document.getElementById('input_send_wa').value='0'" class="flex-1 bg-slate-800 text-white py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-lg transition-all hover:bg-slate-700 active:scale-[0.98]">
                                <i class="fas fa-save mr-2"></i> Simpan Saja
                            </button>

                            <button type="button" id="btnKirimWa" onclick="kirimWhatsApp()" class="flex-1 bg-[#25D366] text-white py-5 rounded-2xl font-black text-xs uppercase tracking-[0.2em] shadow-lg transition-all hover:bg-[#128C7E] active:scale-[0.98] flex items-center justify-center gap-2">
                                <span id="btnText"><i class="fab fa-whatsapp text-lg"></i> Simpan & Kirim WA</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    const dbJenis = {
        'Penerangan Jalan Umum (PJU)': ['Tiang PJU Galvanis', 'Tiang PJU Dekoratif', 'Lampu LED', 'Lampu Solar Cell', 'Panel Box PJU', 'Kabel Udara PJU'],
        'Perlengkapan Jalan': ['Rambu Larangan', 'Rambu Peringatan', 'Rambu Petunjuk', 'Cermin Tikungan', 'Guardrail', 'Delineator'],
        'Fasilitas Lalu Lintas': ['APILL (Traffic Light)', 'Warning Light', 'Marka Jalan', 'Paku Jalan', 'Water Barrier'],
        'Pengendalian dan Pengawasan': ['CCTV Surveilans', 'CCTV E-TLE', 'VMS (Papan Digital)', 'ATCS Controller'],
        'Prasarana Transportasi': ['Halte Bus', 'Terminal', 'Jembatan Penyeberangan (JPO)']
    };

    const katSelect = document.getElementById('kat_select');
    const jenisSelect = document.getElementById('jenis_select');
    const categoryIdHidden = document.getElementById('category_id_hidden');

    katSelect.addEventListener('change', function() {
        const selectedKat = this.value;
        const selectedOption = this.options[this.selectedIndex];
        if(selectedOption && selectedOption.dataset.id) { categoryIdHidden.value = selectedOption.dataset.id; }

        const currentJenis = "{{ old('jenis_aset', $maintenance->jenis_aset ?? '') }}";
        jenisSelect.innerHTML = '<option value="">-- Pilih Jenis --</option>';
        if(!selectedKat) return;

        let foundKey = Object.keys(dbJenis).find(k => {
            return selectedKat.includes(k) || k.includes(selectedKat);
        });

        if(foundKey && dbJenis[foundKey]) {
            dbJenis[foundKey].forEach(jenis => {
                const opt = document.createElement('option');
                opt.value = jenis; opt.text = jenis;
                if(currentJenis === jenis) opt.selected = true;
                jenisSelect.add(opt);
            });
        }
    });

    function kirimWhatsApp() {
        const seksiSelect = document.getElementById('seksi_select');
        const btnKirimWa = document.getElementById('btnKirimWa');
        const btnText = document.getElementById('btnText');
        const instruksi = document.getElementById('wa_instruksi');
        const inputSendWa = document.getElementById('input_send_wa');
        
        if (!seksiSelect.value) {
            alert('Pilih Petugas dulu!');
            seksiSelect.focus();
            return;
        }

        if (!instruksi.value.trim()) {
            alert('Tolong tulis instruksinya dulu!');
            instruksi.focus();
            return;
        }

        inputSendWa.value = '1';
        btnKirimWa.disabled = true;
        btnKirimWa.classList.add('opacity-50', 'cursor-not-allowed');
        btnText.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';

        document.getElementById('formMaintenance').submit();
    }

    window.addEventListener('DOMContentLoaded', () => {
        if(katSelect.value) { katSelect.dispatchEvent(new Event('change')); }

        @if(session('open_wa'))
            const waUrl = "{!! session('open_wa') !!}";
            const waWindow = window.open(waUrl, '_blank');
            if(!waWindow || waWindow.closed || typeof waWindow.closed=='undefined') {
                alert('Pesan berhasil disimpan! Namun browser memblokir pop-up WhatsApp.');
            }
        @endif
    });
</script>

<style>
    .font-black { font-weight: 900 !important; }
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(12%) sepia(48%) saturate(3025%) hue-rotate(195deg) brightness(92%) contrast(105%);
    }
</style>
@endsection