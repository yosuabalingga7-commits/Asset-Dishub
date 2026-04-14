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
</style>

<div class="h-full w-full overflow-y-auto custom-scroll bg-[#F8FAFC] font-inter-fix" x-data="{ 
    filterStatus: 'semua', 
    filterKondisi: 'semua',
    searchTerm: '',
    checkFilter(val, filter) {
        if(filter === 'semua') return true;
        let value = val.toLowerCase().trim();
        let target = filter.toLowerCase().trim();
        
        // PERBAIKAN FILTER: Jika user memilih filter 'baik', maka status 'selesai' juga harus tampil
        if(target === 'baik') {
            return value === 'baik' || value === 'selesai';
        }
        
        return value === target;
    }
}">
    <div class="p-6 md:p-10">
        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 gap-4">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tighter">
                        Daftar Laporan <span class="text-indigo-600">Masuk</span>
                    </h1>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Manajemen Pengaduan Infrastruktur Masyarakat</p>
                </div>
                <div class="flex gap-2">
                    <button onclick="window.location.reload()" class="bg-white border-2 border-slate-100 p-2.5 rounded-xl hover:bg-slate-50 transition-all shadow-sm group">
                        <svg class="w-5 h-5 text-slate-400 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-3xl border-2 border-slate-50 shadow-sm transition-transform hover:scale-[1.02]">
                    <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Laporan</p>
                    <p class="text-2xl font-black text-slate-900 leading-none">{{ $tickets->total() }}</p>
                </div>
                <div class="bg-red-50 p-5 rounded-3xl border-2 border-red-100 shadow-sm transition-transform hover:scale-[1.02]">
                    <p class="text-[8px] font-black text-red-400 uppercase tracking-widest mb-1">Baru Masuk</p>
                    <p class="text-2xl font-black text-red-600 leading-none">{{ $allData->whereIn('status', ['masuk', 'Masuk'])->count() }}</p>
                </div>
                <div class="bg-blue-50 p-5 rounded-3xl border-2 border-blue-100 shadow-sm transition-transform hover:scale-[1.02]">
                    <p class="text-[8px] font-black text-blue-600 uppercase tracking-widest mb-1">Proses Perbaikan</p>
                    <p class="text-2xl font-black text-blue-600 leading-none">{{ $allData->where('status', 'Proses Perbaikan')->count() }}</p>
                </div>
                <div class="bg-green-50 p-5 rounded-3xl border-2 border-green-100 shadow-sm transition-transform hover:scale-[1.02]">
                    <p class="text-[8px] font-black text-green-600 uppercase tracking-widest mb-1">Selesai / Baik</p>
                    <p class="text-2xl font-black text-green-600 leading-none">{{ $allData->whereIn('status', ['Baik', 'Selesai', 'baik', 'selesai'])->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-50 mb-6 flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[250px] relative">
                <input type="text" x-model="searchTerm" placeholder="Cari Kode Laporan / Nama Pelapor..." class="w-full pl-11 pr-4 py-3.5 rounded-2xl bg-slate-50 border-none text-[11px] font-bold focus:ring-2 focus:ring-indigo-500 transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-4 top-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <div class="flex flex-wrap gap-3">
                <select x-model="filterKondisi" class="bg-slate-50 px-4 py-3.5 rounded-2xl border-none text-[10px] font-black uppercase outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm cursor-pointer">
                    <option value="semua">Semua Kondisi</option>
                    <option value="Rusak">Rusak</option>
                    <option value="Hilang">Hilang</option>
                    <option value="Lainnya">Lainnya</option>
                    <option value="Pindah Tempat">Pindah tempat</option>
                </select>

                <select x-model="filterStatus" class="bg-slate-50 px-4 py-3.5 rounded-2xl border-none text-[10px] font-black uppercase outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm cursor-pointer">
                    <option value="semua">Semua Status Sistem</option>
                    <option value="masuk">🔴 Masuk (Baru)</option>
                    <option value="Rusak">🟡 Status: Rusak</option>
                    <option value="Kritis">🔴 Status: Kritis</option>
                    <option value="Proses Perbaikan">🔵 Proses Perbaikan</option>
                    <option value="Baik">🟢 Selesai (Baik)</option>
                </select>

                <button @click="filterStatus = 'semua'; filterKondisi = 'semua'; searchTerm = ''" class="bg-slate-900 text-white px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-md active:scale-95">
                    Reset
                </button>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-slate-900 text-white text-[9px] font-black uppercase tracking-widest">
                            <th class="p-6 w-16 text-center">No</th>
                            <th class="p-6">Kode Tiket</th>
                            <th class="p-6">Pelapor</th>
                            <th class="p-6">Judul Laporan</th>
                            <th class="p-6 text-center">Kondisi (Warga)</th>
                            <th class="p-6 text-center">Status (Sistem)</th>
                            <th class="p-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-slate-600 text-[11px] font-bold">
                        @forelse($tickets as $ticket)
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors" 
                            x-show="checkFilter('{{ $ticket->status }}', filterStatus) && 
                                    checkFilter('{{ $ticket->kondisi_aset }}', filterKondisi) &&
                                    (searchTerm === '' || '{{ strtolower($ticket->ticket_number) }}'.includes(searchTerm.toLowerCase()) || '{{ strtolower(addslashes($ticket->nama_pelapor)) }}'.includes(searchTerm.toLowerCase()))"
                            x-cloak>
                            
                            <td class="p-6 text-center text-slate-400">
                                {{ ($tickets->currentPage() - 1) * $tickets->perPage() + $loop->iteration }}
                            </td>
                            <td class="p-6 text-indigo-600 font-black">#{{ $ticket->ticket_number }}</td>
                            <td class="p-6">
                                <div class="uppercase tracking-tighter text-slate-900">{{ $ticket->nama_pelapor }}</div>
                                <div class="text-[9px] text-slate-400 font-medium">{{ $ticket->kontak_pelapor }}</div>
                            </td>
                            <td class="p-6 text-slate-500 max-w-[200px] truncate">{{ $ticket->judul_laporan }}</td>
                            <td class="p-6 text-center text-[10px]">
                                <span class="bg-slate-100 px-3 py-1.5 rounded-lg text-[9px] uppercase border border-slate-200 text-slate-700">{{ $ticket->kondisi_aset }}</span>
                            </td>
                            <td class="p-6 text-center">
                                @php
                                    $badgeColor = 'bg-slate-100 text-slate-600';
                                    $s = strtolower($ticket->status);
                                    if($s == 'masuk') $badgeColor = 'bg-red-50 text-red-600 border border-red-100';
                                    elseif($s == 'kritis') $badgeColor = 'bg-red-600 text-white shadow-lg shadow-red-100';
                                    elseif($s == 'rusak') $badgeColor = 'bg-amber-100 text-amber-600 border border-amber-200';
                                    elseif($s == 'proses perbaikan') $badgeColor = 'bg-blue-600 text-white shadow-lg shadow-blue-100';
                                    elseif($s == 'baik' || $s == 'selesai') $badgeColor = 'bg-green-100 text-green-600 border border-green-200';
                                @endphp
                                <span class="px-3 py-1.5 rounded-full text-[8px] font-black uppercase {{ $badgeColor }}">
                                    {{ $ticket->status }}
                                </span>
                            </td>
                            <td class="p-6 text-right">
                                <a href="{{ route('admin.pengaduan.show', $ticket->id) }}" class="inline-block bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-[9px] font-black uppercase hover:bg-slate-900 transition-all shadow-lg shadow-indigo-100 active:scale-95">
                                    Detail & Validasi
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-20 text-center uppercase font-black text-slate-300 tracking-[0.3em]">
                                Tidak ada data laporan di database
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($tickets->hasPages())
            <div class="p-6 bg-slate-50/50 border-t border-slate-50">
                {{ $tickets->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection