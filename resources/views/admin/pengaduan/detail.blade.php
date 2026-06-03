@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F8FAFC] font-['Inter'] pb-12 text-left" x-data="{ showModal: false, showRejectModal: false }">
    {{-- Header --}}
    <div class="bg-white border-b border-slate-200 px-8 py-6 mb-8 shadow-sm">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <nav class="flex mb-3 text-[10px] font-black tracking-[0.2em] text-slate-400">
                    <a href="{{ route('admin.pengaduan.index') }}" class="hover:text-[#1E293B] transition-colors">Manajemen Laporan</a>
                    <span class="mx-3 text-slate-300">/</span>
                    <span class="text-[#1E293B]">Detail Pengaduan</span>
                </nav>
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-black text-[#1E293B] tracking-tighter">
                        Laporan #{{ $laporan->ticket_number }}
                    </h1>
                    @php
                        $status = strtolower($laporan->status);
                    @endphp
                    @if($status == 'masuk')
                        <span class="px-4 py-1.5 bg-blue-100 text-blue-700 rounded-full text-[10px] font-black tracking-widest border border-blue-200">🔵 Verifikasi</span>
                    @elseif($status == 'proses perbaikan' || $status == 'proses')
                        <span class="px-4 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-[10px] font-black tracking-widest border border-indigo-200">🔵 Proses</span>
                    @elseif($status == 'ditolak')
                        <span class="px-4 py-1.5 bg-slate-100 text-slate-600 rounded-full text-[10px] font-black tracking-widest border border-slate-200">⚪ Ditolak</span>
                    @elseif($status == 'rusak' || $status == 'kritis')
                        <span class="px-4 py-1.5 bg-red-50 text-red-700 rounded-full text-[10px] font-black tracking-widest border border-red-200">🔴 {{ strtoupper($status) }}</span>
                    @else
                        <span class="px-4 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black tracking-widest border border-emerald-200">🟢 Selesai / Baik</span>
                    @endif
                </div>
            </div>

            {{-- Progress Stepper --}}
            <div class="flex items-center">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 {{ ($laporan->is_validated || $status == 'baik') ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-[#1E293B] text-[#1E293B] bg-white' }} font-black shadow-sm transition-all">
                        @if($laporan->is_validated || $status == 'baik') ✓ @else 1 @endif
                    </div>
                    <span class="text-[9px] font-black tracking-tighter mt-2 text-slate-500">Validasi</span>
                </div>
                <div class="w-12 h-0.5 {{ ($laporan->is_validated || $status == 'baik') ? 'bg-emerald-500' : 'bg-slate-200' }} mb-4 mx-2"></div>
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 {{ ($status == 'proses perbaikan' || $status == 'proses') ? 'bg-[#2563EB] border-[#2563EB] text-white shadow-lg animate-pulse' : ($status == 'baik' ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-200 text-slate-300') }} font-black transition-all">
                        @if($status == 'baik') ✓ @else 2 @endif
                    </div>
                    <span class="text-[9px] font-black tracking-tighter mt-2 text-slate-500">Perbaikan</span>
                </div>
                <div class="w-12 h-0.5 {{ ($status == 'baik') ? 'bg-emerald-500' : 'bg-slate-200' }} mb-4 mx-2"></div>
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full border-2 {{ ($status == 'baik') ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-slate-200 text-slate-300' }} font-black transition-all">
                        3
                    </div>
                    <span class="text-[9px] font-black tracking-tighter mt-2 text-slate-500">Selesai</span>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
            <div class="lg:col-span-3 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Identitas Pelapor --}}
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 bg-blue-50 rounded-xl text-[#2563EB]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            </div>
                            <p class="text-[10px] font-black text-slate-400 tracking-widest">Identitas Pelapor</p>
                        </div>
                        <h2 class="text-lg font-black text-[#1E293B] leading-tight">{{ $laporan->nama_pelapor }}</h2>
                        <p class="text-xs font-bold text-slate-500 tracking-wider font-mono mb-4">{{ $laporan->kontak_pelapor ?? 'Tidak Ada Kontak' }}</p>
                        
                        <div class="mt-auto pt-4 border-t border-slate-100">
                            @php
                                $phone = preg_replace('/[^0-9]/', '', $laporan->kontak_pelapor);
                                if ($phone && str_starts_with($phone, '0')) { $phone = '62' . substr($phone, 1); }
                            @endphp
                            <a href="https://wa.me/{{ $phone }}" target="_blank" class="w-full inline-flex justify-center items-center gap-2 bg-[#2563EB] hover:bg-[#1E293B] text-white py-3 rounded-xl text-[10px] font-black transition-all">
                                HUBUNGI WHATSAPP
                            </a>
                        </div>
                    </div>

                    {{-- Detail Laporan dengan Data Aset --}}
                    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm flex flex-col">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-2 bg-amber-50 rounded-xl text-[#2563EB]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <p class="text-[10px] font-black text-slate-400 tracking-widest">Detail Laporan</p>
                        </div>
                        <h2 class="text-sm font-black text-[#1E293B] leading-tight mb-2">{{ $laporan->judul_laporan }}</h2>
                        <p class="text-[10px] font-bold text-slate-500">Kondisi: <span class="text-red-700">{{ $laporan->kondisi_aset }}</span></p>
                        
                        {{-- Data Aset dari Relasi --}}
                        @if($laporan->asset)
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 tracking-widest mb-1">Data Aset Terdeteksi:</p>
                            <p class="text-[10px] font-bold text-[#1E293B]"><span class="text-slate-500">Nama Aset:</span> {{ $laporan->asset->nama }}</p>
                            <p class="text-[10px] font-bold text-[#1E293B]"><span class="text-slate-500">Kategori:</span> {{ $laporan->asset->kategori }}</p>
                            <p class="text-[10px] font-bold text-[#1E293B]"><span class="text-slate-500">ID Aset:</span> {{ $laporan->id_asset }}</p>
                            <p class="text-[10px] font-bold text-[#1E293B]"><span class="text-slate-500">Jenis:</span> {{ $laporan->asset->jenis ?? '-' }}</p>
                        </div>
                        @else
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <p class="text-[9px] font-black text-amber-500 tracking-widest mb-1">⚠️ Data Aset:</p>
                            <p class="text-[10px] font-bold text-slate-400">Tidak ada aset terdeteksi dalam radius 100 meter</p>
                        </div>
                        @endif
                        
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 tracking-widest mb-1">Lokasi Kejadian:</p>
                            <p class="text-[11px] font-bold text-slate-600 leading-relaxed">{{ $laporan->alamat ?? 'Alamat tidak diinput' }}</p>
                        </div>
                    </div>

                    {{-- Bukti Foto --}}
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
                        <div class="p-3 border-b border-slate-100 bg-slate-50">
                            <span class="text-[9px] font-black text-slate-400 tracking-widest">Foto Kejadian</span>
                        </div>
                        <div class="flex-1 bg-slate-100 min-h-[120px]">
                            @if($laporan->foto)
                                <img src="{{ asset('storage/' . $laporan->foto) }}" class="w-full h-full object-cover" alt="Foto Laporan">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-400 font-black text-[9px]">TIDAK ADA FOTO</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Map Dinamis --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] font-black text-red-700 tracking-widest">📍 Koordinat: {{ $laporan->lat ?? '0' }}, {{ $laporan->lng ?? '0' }}</span>
                    </div>
                    <div id="detailMap" class="w-full h-[350px] z-0"></div>
                </div>

                {{-- Timeline Riwayat --}}
                <div class="bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
                    <h4 class="text-[11px] font-black text-[#1E293B] tracking-widest mb-8">Riwayat Aktivitas</h4>
                    <div class="relative pl-6 border-l-2 border-slate-100 space-y-8">
                        @forelse($laporan->logs as $log)
                        <div class="relative">
                            <div class="absolute -left-[31px] top-0 w-3.5 h-3.5 bg-white border-4 border-[#2563EB] rounded-full shadow-sm"></div>
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-[11px] font-black text-[#1E293B]">{{ $log->aksi }}</p>
                                <p class="text-[10px] text-slate-400 font-bold">{{ $log->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <p class="text-[11px] text-slate-600">Oleh: <span class="font-black text-[#2563EB]">{{ $log->user->name ?? 'System' }}</span></p>
                            <div class="bg-slate-50 inline-block px-3 py-1.5 rounded-lg border border-slate-100 mt-2">
                                <p class="text-[11px] text-slate-500 italic">"{{ $log->keterangan }}"</p>
                            </div>
                        </div>
                        @empty
                        <p class="text-[10px] font-bold text-slate-400 italic">Belum ada riwayat aktivitas.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- PANEL KONTROL (KANAN) --}}
            <div class="lg:col-span-1 space-y-6">
                @if(!$laporan->is_validated && $status != 'ditolak' && $status != 'baik')
                <div class="bg-[#1E293B] rounded-2xl p-8 shadow-xl text-white">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="w-1.5 h-6 bg-amber-500 rounded-full"></div>
                        <h3 class="text-sm font-black tracking-[0.2em]">Verifikasi</h3>
                    </div>
                    
                    <div class="space-y-3 pt-4">
                        <button type="button" @click="showModal = true"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-4 rounded-xl font-black text-[10px] tracking-widest shadow-lg transition-all active:scale-95">
                            Validasi Sekarang
                        </button>

                        <button type="button" @click="showRejectModal = true"
                            class="w-full bg-white/5 hover:bg-red-600 text-slate-300 hover:text-white border border-white/10 py-4 rounded-xl font-black text-[10px] tracking-widest transition-all">
                            Tolak Laporan
                        </button>
                    </div>
                </div>

                @elseif($laporan->is_validated && $status != 'baik' && $status != 'ditolak')
                <div class="bg-[#1E293B] rounded-2xl p-8 shadow-xl text-white text-center">
                    <div class="flex items-center gap-3 mb-6 justify-center">
                        <div class="w-1.5 h-6 bg-amber-500 rounded-full"></div>
                        <h3 class="text-sm font-black tracking-[0.2em]">Pengerjaan</h3>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-slate-700 py-2 px-4 rounded-xl mb-4 border border-slate-600 text-left">
                            <span class="text-[11px] font-black text-amber-400 tracking-widest">
                                {{ $laporan->kepemilikan == 'dishub' ? 'Aset Dishub' : 'Fasilitas Umum' }}
                            </span>
                        </div>

                        @if($laporan->tiket)
                            <div class="bg-white/5 border border-white/10 p-4 rounded-xl text-center">
                                <div class="w-12 h-12 bg-[#2563EB] rounded-full flex items-center justify-center mx-auto mb-3 shadow-lg">
                                    <i class="fas fa-tools text-white"></i>
                                </div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-blue-300">Dalam Pengerjaan</p>
                                <p class="text-[9px] opacity-60 mt-1">Tiket telah dibuat. Menunggu konfirmasi selesai dari petugas.</p>
                            </div>
                        @else
                            @if($laporan->kepemilikan == 'dishub')
                            <a href="{{ route('admin.maintenance.create', ['report_id' => $laporan->id, 'source' => 'dishub']) }}" 
                               class="w-full inline-block bg-white text-[#1E293B] py-4 rounded-xl font-black text-[10px] tracking-widest shadow-xl transition-all hover:bg-slate-100">
                                <i class="fas fa-ticket-alt mr-2"></i> Buat Tiket Maintenance
                            </a>
                            @else
                            <div class="bg-amber-500/20 text-amber-400 border border-amber-500/30 p-4 rounded-xl text-[9px] font-black">
                                Menunggu Kelola Laporan Pihak Ke-3
                            </div>
                            @endif

                            <div class="relative py-2">
                                <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-white/10"></span></div>
                                <div class="relative flex justify-center text-[8px] font-black"><span class="bg-[#1E293B] px-2 text-white/40">Atau</span></div>
                            </div>

                            <form action="{{ route('admin.pengaduan.update-status', $laporan->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="Baik">
                                <input type="hidden" name="catatan_admin" value="Laporan diselesaikan langsung oleh admin.">
                                <button type="submit" class="w-full bg-emerald-600/20 hover:bg-emerald-600 text-emerald-400 hover:text-white border border-emerald-600/30 py-4 rounded-xl font-black text-[10px] tracking-widest transition-all">
                                    Selesaikan Laporan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                @elseif($status == 'ditolak')
                <div class="bg-slate-200 rounded-2xl p-8 shadow-sm text-center">
                    <h3 class="text-sm font-black text-slate-500 tracking-[0.2em]">Laporan Ditolak</h3>
                    <p class="text-[9px] text-slate-400 font-bold mt-2">Alasan: {{ $laporan->catatan_admin }}</p>
                </div>
                @else
                <div class="bg-slate-800 rounded-2xl p-8 shadow-xl text-white text-center">
                    <div class="w-16 h-16 bg-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">✓</div>
                    <h3 class="text-sm font-black tracking-[0.2em]">Laporan Selesai</h3>
                    <p class="text-[10px] text-slate-400 mt-2">Status Akhir: {{ strtoupper($laporan->status) }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL VALIDASI --}}
    <div x-show="showModal" class="fixed inset-0 z-[999] overflow-y-auto" style="display: none;" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-[#1E293B]/80 backdrop-blur-sm" @click="showModal = false"></div>
            <div class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl p-10">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1.5 h-6 bg-emerald-500 rounded-full"></div>
                    <h3 class="text-sm font-black text-[#1E293B] tracking-widest">Validasi Laporan</h3>
                </div>

                <form action="{{ route('admin.pengaduan.validate', $laporan->id) }}" method="POST" class="space-y-6 text-left">
                    @csrf
                    <div>
                        <label class="text-[10px] font-black text-slate-400 mb-3 block">Tentukan Kategori</label>
                        <select name="kepemilikan" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-[#1E293B] text-xs font-bold outline-none focus:ring-2 focus:ring-emerald-500 transition-all">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="dishub">ASET DISHUB</option>
                            <option value="umum">UMUM / PIHAK KE-3</option>
                        </select>
                    </div>

                    <input type="hidden" name="catatan_admin" value="Validasi otomatis melalui pemilihan kategori.">

                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" @click="showModal = false" class="bg-slate-100 text-slate-500 py-4 rounded-xl font-black text-[10px]">Batal</button>
                        <button type="submit" class="bg-emerald-600 text-white py-4 rounded-xl font-black text-[10px] shadow-lg">Konfirmasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL TOLAK --}}
    <div x-show="showRejectModal" class="fixed inset-0 z-[999] overflow-y-auto" style="display: none;" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4 py-6">
            <div class="fixed inset-0 bg-[#1E293B]/80 backdrop-blur-sm" @click="showRejectModal = false"></div>
            <div class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl p-10">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1.5 h-6 bg-rose-500 rounded-full"></div>
                    <h3 class="text-sm font-black text-[#1E293B] tracking-widest">Tolak Laporan</h3>
                </div>

                <form action="{{ route('admin.pengaduan.update-status', $laporan->id) }}" method="POST" class="space-y-6 text-left">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="Ditolak">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 mb-3 block">Alasan Penolakan</label>
                        <textarea name="catatan_admin" rows="3" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-[#1E293B] text-xs outline-none focus:ring-2 focus:ring-rose-500 transition-all" placeholder="Kenapa laporan ini ditolak?"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <button type="button" @click="showRejectModal = false" class="bg-slate-100 text-slate-500 py-4 rounded-xl font-black text-[10px]">Batal</button>
                        <button type="submit" class="bg-rose-600 text-white py-4 rounded-xl font-black text-[10px] shadow-lg">Tolak Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const lat = {{ $laporan->lat ?? -6.8438 }};
        const lng = {{ $laporan->lng ?? 107.4965 }};
        const map = L.map('detailMap', { 
            center: [lat, lng], 
            zoom: 17,
            scrollWheelZoom: false 
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup("Laporan #{{ $laporan->ticket_number }}").openPopup();
    });
</script>

<style>
    .font-black { font-weight: 900 !important; }
    [x-cloak] { display: none !important; }
</style>
@endsection