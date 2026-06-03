@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    * {
        font-family: 'Inter', sans-serif !important;
    }

    .table-container {
        background: white;
        border-radius: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    /* STATUS BADGES sesuai tema Corporate Trust */
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.03em;
    }

    .status-baik { background: #ecfdf5; color: #059669; }
    .status-proses { background: #fffbeb; color: #d97706; }
    .status-rusak { background: #fff7ed; color: #ea580c; }
    .status-kritis { background: #fef2f2; color: #dc2626; }

    /* TABLE HEADER - Navy #1E293B dengan teks putih */
    .table-custom th {
        background: #1E293B;
        color: white;
        font-weight: 800;
        font-size: 10px;
        letter-spacing: 0.05em;
        padding: 16px;
        text-align: center;
    }

    .table-custom td {
        padding: 14px 16px;
        font-size: 12px;
        font-weight: 500;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        text-align: center;
    }

    .table-custom tr:hover {
        background: #f8fafc;
    }

    /* TOMBOL AKSI - Ghost button / outline */
    .btn-action {
        padding: 6px 12px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 700;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }

    .btn-action:hover {
        transform: translateY(-1px);
    }

    .btn-detail {
        background: #eff6ff;
        color: #2563EB;
    }
    .btn-detail:hover {
        background: #dbeafe;
    }

    .btn-edit {
        background: #fef3c7;
        color: #d97706;
    }
    .btn-edit:hover {
        background: #fde68a;
    }

    .btn-delete {
        background: #fef2f2;
        color: #dc2626;
    }
    .btn-delete:hover {
        background: #fee2e2;
    }

    /* TOMBOL DOWNLOAD */
    .btn-download-pdf {
        background: #dc2626;
        color: white;
    }
    .btn-download-pdf:hover {
        background: #b91c1c;
    }

    .btn-download-excel {
        background: #059669;
        color: white;
    }
    .btn-download-excel:hover {
        background: #047857;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .table-custom {
        width: 100%;
        border-collapse: collapse;
    }

    .pagination-container {
        padding: 20px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: center;
    }

    /* Styling Pagination */
    .pagination {
        display: flex;
        gap: 6px;
        margin: 0;
        padding: 0;
        list-style: none;
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination li {
        display: inline-block;
    }

    .pagination a, .pagination span {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        color: #64748b;
        background: #f1f5f9;
    }

    .pagination a:hover {
        background: #e2e8f0;
        color: #334155;
        transform: translateY(-1px);
    }

    .pagination .active span {
        background: #2563EB;
        color: white;
    }

    .pagination .disabled span {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8fafc;
        color: #94a3b8;
    }

    .pagination .page-item:first-child a,
    .pagination .page-item:first-child span,
    .pagination .page-item:last-child a,
    .pagination .page-item:last-child span {
        font-size: 0;
    }

    .pagination .page-item:first-child a:before {
        content: "←";
        font-size: 14px;
    }

    .pagination .page-item:last-child a:before {
        content: "→";
        font-size: 14px;
    }

    .pagination .page-item:first-child span:before {
        content: "←";
        font-size: 14px;
    }

    .pagination .page-item:last-child span:before {
        content: "→";
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .filter-group {
            flex-direction: column;
        }
        
        .pagination a, .pagination span {
            min-width: 32px;
            height: 32px;
            font-size: 11px;
            padding: 0 8px;
        }
    }
</style>

<div class="bg-[#F8FAFC] min-h-screen p-6">
    <div class="max-w-[1400px] mx-auto">
        
        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-black tracking-tighter">
                    <span class="text-[#1E293B]">Daftar</span>
                    <span class="text-[#2563EB]">Aset</span>
                </h1>
                <p class="text-slate-500 font-medium text-xs mt-1 tracking-wider">
                    Kelola dan lihat seluruh data aset Dishub Kab. Bandung Barat
                </p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('assets.pdf') }}" class="px-5 py-2.5 bg-red-600 text-white rounded-xl font-bold text-[11px] tracking-wider hover:bg-red-700 transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="{{ route('assets.export') }}" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl font-bold text-[11px] tracking-wider hover:bg-emerald-700 transition-all shadow-sm flex items-center gap-2">
                    <i class="fas fa-file-excel"></i> EXCEL
                </a>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('assets.create', ['from' => 'list']) }}" class="px-5 py-2.5 bg-[#2563EB] text-white rounded-xl font-bold text-[11px] tracking-wider hover:bg-[#1D4ED8] transition-all shadow-sm flex items-center gap-2">
                    + Tambah Aset
                </a>
                @endif
                <a href="{{ route('assets.index') }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 rounded-xl font-bold text-[11px] tracking-wider hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                    Lihat Peta
                </a>
            </div>
        </div>

        {{-- Filter & Search --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 mb-6">
            <form method="GET" action="{{ route('assets.list') }}" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-[9px] font-black text-slate-400 tracking-wider mb-1">Cari</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama, ID, atau Alamat..." class="w-full px-4 py-2.5 bg-slate-50 border-0 rounded-xl text-sm font-medium text-slate-700 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-[#2563EB] transition-all">
                </div>
                
                <div>
                    <label class="block text-[9px] font-black text-slate-400 tracking-wider mb-1">Status Aset</label>
                    <select name="status" class="px-4 py-2.5 bg-slate-50 border-0 rounded-xl text-sm font-medium text-slate-700 cursor-pointer focus:bg-white focus:ring-2 focus:ring-[#2563EB] transition-all">
                        <option value="">Semua Status Aset</option>
                        @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-[9px] font-black text-slate-400 tracking-wider mb-1">Kategori Aset</label>
                    <select name="kategori" class="px-4 py-2.5 bg-slate-50 border-0 rounded-xl text-sm font-medium text-slate-700 cursor-pointer focus:bg-white focus:ring-2 focus:ring-[#2563EB] transition-all">
                        <option value="">Semua Kategori Aset</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ request('kategori') == $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-[9px] font-black text-slate-400 tracking-wider mb-1">Jenis Aset</label>
                    <select name="jenis" class="px-4 py-2.5 bg-slate-50 border-0 rounded-xl text-sm font-medium text-slate-700 cursor-pointer focus:bg-white focus:ring-2 focus:ring-[#2563EB] transition-all">
                        <option value="">Semua Jenis Aset</option>
                        @php
                            $jenisOptions = collect($assets->items())->pluck('jenis')->unique()->filter()->values();
                        @endphp
                        @foreach($jenisOptions as $jenis)
                        <option value="{{ $jenis }}" {{ request('jenis') == $jenis ? 'selected' : '' }}>
                            {{ $jenis }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="px-5 py-2.5 bg-[#2563EB] text-white rounded-xl font-bold text-[10px] tracking-wider hover:bg-[#1D4ED8] transition-all">
                        Filter
                    </button>
                    @if(request()->has('search') || request()->has('status') || request()->has('kategori') || request()->has('jenis'))
                    <a href="{{ route('assets.list') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 rounded-xl font-bold text-[10px] tracking-wider text-slate-600 transition-all">
                        Reset
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Aset --}}
        <div class="table-container">
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>ID Aset</th>
                            <th>Nama Aset</th>
                            <th>Kategori Aset</th>
                            <th>Jenis Aset</th>
                            <th>Status Aset</th>
                            <th>Lokasi Aset</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $asset)
                        @php
                            $statusTampil = $asset->status;
                            if ($statusTampil == 'Proses') {
                                $statusTampil = 'Proses Perbaikan';
                            }
                            
                            $statusClass = '';
                            if($asset->status == 'Baik') $statusClass = 'status-baik';
                            elseif($asset->status == 'Proses' || $asset->status == 'Proses Perbaikan') $statusClass = 'status-proses';
                            elseif($asset->status == 'Rusak') $statusClass = 'status-rusak';
                            else $statusClass = 'status-kritis';
                        @endphp
                        <tr>
                            <td class="font-mono text-[10px] font-bold text-[#2563EB]">
                                {{ $asset->id_asset ?? '#'.$asset->id }}
                            </td>
                            <td class="font-semibold text-[#1E293B]">
                                {{ $asset->nama }}
                            </td>
                            <td>
                                <span class="text-[10px] font-bold text-slate-500">
                                    {{ $asset->kategori }}
                                </span>
                            </td>
                            <td>{{ $asset->jenis }}</td>
                            <td>
                                <span class="status-badge {{ $statusClass }}">
                                    {{ $statusTampil }}
                                </span>
                            </td>
                            <td>
                                <div class="text-[10px] text-slate-500">
                                    <span class="truncate max-w-[150px] inline-block">{{ $asset->alamat ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('assets.show', $asset->id) }}" class="btn-action btn-detail">
                                        Detail
                                    </a>
                                    <a href="{{ route('assets.edit', $asset->id) }}" class="btn-action btn-edit">
                                        Edit
                                    </a>
                                    <button type="button" onclick="confirmDelete({{ $asset->id }}, '{{ addslashes($asset->nama) }}')" class="btn-action btn-delete">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-12">
                                <p class="text-slate-400 font-medium">Belum ada data aset</p>
                                @if(auth()->user()->role === 'admin')
                                <a href="{{ route('assets.create') }}" class="inline-block mt-3 text-[#2563EB] text-sm font-bold">
                                    + Tambah Aset Sekarang
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if($assets->hasPages())
            <div class="pagination-container">
                {{ $assets->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
        
        {{-- Informasi Total --}}
        <div class="mt-6 text-center text-[10px] text-slate-400 font-medium">
            Total {{ $assets->total() }} aset terdaftar
        </div>
    </div>
</div>

{{-- Form Delete (tersembunyi) --}}
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    function confirmDelete(assetId, assetName) {
        if (confirm('Apakah Anda yakin ingin menghapus aset "' + assetName + '"?\n\nTindakan ini tidak dapat dibatalkan!')) {
            const form = document.getElementById('delete-form');
            form.action = "{{ url('/admin/assets') }}/" + assetId;
            form.submit();
        }
    }
</script>

@endsection