@extends('layouts.app')

@section('content')
<div class="p-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Kategori Aset</h1>
            <p class="text-sm text-slate-500 font-medium">Pengelompokan besar inventaris Dishub KBB.</p>
        </div>
        <button class="px-5 py-2.5 bg-indigo-600 text-white text-xs font-bold rounded-xl shadow-lg hover:bg-indigo-700 transition-all  tracking-widest">
            + Tambah Kategori
        </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50/50 border-b border-slate-200">
                    <th class="p-5 text-[10px] font-black text-slate-400 tracking-widest">No</th>
                    <th class="p-5 text-[10px] font-black text-slate-400 tracking-widest">Nama Kategori</th>
                    <th class="p-5 text-[10px] font-black text-slate-400 tracking-widest">Kode</th>
                    <th class="p-5 text-[10px] font-black text-slate-400  tracking-widest text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="p-5 text-sm font-bold text-slate-400">01</td>
                    <td class="p-5">
                        <span class="text-sm font-bold text-slate-700">Penerangan Jalan Umum</span>
                    </td>
                    <td class="p-5 text-sm font-mono text-indigo-500 font-bold">PJU</td>
                    <td class="p-5 flex justify-center gap-3">
                        <button class="p-2 bg-amber-50 text-amber-600 rounded-lg hover:bg-amber-100 transition-colors text-xs">Edit</button>
                        <button class="p-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors text-xs">Hapus</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection