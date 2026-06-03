@extends('layouts.app')

@section('content')
<style>
    .font-inter-fix * {
        font-family: 'Inter', sans-serif !important;
        font-style: normal !important;
    }
    [x-cloak] { display: none !important; }

    .custom-scroll::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .custom-scroll::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }

    input, select {
        color: #1E293B !important;
        font-weight: 500 !important;
    }
    
    input::placeholder {
        color: #94a3b8 !important;
        font-weight: 400 !important;
    }
    
    select option {
        color: #1E293B !important;
        background: white !important;
    }
    
    select {
        background-color: #f8fafc !important;
        color: #1E293B !important;
    }
    
    input:focus, select:focus {
        border-color: #2563EB !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1) !important;
        outline: none !important;
    }
    
    .badge-masuk {
        background-color: #FEF2F2 !important;
        color: #DC2626 !important;
        border: 1px solid #FEE2E2 !important;
    }
    .badge-proses {
        background-color: #EFF6FF !important;
        color: #2563EB !important;
        border: 1px solid #BFDBFE !important;
    }
    .badge-selesai {
        background-color: #ECFDF5 !important;
        color: #059669 !important;
        border: 1px solid #D1FAE5 !important;
    }
    .badge-rusak {
        background-color: #FFFBEB !important;
        color: #D97706 !important;
        border: 1px solid #FDE68A !important;
    }
    .badge-kritis {
        background-color: #FEF2F2 !important;
        color: #DC2626 !important;
        border: 1px solid #FEE2E2 !important;
    }
    .badge-hilang {
        background-color: #FEF3C7 !important;
        color: #B45309 !important;
        border: 1px solid #FDE68A !important;
    }
    .badge-default {
        background-color: #F1F5F9 !important;
        color: #475569 !important;
        border: 1px solid #E2E8F0 !important;
    }
</style>

<div class="h-full w-full overflow-y-auto custom-scroll bg-[#F8FAFC] font-inter-fix">
    <div class="p-6 md:p-10">
        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-black text-[#1E293B] tracking-tighter">
                        Daftar Laporan <span class="text-[#2563EB]">Masyarakat</span>
                    </h1>
                    <p class="text-[10px] font-bold text-slate-400 tracking-widest">Manajemen Pengaduan Infrastruktur</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.pengaduan.petugas') }}" class="bg-[#2563EB] hover:bg-[#1D4ED8] text-white px-5 py-2.5 rounded-xl text-[11px] font-bold transition-all shadow-md hover:shadow-lg active:scale-95 flex items-center gap-2">
                        <i class="fas fa-user-shield"></i> Lihat Laporan Petugas
                    </a>
                    <button onclick="window.location.reload()" class="bg-white border border-slate-200 p-2.5 rounded-xl hover:bg-slate-50 transition-all shadow-sm group">
                        <svg class="w-5 h-5 text-[#1E293B] group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                </div>
            </div>

            {{-- STATISTIK CARDS (Hanya untuk Laporan Masyarakat) --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl border border-slate-100 shadow-sm transition-all hover:shadow-md">
                    <p class="text-[8px] font-black text-slate-400 tracking-widest mb-1">Total Laporan Masyarakat</p>
                    <p class="text-2xl font-black text-[#1E293B] leading-none">{{ $laporanMasyarakat->total() }}</p>
                </div>
                
                <div class="bg-white p-5 rounded-xl border-l-4 border-l-red-500 border border-slate-100 shadow-sm transition-all hover:shadow-md">
                    <p class="text-[8px] font-black text-slate-400 tracking-widest mb-1">Baru Masuk</p>
                    <p class="text-2xl font-black text-[#1E293B] leading-none">{{ $allDataMasyarakat->whereIn('status', ['masuk', 'Masuk'])->count() }}</p>
                </div>
                
                <div class="bg-white p-5 rounded-xl border-l-4 border-l-[#2563EB] border border-slate-100 shadow-sm transition-all hover:shadow-md">
                    <p class="text-[8px] font-black text-slate-400 tracking-widest mb-1">Proses Perbaikan</p>
                    <p class="text-2xl font-black text-[#1E293B] leading-none">{{ $allDataMasyarakat->where('status', 'Proses Perbaikan')->count() }}</p>
                </div>
                
                <div class="bg-white p-5 rounded-xl border-l-4 border-l-emerald-500 border border-slate-100 shadow-sm transition-all hover:shadow-md">
                    <p class="text-[8px] font-black text-slate-400 tracking-widest mb-1">Selesai / Baik</p>
                    <p class="text-2xl font-black text-[#1E293B] leading-none">{{ $allDataMasyarakat->whereIn('status', ['Baik', 'Selesai', 'baik', 'selesai'])->count() }}</p>
                </div>
            </div>
        </div>

        {{-- FILTER & SEARCH BAR --}}
        <div class="bg-white p-4 rounded-xl shadow-md border border-slate-100 mb-6 flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[250px] relative">
                <input type="text" id="searchMasyarakat" placeholder="Cari Kode Laporan / Nama Pelapor..." class="w-full pl-11 pr-4 py-3.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] font-medium focus:border-[#2563EB] transition-all text-[#1E293B]">
                <svg class="w-4 h-4 text-slate-400 absolute left-4 top-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <div class="flex flex-wrap gap-3">
                <select id="filterKondisiMasyarakat" class="bg-slate-50 px-4 py-3.5 rounded-xl border border-slate-200 text-[11px] font-medium outline-none focus:border-[#2563EB] shadow-sm cursor-pointer text-[#1E293B]">
                    <option value="semua">Semua Jenis Kejadian</option>
                    <option value="Rusak">Rusak</option>
                    <option value="Hilang">Hilang</option>
                    <option value="Lainnya">Lainnya</option>
                    <option value="Pindah Tempat">Pindah tempat</option>
                </select>

                <select id="filterStatusMasyarakat" class="bg-slate-50 px-4 py-3.5 rounded-xl border border-slate-200 text-[11px] font-medium outline-none focus:border-[#2563EB] shadow-sm cursor-pointer text-[#1E293B]">
                    <option value="semua">Semua Status Laporan</option>
                    <option value="masuk">🔴 Masuk (Baru)</option>
                    <option value="Proses Perbaikan">🔵 Proses Perbaikan</option>
                    <option value="Baik">🟢 Selesai (Baik)</option>
                </select>

                <button id="resetFilterMasyarakat" class="bg-white border border-slate-300 text-[#1E293B] px-6 py-3.5 rounded-xl text-[10px] font-bold tracking-widest hover:bg-slate-50 transition-all shadow-sm active:scale-95">
                    Reset
                </button>
            </div>
        </div>

        {{-- TABEL DATA LAPORAN MASYARAKAT --}}
        <div class="bg-white rounded-xl shadow-md border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]" id="tableMasyarakat">
                    <thead>
                        <tr class="bg-[#1E293B] text-white text-[9px] font-black tracking-widest">
                            <th class="p-6 w-16 text-center">No</th>
                            <th class="p-6">Kode Tiket</th>
                            <th class="p-6">Pelapor</th>
                            <th class="p-6">Judul Laporan</th>
                            <th class="p-6 text-center">Jenis Kejadian</th>
                            <th class="p-6 text-center">Status Laporan</th>
                            <th class="p-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600 text-[11px] font-medium">
                        @forelse($laporanMasyarakat as $ticket)
                        <tr class="border-b border-slate-100 hover:bg-slate-50/80 transition-colors {{ $loop->iteration % 2 == 0 ? 'bg-white' : 'bg-slate-50/30' }} baris-masyarakat"
                            data-ticket-number="{{ strtolower($ticket->ticket_number) }}"
                            data-nama-pelapor="{{ strtolower(addslashes($ticket->nama_pelapor)) }}"
                            data-status="{{ strtolower($ticket->status) }}"
                            data-kondisi="{{ strtolower($ticket->kondisi_aset) }}">
                            <td class="p-6 text-center text-slate-400 font-bold">{{ ($laporanMasyarakat->currentPage() - 1) * $laporanMasyarakat->perPage() + $loop->iteration }}</td>
                            <td class="p-6"><a href="{{ route('admin.pengaduan.show', $ticket->id) }}" class="text-[#2563EB] font-black hover:underline">#{{ $ticket->ticket_number }}</a></td>
                            <td class="p-6">
                                <div class="tracking-tighter text-[#1E293B] font-bold">{{ $ticket->nama_pelapor }}</div>
                                <div class="text-[9px] text-slate-400 font-medium">{{ $ticket->kontak_pelapor }}</div>
                            </td>
                            <td class="p-6 text-slate-600 max-w-[200px] truncate">{{ $ticket->judul_laporan }}</td>
                            <td class="p-6 text-center">
                                @php
                                    $kondisiBadge = 'badge-default';
                                    $k = strtolower($ticket->kondisi_aset);
                                    if($k == 'rusak') $kondisiBadge = 'badge-rusak';
                                    elseif($k == 'hilang' || $k == 'hilang/dicuri') $kondisiBadge = 'badge-hilang';
                                    elseif($k == 'kritis') $kondisiBadge = 'badge-kritis';
                                @endphp
                                <span class="px-3 py-1.5 rounded-lg text-[9px] font-bold {{ $kondisiBadge }}">{{ $ticket->kondisi_aset }}</span>
                            </td>
                            <td class="p-6 text-center">
                                @php
                                    $badgeColor = 'badge-default';
                                    $s = strtolower($ticket->status);
                                    if($s == 'masuk') $badgeColor = 'badge-masuk';
                                    elseif($s == 'kritis') $badgeColor = 'badge-kritis';
                                    elseif($s == 'rusak') $badgeColor = 'badge-rusak';
                                    elseif($s == 'proses perbaikan') $badgeColor = 'badge-proses';
                                    elseif($s == 'baik' || $s == 'selesai') $badgeColor = 'badge-selesai';
                                @endphp
                                <span class="px-3 py-1.5 rounded-full text-[8px] font-bold {{ $badgeColor }}">{{ $ticket->status }}</span>
                            </td>
                            <td class="p-6 text-right">
                                <a href="{{ route('admin.pengaduan.show', $ticket->id) }}" class="inline-block bg-[#2563EB] hover:bg-[#1D4ED8] text-white px-5 py-2.5 rounded-lg text-[9px] font-bold transition-all shadow-md hover:shadow-lg active:scale-95">Detail & Validasi</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-20 text-center font-black text-slate-300 tracking-[0.3em]">Tidak ada data laporan masyarakat</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($laporanMasyarakat->hasPages())
            <div class="p-6 bg-slate-50/50 border-t border-slate-100">{{ $laporanMasyarakat->links() }}</div>
            @endif
        </div>
    </div>
</div>

<script>
    // Filter untuk Tabel Masyarakat
    function filterMasyarakat() {
        const searchTerm = document.getElementById('searchMasyarakat').value.toLowerCase();
        const filterKondisi = document.getElementById('filterKondisiMasyarakat').value.toLowerCase();
        const filterStatus = document.getElementById('filterStatusMasyarakat').value.toLowerCase();
        const rows = document.querySelectorAll('#tableMasyarakat tbody .baris-masyarakat');
        
        rows.forEach(row => {
            const ticketNumber = row.getAttribute('data-ticket-number');
            const namaPelapor = row.getAttribute('data-nama-pelapor');
            const status = row.getAttribute('data-status');
            const kondisi = row.getAttribute('data-kondisi');
            
            let show = true;
            
            if (searchTerm && !ticketNumber.includes(searchTerm) && !namaPelapor.includes(searchTerm)) show = false;
            if (filterKondisi !== 'semua' && kondisi !== filterKondisi) show = false;
            if (filterStatus !== 'semua') {
                if (filterStatus === 'baik' && !(status === 'baik' || status === 'selesai')) show = false;
                else if (filterStatus !== 'baik' && status !== filterStatus) show = false;
            }
            
            row.style.display = show ? '' : 'none';
        });
    }
    
    document.getElementById('searchMasyarakat')?.addEventListener('keyup', filterMasyarakat);
    document.getElementById('filterKondisiMasyarakat')?.addEventListener('change', filterMasyarakat);
    document.getElementById('filterStatusMasyarakat')?.addEventListener('change', filterMasyarakat);
    document.getElementById('resetFilterMasyarakat')?.addEventListener('click', function() {
        document.getElementById('searchMasyarakat').value = '';
        document.getElementById('filterKondisiMasyarakat').value = 'semua';
        document.getElementById('filterStatusMasyarakat').value = 'semua';
        filterMasyarakat();
    });
</script>
@endsection