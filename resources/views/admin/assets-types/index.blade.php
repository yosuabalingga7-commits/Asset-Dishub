@extends('layouts.app')

@section('content')
<div class="p-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-800 uppercase tracking-tight">Jenis Aset</h1>
            <p class="text-sm text-slate-500 font-medium">Detail spesifikasi tipe aset berdasarkan kategori.</p>
        </div>
        <button class="px-5 py-2.5 bg-indigo-600 text-white text-xs font-bold rounded-xl shadow-lg uppercase tracking-widest">
            + Tambah Jenis
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm hover:border-indigo-300 transition-all group">
            <div class="flex justify-between items-start mb-4">
                <div class="p-3 bg-indigo-50 rounded-2xl text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-all">
                    🏷️
                </div>
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Kategori: PJU</span>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-1">PJU LED High Mast</h3>
            <p class="text-xs text-slate-500 font-medium mb-4 italic">Penerangan area persimpangan besar.</p>
            <div class="flex justify-between items-center pt-4 border-t border-slate-100">
                <span class="text-xs font-bold text-indigo-600">12 Aset Terdaftar</span>
                <button class="text-xs font-bold text-slate-400 hover:text-amber-500">Edit</button>
            </div>
        </div>
    </div>
</div>
@endsection