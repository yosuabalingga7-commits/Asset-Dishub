@extends('layouts.app')



@section('content')



<div class="absolute top-4 left-4 z-[1000] w-72 flex flex-col gap-2">

    <div class="bg-white rounded-xl shadow-xl border border-gray-200 p-2">

        <div class="relative">

            <input type="text" placeholder="Cari Aset atau Wilayah KBB..." class="w-full pl-8 pr-4 py-2 bg-gray-100 border-none rounded-lg text-xs focus:ring-2 focus:ring-indigo-500">

            <svg class="w-4 h-4 absolute left-2.5 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>

        </div>

    </div>

</div>



<div class="absolute top-4 right-4 z-[1000] w-72 flex flex-col gap-2">

    <div class="bg-white rounded-lg shadow-md flex p-1 border border-gray-200">

        <button id="btnOperasional" class="flex-1 px-2 py-1.5 bg-indigo-600 text-white text-[10px] font-bold rounded-md uppercase transition-all">🛰️ Operasional</button>

        <button id="btnAnalisis" class="flex-1 px-2 py-1.5 bg-white text-gray-700 text-[10px] font-bold rounded-md uppercase hover:bg-gray-50 transition-all">📊 Analisis</button>

    </div>



    <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden flex flex-col">

        <div id="btnToggleList" class="bg-indigo-700 p-4 text-white cursor-pointer hover:bg-indigo-800 transition-all flex justify-between items-center">

            <div>

                <div class="text-[10px] font-bold uppercase tracking-widest opacity-80 text-white/80">Total Seluruh Aset</div>

                <div id="statTotalGlobal" class="text-3xl font-black mt-1">0</div>

            </div>

            <div id="arrowIcon" class="transition-transform duration-300">

                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7" /></svg>

            </div>

        </div>



        <div id="assetListContainer" class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out bg-gray-50 border-b border-gray-100">

            <div id="listBreadcrumb" class="hidden p-3 bg-indigo-50 border-b border-indigo-100 flex items-center gap-3 cursor-pointer hover:bg-indigo-100" onclick="resetToCategories()">

                <div class="bg-indigo-600 text-white rounded-full p-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 19l-7-7 7-7" /></svg></div>

                <div class="flex flex-col">

                    <span class="text-[9px] text-indigo-400 font-bold uppercase leading-none">Kembali</span>

                    <span id="currentCategoryTitle" class="text-[11px] font-black text-indigo-800 uppercase truncate">Kategori</span>

                </div>

            </div>

            <div id="listContent" class="p-2 space-y-1 max-h-[250px] overflow-y-auto"></div>

            <div class="p-2 border-t border-gray-100 text-center bg-white">

                <button id="resetFilter" class="w-full py-2 text-[10px] font-black text-indigo-600 uppercase tracking-tighter hover:bg-indigo-50 rounded-lg transition-colors border border-dashed border-indigo-200">🔄 Reset & Tampilkan Semua</button>

            </div>

        </div>

    </div>



    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4">

        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">

            <h4 class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Kondisi Aset</h4>

            <span id="currentViewLabel" class="text-[9px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded uppercase">Seluruh Aset</span>

        </div>

        <div class="grid grid-cols-2 gap-2">

            <div onclick="renderMap('all', 'all', 'Baik')" class="flex justify-between items-center text-[10px] font-bold p-1.5 bg-gray-50 rounded cursor-pointer hover:bg-green-50 transition-colors">

                <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-500"></span><span class="text-gray-600 uppercase">Baik</span></div>

                <span id="statBaik" class="text-gray-900">0</span>

            </div>

            <div onclick="renderMap('all', 'all', 'Rusak')" class="flex justify-between items-center text-[10px] font-bold p-1.5 bg-gray-50 rounded cursor-pointer hover:bg-orange-50 transition-colors">

                <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-orange-400"></span><span class="text-gray-600 uppercase">Rusak</span></div>

                <span id="statRusak" class="text-gray-900">0</span>

            </div>

            <div onclick="renderMap('all', 'all', 'Kritis')" class="flex justify-between items-center text-[10px] font-bold p-1.5 bg-gray-50 rounded cursor-pointer hover:bg-red-50 transition-colors">

                <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-red-600"></span><span class="text-gray-600 uppercase">Kritis</span></div>

                <span id="statKritis" class="text-gray-900">0</span>

            </div>

            <div onclick="renderMap('all', 'all', 'Perbaikan')" class="flex justify-between items-center text-[10px] font-bold p-1.5 bg-gray-50 rounded cursor-pointer hover:bg-blue-50 transition-colors">

                <div class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-blue-500"></span><span class="text-gray-600 uppercase">Proses</span></div>

                <span id="statPerbaikan" class="text-gray-900">0</span>

            </div>

        </div>

    </div>

</div>



<div id="detailDashboard" class="absolute bottom-6 left-6 z-[1000] w-80 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden hidden translate-y-10 transition-all duration-300">

    <div class="relative h-32 bg-gray-200">

        <img id="detFoto" src="" class="w-full h-full object-cover">

        <div id="detStatusBadge" class="absolute top-3 right-3 px-2 py-1 rounded-md text-[9px] font-black uppercase text-white shadow-lg"></div>

    </div>

    <div class="p-4">

        <div class="mb-3">

            <h3 id="detNama" class="font-black text-gray-800 uppercase text-sm leading-tight">Nama Aset</h3>

           

            <div class="mt-2 flex flex-col gap-0.5">

                <span class="text-[8px] text-gray-400 font-bold uppercase tracking-widest">Lokasi Lengkap:</span>

                <div class="flex gap-1.5 items-start">

                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />

                    </svg>

                    <p id="detAlamat" class="text-[10px] text-gray-600 font-bold leading-relaxed">---</p>

                </div>

            </div>

        </div>

       

        <div class="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-gray-100">

            <div>

                <span class="text-[8px] text-gray-400 font-bold uppercase block tracking-tighter">Kategori</span>

                <span id="detKategori" class="text-[10px] font-bold text-gray-700">---</span>

            </div>

            <div>

                <span class="text-[8px] text-gray-400 font-bold uppercase block tracking-tighter">Jenis</span>

                <span id="detJenis" class="text-[10px] font-bold text-gray-700">---</span>

            </div>

            <div class="col-span-2">

                <span class="text-[8px] text-gray-400 font-bold uppercase block tracking-tighter">Update Terakhir (Log)</span>

                <span id="detUpdate" class="text-[10px] font-bold text-indigo-600 leading-none">---</span>

            </div>

        </div>

    </div>

</div>



<div id="map" class="w-full h-full bg-slate-100"></div>



<script>

document.addEventListener("DOMContentLoaded", function () {

    const initialCenter = [-6.9175, 107.6191];

    const initialZoom = 13;

    let currentMode = 'operasional';



    // DATA DENGAN ALAMAT LENGKAP (Jl. ...)

    const asetDishub = [

        { kategori: "Fasilitas Lalu Lintas", jenis: "Lampu Lalu Lintas", nama: "TL Asia Afrika", alamat: "Jl. Asia Afrika, Simpang Lima, Kec. Sumur Bandung, Kota Bandung, Jawa Barat 40111", lat: -6.9147, lng: 107.6098, status: "Kritis", warna: "#dc2626", update: "10-02-2026 14:00", foto: "https://images.unsplash.com/photo-1545156521-77bd85671d30?q=80&w=400" },

        { kategori: "Penerangan Jalan Umum", jenis: "Lampu Jalan (PJU)", nama: "PJU Dago", alamat: "Jl. Ir. H. Juanda No.100, RS Borromeus, Coblong, Kota Bandung", lat: -6.9198, lng: 107.6235, status: "Baik", warna: "#16a34a", update: "10-02-2026 09:30", foto: "https://images.unsplash.com/photo-1518005020250-675c0c071115?q=80&w=400" },

        { kategori: "Perlengkapan Jalan", jenis: "Halte", nama: "Halte Gasibu", alamat: "Jl. Diponegoro No.22, Citarum, Kec. Bandung Wetan, Kota Bandung", lat: -6.9001, lng: 107.6185, status: "Perbaikan", warna: "#3b82f6", update: "09-02-2026 16:45", foto: "https://images.unsplash.com/photo-1570129477492-45c003edd2be?q=80&w=400" },

        { kategori: "Fasilitas Lalu Lintas", jenis: "Lampu Lalu Lintas", nama: "TL Cihampelas", alamat: "Jl. Cihampelas No.160, Cipaganti, Kec. Coblong, Kota Bandung", lat: -6.8950, lng: 107.6030, status: "Rusak", warna: "#fb923c", update: "08-02-2026 11:20", foto: "https://images.unsplash.com/photo-1545156521-77bd85671d30?q=80&w=400" },

        { kategori: "Fasilitas Lalu Lintas", jenis: "Lampu Lalu Lintas", nama: "TL Ciwaruga", alamat: "Jl. Ciwaruga No.12, Ciwaruga, Kec. Parongpong, Kabupaten Bandung Barat", lat: -6.8654, lng: 107.5765, status: "Baik", warna: "#16a34a", update: "11-02-2026 08:00", foto: "https://images.unsplash.com/photo-1545156521-77bd85671d30?q=80&w=400" }

    ];



    const googleRoadmap = L.tileLayer("https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}", { maxZoom: 20 });

    const googleSatelite = L.tileLayer("https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}", { maxZoom: 22 });



    const map = L.map('map', {

        zoomControl: false,

        attributionControl: false,

        layers: [googleRoadmap]

    }).setView(initialCenter, initialZoom);



    const baseMaps = { "Peta Jalan": googleRoadmap, "Satelit": googleSatelite };

    L.control.layers(baseMaps, null, { position: 'bottomright' }).addTo(map);



    const operationalLayer = L.layerGroup().addTo(map);



    function renderMap(katFilter = 'all', jenFilter = 'all', statusFilter = 'all') {

        operationalLayer.clearLayers();

        const bounds = [];

        let stats = { total: 0, baik: 0, rusak: 0, kritis: 0, perbaikan: 0 };



        asetDishub.forEach(aset => {

            const s = aset.status.toLowerCase();