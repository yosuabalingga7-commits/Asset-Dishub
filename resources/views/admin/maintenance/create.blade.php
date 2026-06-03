@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F8FAFC] font-['Inter'] pb-12 text-left">
    {{-- Header --}}
    <div class="bg-white border-b border-slate-200 px-8 py-6 mb-8 shadow-sm">
        <div class="max-w-5xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <nav class="flex mb-3 text-[10px] font-black tracking-[0.2em] text-slate-400">
                    <a href="{{ route('admin.maintenance.index') }}" class="hover:text-[#0B2A4A] transition-colors">Monitoring</a>
                    <span class="mx-3 text-slate-300">/</span>
                    <span class="text-[#0B2A4A]">{{ isset($maintenance) ? 'Edit Penugasan' : 'Buat Tiket Baru' }}</span>
                </nav>
                <h1 class="text-2xl font-black text-[#0B2A4A] tracking-tighter">
                    {{ isset($maintenance) ? 'Update Penugasan Seksi' : 'Registrasi Tiket Maintenance' }}
                </h1>
            </div>
            <a href="{{ url()->previous() }}" class="text-[10px] font-black tracking-widest text-slate-400 hover:text-rose-500 transition-colors">
                ← Batal & Kembali
            </a>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-8">
        @if ($errors->any())
            <div class="mb-6 p-4 bg-rose-100 border-l-4 border-rose-500 text-rose-700 rounded-xl shadow-sm">
                <p class="font-black text-xs tracking-widest mb-2">TERJADI KESALAHAN INPUT:</p>
                <ul class="list-disc list-inside text-xs font-bold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="formMaintenance" action="{{ isset($maintenance) ? route('admin.maintenance.update', $maintenance->id) : route('admin.maintenance.store') }}" 
              method="POST" class="space-y-6">
            @csrf
            @if(isset($maintenance)) @method('PUT') @endif
            
            <input type="hidden" name="report_id" value="{{ $laporan ? $laporan->id : ($maintenance->report_id ?? '') }}">
            <input type="hidden" name="asset_id" value="{{ $laporan ? $laporan->id_asset : ($maintenance->asset_id ?? '') }}">
            
            {{-- FIX FORM BLADE: Mengambil value dinamis kepemilikan yang sudah disinkronkan di controller --}}
            <input type="hidden" name="source_type" value="{{ $laporan->kepemilikan ?? ($maintenance->kepemilikan ?? 'Dishub') }}">
            
            <input type="hidden" name="location_address" value="{{ $laporan->alamat ?? ($maintenance->location_address ?? '') }}">
            <input type="hidden" name="latitude" value="{{ $laporan->lat ?? ($maintenance->latitude ?? '') }}">
            <input type="hidden" name="longitude" value="{{ $laporan->lng ?? ($maintenance->longitude ?? '') }}">
            <input type="hidden" name="category_id" id="category_id_hidden" value="{{ old('category_id', $maintenance->category_id ?? '') }}">
            <input type="hidden" name="send_wa" id="input_send_wa" value="0">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                {{-- Panel Kiri --}}
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-[#0B2A4A] rounded-[2.5rem] p-8 text-white shadow-2xl relative overflow-hidden">
                        <p class="text-[10px] font-black text-white/40 tracking-[0.2em] mb-4">Referensi Laporan</p>
                        
                        <div class="p-4 bg-amber-500 rounded-2xl border border-white/10 shadow-lg mb-6">
                            <p class="text-[9px] font-black text-white/80 mb-1">ID Tiket / No. Urut</p>
                            <p class="text-2xl font-black text-white">#{{ isset($maintenance) ? $maintenance->id : ($laporan->id ?? 'NEW') }}</p>
                        </div>
                        
                        @php $ref = $laporan ?? ($maintenance->report ?? null); @endphp
                        
                        @if($ref)
                            <div class="space-y-4">
                                <div class="relative group">
                                    <p class="text-[9px] font-black text-amber-500 mb-2">Foto Kondisi</p>
                                    @if($ref->foto)
                                        <img src="{{ asset('storage/' . $ref->foto) }}" class="w-full h-48 object-cover rounded-2xl border border-white/10 mb-4 shadow-lg">
                                    @else
                                        <div class="w-full h-32 bg-white/5 rounded-2xl border border-dashed border-white/20 flex items-center justify-center mb-4">
                                            <span class="text-[10px] text-white/30 font-bold">Tidak ada foto</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4 bg-white/5 rounded-2xl border border-white/10">
                                    <p class="text-[9px] font-black text-blue-400 mb-1">Judul Laporan</p>
                                    <p class="text-xs font-bold italic leading-relaxed">"{{ $ref->judul_laporan ?? ($maintenance->jenis_aset ?? 'Laporan Tanpa Judul') }}"</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Panel Kanan --}}
                <div class="lg:col-span-2">
                    <div class="bg-white border border-slate-200 rounded-[2.5rem] p-10 shadow-sm space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 mb-4 block tracking-widest italic">1. Petugas Pelaksana</label>
                                    <select name="user_id" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-[#0B2A4A] text-xs font-bold focus:ring-2 focus:ring-[#25D366] outline-none transition-all">
                                        <option value="">-- Pilih Petugas --</option>
                                        @foreach($listSeksi as $petugas)
                                            @if($petugas->role == 'seksi')
                                            <option value="{{ $petugas->id }}" {{ (old('user_id', $maintenance->user_id ?? '') == $petugas->id) ? 'selected' : '' }}>
                                                👤 {{ strtoupper($petugas->name) }} | {{ strtoupper($petugas->seksi->nama_seksi ?? 'Petugas') }}
                                            </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-slate-400 mb-4 block tracking-widest italic">2. Target Selesai</label>
                                    <input type="date" name="deadline" value="{{ old('deadline', isset($maintenance->deadline) ? \Carbon\Carbon::parse($maintenance->deadline)->format('Y-m-d') : '') }}" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-[#0B2A4A] text-xs font-bold outline-none transition-all">
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label class="text-[10px] font-black text-slate-400 mb-4 block tracking-widest italic">3. Kategori Aset</label>
                                    <select name="category" id="kat_select" onchange="updateJenisDropdown()" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-[#0B2A4A] text-xs font-bold outline-none transition-all">
                                        <option value="">-- Pilih Kategori --</option>
                                        @php 
                                            $activeKat = old('category', $maintenance->category ?? ($laporan->kategori_laporan ?? '')); 
                                        @endphp
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->nama_kategori }}" data-id="{{ $cat->id }}" {{ (strtoupper(trim($activeKat)) == strtoupper(trim($cat->nama_kategori))) ? 'selected' : '' }}>
                                                {{ $cat->nama_kategori }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-[10px] font-black text-slate-400 mb-4 block tracking-widest italic">4. Jenis Spesifik</label>
                                    <select name="jenis_aset" id="jenis_select" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-[#0B2A4A] text-xs font-bold outline-none transition-all">
                                        <option value="">-- Pilih Kategori Dahulu --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="text-[10px] font-black text-slate-400 mb-4 block tracking-widest italic">5. Prioritas</label>
                            <select name="priority" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-4 text-[#0B2A4A] text-xs font-bold outline-none transition-all">
                                @foreach(['Urgent', 'Tinggi', 'Normal', 'Rendah'] as $prio)
                                    <option value="{{ $prio }}" {{ old('priority', $maintenance->priority ?? 'Normal') == $prio ? 'selected' : '' }}>{{ strtoupper($prio) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row gap-4">
                            <button type="submit" onclick="document.getElementById('input_send_wa').value='0'" class="flex-1 bg-slate-800 text-white py-5 rounded-2xl font-black text-xs tracking-[0.2em] shadow-lg transition-all hover:bg-slate-700">
                                <i class="fas fa-save mr-2"></i> Simpan Saja
                            </button>
                            <button type="button" onclick="kirimWhatsApp()" class="flex-1 bg-[#25D366] text-white py-5 rounded-2xl font-black text-xs tracking-[0.2em] shadow-lg transition-all hover:bg-[#128C7E] flex items-center justify-center gap-2">
                                <i class="fab fa-whatsapp text-lg"></i> Simpan & Kirim WA
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
        'PENERANGAN JALAN UMUM (PJU)': ['Tiang PJU Galvanis', 'Tiang PJU Dekoratif', 'Lampu LED', 'Lampu Solar Cell', 'Panel Box PJU', 'Kabel Udara PJU'],
        'PERLENGKAPAN JALAN': ['Rambu Larangan', 'Rambu Peringatan', 'Rambu Petunjuk', 'Rambu Perintah', 'Rambu Parkir', 'Cermin Tikungan', 'Guardrail', 'Delineator'],
        'FASILITAS LALU LINTAS': ['APILL (Traffic Light)', 'Warning Light', 'Marka Jalan', 'Marka Zebra Cross', 'Paku Jalan', 'Water Barrier'],
        'PENGENDALIAN DAN PENGAWASAN': ['CCTV Surveilans', 'CCTV E-TLE', 'VMS (Papan Digital)', 'ATCS Controller', 'CCTV Survey'],
        'PRASARANA TRANSPORTASI': ['Halte Bus', 'Terminal', 'Jembatan Penyeberangan (JPO)', 'Gedung PKB']
    };

    function updateJenisDropdown() {
        const katSelect = document.getElementById('kat_select');
        const jenisSelect = document.getElementById('jenis_select');
        const categoryIdHidden = document.getElementById('category_id_hidden');
        
        const selectedOption = katSelect.options[katSelect.selectedIndex];
        const selectedKatText = (katSelect.value || "").toUpperCase().trim();
        
        if(selectedOption && selectedOption.dataset.id) {
            categoryIdHidden.value = selectedOption.dataset.id;
        }

        const currentJenis = "{{ old('jenis_aset', $maintenance->jenis_aset ?? ($laporan->judul_laporan ?? '')) }}";
        jenisSelect.innerHTML = '<option value="">-- Pilih Jenis --</option>';
        
        if(!selectedKatText) return;
        
        let foundKey = null;
        for(let key in dbJenis) {
            if(selectedKatText.includes(key) || key.includes(selectedKatText)) {
                foundKey = key;
                break;
            }
        }
        
        if(foundKey) {
            dbJenis[foundKey].forEach(jenis => {
                const opt = document.createElement('option');
                opt.value = jenis;
                opt.textContent = jenis;
                if(currentJenis === jenis) opt.selected = true;
                jenisSelect.appendChild(opt);
            });
        }
    }

    document.addEventListener('DOMContentLoaded', updateJenisDropdown);

    function kirimWhatsApp() {
        document.getElementById('input_send_wa').value = '1';
        document.getElementById('formMaintenance').submit();
    }
</script>
@endsection