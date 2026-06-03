@extends('layouts.app')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<style>
    /* Custom Dropdown Styles */
    .custom-dropdown {
        position: relative;
        width: 100%;
    }
    
    .dropdown-selected {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        background-color: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .dropdown-selected:hover {
        border-color: #cbd5e1;
    }
    
    .dropdown-selected .selected-text {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .dropdown-selected .selected-text i {
        width: 1.25rem;
        font-size: 1rem;
        color: #64748b;
    }
    
    .dropdown-selected i.chevron {
        color: #64748b;
        font-size: 0.75rem;
        transition: transform 0.2s;
    }
    
    .dropdown-selected.open .chevron {
        transform: rotate(180deg);
    }
    
    .dropdown-options {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        margin-top: 0.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        z-index: 50;
        max-height: 250px;
        overflow-y: auto;
        display: none;
    }
    
    .dropdown-options.open {
        display: block;
    }
    
    .dropdown-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.15s;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .dropdown-option:last-child {
        border-bottom: none;
    }
    
    .dropdown-option:hover {
        background-color: #f1f5f9;
    }
    
    .dropdown-option.selected {
        background-color: #e0e7ff;
        color: #4f46e5;
    }
    
    .dropdown-option i {
        width: 1.25rem;
        font-size: 1rem;
        color: #64748b;
    }
    
    .dropdown-option.selected i {
        color: #4f46e5;
    }

    /* Asset Dropdown Styles */
    .asset-dropdown {
        position: relative;
        width: 100%;
    }
    
    .asset-dropdown-selected {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        background-color: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .asset-dropdown-selected:hover {
        border-color: #cbd5e1;
    }
    
    .asset-dropdown-options {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        margin-top: 0.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        z-index: 50;
        max-height: 250px;
        overflow-y: auto;
        display: none;
    }
    
    .asset-dropdown-options.open {
        display: block;
    }
    
    .asset-dropdown-option {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
        transition: all 0.15s;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .asset-dropdown-option:last-child {
        border-bottom: none;
    }
    
    .asset-dropdown-option:hover {
        background-color: #f1f5f9;
    }
</style>

<div class="min-h-screen relative font-['Inter'] pb-20 bg-[#F8FAFC]">
    
    {{-- HEADER - Background Navy Gelap #1E293B --}}
    <div class="bg-[#1E293B] pt-12 pb-32 px-4 text-center relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <svg width="100%" height="100%"><pattern id="grid" width="30" height="30" patternUnits="userSpaceOnUse"><path d="M 30 0 L 0 0 0 30" fill="none" stroke="white" stroke-width="0.5"/></pattern><rect width="100%" height="100%" fill="url(#grid)" /></svg>
        </div>
        <div class="relative z-10">
            <h1 class="text-white text-3xl md:text-4xl font-extrabold mt-3 tracking-tight">INPUT LAPORAN <span class="text-[#3B82F6]">TEKNIS</span></h1>
            <p class="text-slate-300 text-xs mt-2">Petugas Lapangan - Dinas Perhubungan Kabupaten Bandung Barat</p>
        </div>
    </div>

    {{-- FORM AREA --}}
    <div class="max-w-6xl mx-auto -mt-20 px-4 relative z-20">
        <form action="{{ route('laporan.petugas.store') }}" method="POST" enctype="multipart/form-data" id="laporanForm">
            @csrf
            <input type="hidden" name="asset_id" id="selected_asset_id">
            <input type="hidden" name="id_asset" id="selected_id_asset">
            <input type="hidden" name="judul_laporan" id="judul_laporan_hidden">

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                
                {{-- SISI KIRI: IDENTITAS PETUGAS (OTOMATIS DARI LOGIN) --}}
                <div class="lg:col-span-7 space-y-5">
                    <div class="bg-white rounded-2xl shadow-xl p-8 border border-slate-100">
                        
                        {{-- SECTION 1: DATA PETUGAS (OTOMATIS) --}}
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-8 h-8 bg-[#2563EB] text-white rounded-xl flex items-center justify-center font-bold text-sm">01</div>
                            <h3 class="font-bold text-[#1E293B] text-sm tracking-wider">Identitas Petugas</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="text-[10px] font-bold text-slate-500 ml-1">Nama Petugas</label>
                                <input type="text" name="nama_pelapor" value="{{ Auth::user()->name }}" readonly class="w-full bg-slate-100 border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-[#1E293B] cursor-not-allowed">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-500 ml-1">NIP</label>
                                <input type="text" name="nip" value="{{ Auth::user()->nip }}" readonly class="w-full bg-slate-100 border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-[#1E293B] cursor-not-allowed">
                            </div>
                        </div>

                        <div class="mb-8">
                            <label class="text-[10px] font-bold text-slate-500 ml-1">WhatsApp Aktif</label>
                            <input type="tel" name="kontak_pelapor" value="{{ Auth::user()->no_wa }}" readonly class="w-full bg-slate-100 border border-slate-200 rounded-xl py-3 px-4 text-sm font-semibold text-[#1E293B] cursor-not-allowed">
                        </div>

                        <div class="border-t border-slate-100 pt-8 space-y-4">
                            {{-- UPLOAD PHOTO --}}
                            <div>
                                <label class="text-[10px] font-bold text-slate-500 ml-1">Foto Dokumentasi</label>
                                <div class="relative group mt-1">
                                    <div class="w-full py-10 bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl flex flex-col items-center justify-center transition-all group-hover:border-[#2563EB] group-hover:bg-blue-50/50">
                                        <div class="w-14 h-14 bg-white shadow-lg rounded-2xl flex items-center justify-center text-2xl mb-3"><i class="fas fa-camera text-slate-400 text-2xl"></i></div>
                                        <p class="text-[11px] font-black text-[#1E293B] uppercase tracking-wide">Klik Untuk Ambil Gambar</p>
                                        <input type="file" name="foto" id="foto_kamera" accept="image/*" capture="user" class="absolute inset-0 opacity-0 cursor-pointer" required onchange="previewImage(event)">
                                    </div>
                                </div>
                                <div id="image_preview_container" class="mt-3 hidden">
                                    <img id="image_preview" src="#" class="w-full h-40 object-cover rounded-xl border-2 border-white shadow-md">
                                </div>
                            </div>

                            {{-- KONDISI - CUSTOM DROPDOWN --}}
                            <div>
                                <label class="text-[10px] font-bold text-slate-500 ml-1">Kategori Kondisi</label>
                                <div class="custom-dropdown mt-1" id="kondisiDropdown">
                                    <div class="dropdown-selected" onclick="toggleKondisiDropdown()">
                                        <div class="selected-text">
                                            <i class="fas fa-circle-info"></i>
                                            <span id="selectedKondisiText" class="text-slate-400">Pilih Salah Satu</span>
                                        </div>
                                        <i class="fas fa-chevron-down chevron"></i>
                                    </div>
                                    <div class="dropdown-options" id="kondisiOptions">
                                        <div class="dropdown-option" data-value="Rusak" onclick="selectKondisi('Rusak', 'RUSAK', 'fa-triangle-exclamation')">
                                            <i class="fas fa-triangle-exclamation"></i>
                                            <span>RUSAK</span>
                                        </div>
                                        <div class="dropdown-option" data-value="Hilang/Dicuri" onclick="selectKondisi('Hilang/Dicuri', 'HILANG / DICURI', 'fa-ban')">
                                            <i class="fas fa-ban"></i>
                                            <span>HILANG / DICURI</span>
                                        </div>
                                        <div class="dropdown-option" data-value="Pindah Posisi" onclick="selectKondisi('Pindah Posisi', 'PINDAH POSISI', 'fa-location-dot')">
                                            <i class="fas fa-location-dot"></i>
                                            <span>PINDAH POSISI</span>
                                        </div>
                                        <div class="dropdown-option" data-value="Lainnya" onclick="selectKondisi('Lainnya', 'LAINNYA', 'fa-ellipsis-h')">
                                            <i class="fas fa-ellipsis-h"></i>
                                            <span>LAINNYA</span>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="kondisi_aset" id="kondisi_aset_input" value="">
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-slate-500 ml-1">Catatan Kronologi</label>
                                <textarea name="deskripsi" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-3 px-4 text-sm outline-none focus:border-[#2563EB] focus:bg-white transition-all" placeholder="Tambahkan catatan teknis..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SISI KANAN: PETA & DETEKSI ASET --}}
                <div class="lg:col-span-5 space-y-5">
                    <div class="bg-white rounded-2xl shadow-xl p-6 border border-slate-100">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-8 h-8 bg-[#2563EB] text-white rounded-xl flex items-center justify-center font-bold text-sm">02</div>
                            <h3 class="font-bold text-[#1E293B] text-sm tracking-wider">Lokasi Kejadian</h3>
                        </div>

                        <div class="flex flex-wrap gap-3 mb-4">
                            <button type="button" id="btnAktifkanGPS" class="bg-[#2563EB] text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wide shadow-md hover:bg-[#1D4ED8] transition-all">
                                <i class="fas fa-satellite-dish mr-2"></i> Aktifkan GPS
                            </button>
                            <button type="button" id="btnToggleKunci" class="bg-amber-500 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-wide shadow-md hover:bg-amber-600 transition-all">
                                <i class="fas fa-lock mr-2"></i> Kunci Lokasi
                            </button>
                        </div>

                        <div class="relative rounded-xl overflow-hidden border border-slate-200" style="height: 380px;">
                            <div id="miniMap" class="w-full h-full bg-slate-200"></div>
                        </div>

                        {{-- KETERANGAN TITIK ASET --}}
                        <div class="mt-3 text-center bg-blue-50/80 rounded-xl py-2 px-4 border border-blue-200">
                            <div class="flex items-center justify-center gap-2">
                                <div style="background-color: #F4B400; width: 20px; height: 20px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; border: 2px solid #0B2A4A; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                                    <i class="fas fa-box" style="font-size: 9px; color: #0B2A4A;"></i>
                                </div>
                                <span class="font-black text-blue-800 text-[10px]">= Lokasi aset terdaftar dalam radius 100 meter dari posisi Anda</span>
                            </div>
                        </div>

                        <div class="mt-3 px-4 py-3 bg-blue-50 rounded-xl border border-blue-100">
                            <div class="flex items-start gap-3">
                                <div class="w-2.5 h-2.5 mt-1 bg-red-600 rounded-full animate-pulse flex-shrink-0"></div>
                                <p class="text-[10px] font-black text-[#1E293B] leading-relaxed italic" id="alamatText">Belum ada lokasi dipilih</p>
                            </div>
                        </div>

                        {{-- SISTEM DETEKSI ASET OTOMATIS --}}
                        <div class="mt-4 space-y-3 bg-blue-50 rounded-xl p-4 border border-blue-100">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-location-dot text-blue-600"></i>
                                <label class="text-[10px] font-black text-blue-800 uppercase tracking-wide">Sistem Deteksi Aset Otomatis</label>
                            </div>
                            <p class="text-[10px] text-blue-700 font-semibold">
                                Aktifkan GPS atau klik di peta. Sistem akan mendeteksi aset dalam radius 100 meter.
                            </p>
                            
                            {{-- DROPDOWN PILIH ASET --}}
                            <div class="mt-2" id="assetDropdownContainer" style="display: none;">
                                <label class="text-[10px] font-black text-blue-800 uppercase tracking-wide">Pilih Aset yang Dilaporkan</label>
                                <div class="asset-dropdown mt-2" id="assetDropdown">
                                    <div class="asset-dropdown-selected" onclick="toggleAssetDropdown()">
                                        <div class="selected-text">
                                            <i class="fas fa-building"></i>
                                            <span id="selectedAssetText">Pilih Aset</span>
                                        </div>
                                        <i class="fas fa-chevron-down chevron"></i>
                                    </div>
                                    <div class="asset-dropdown-options" id="assetDropdownOptions">
                                    </div>
                                </div>
                                <p class="text-[8px] text-blue-500 mt-1" id="assetCountInfo"></p>
                            </div>
                            <div class="mt-2" id="noAssetWarning" style="display: none;">
                                <p class="text-[10px] text-amber-600 font-semibold">⚠️ Tidak ada aset terdaftar dalam radius 100 meter dari lokasi Anda.</p>
                            </div>
                            
                            <div class="flex items-center gap-2 mt-2">
                                <i class="fas fa-ruler-combined text-blue-500 text-xs"></i>
                                <span class="text-[8px] text-blue-600 font-bold">Radius deteksi: 100 meter</span>
                            </div>
                        </div>

                        <input type="hidden" name="lat" id="inp_lat">
                        <input type="hidden" name="lng" id="inp_lng">
                        <input type="hidden" name="alamat" id="inp_alamat">

                        {{-- TOMBOL KIRIM --}}
                        <button type="submit" id="submitBtn" class="w-full mt-6 bg-[#2563EB] hover:bg-[#1D4ED8] text-white py-4 rounded-xl text-[11px] font-black uppercase tracking-wide shadow-lg shadow-blue-500/30 transition-all active:scale-95">
                            <span class="flex items-center justify-center gap-2"><i class="fas fa-paper-plane"></i> Kirim Laporan Resmi</span>
                        </button>
                    </div>

                    <div class="flex flex-col items-center mt-2 gap-2">
                        <img src="{{ asset('img/logo_dishub_kbb.png') }}" class="h-16">
                        <p class="text-[7px] text-slate-400 font-black uppercase tracking-[0.3em]">Dishub Kabupaten Bandung Barat • 2026</p>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
    // Variabel global
    let map, marker, radiusCircle;
    const radiusMeters = 100;
    let alamatTerkunci = false;
    let daftarAset = [];
    let selectedAssetId = null;
    let selectedAssetIdAsset = null;
    let selectedAssetNama = '';
    let assetMarkers = [];
    let currentLat = null;
    let currentLng = null;
    
    // Peta CartoDB Voyager (Light Mode)
    const lightMapLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20,
        minZoom: 0
    });
    
    // Fungsi toggle dropdown kondisi
    function toggleKondisiDropdown() {
        const options = document.getElementById('kondisiOptions');
        const selected = document.querySelector('#kondisiDropdown .dropdown-selected');
        if (options && selected) {
            options.classList.toggle('open');
            selected.classList.toggle('open');
        }
    }
    
    function selectKondisi(value, text, iconClass) {
        document.getElementById('kondisi_aset_input').value = value;
        
        const selectedDiv = document.querySelector('#kondisiDropdown .dropdown-selected');
        const selectedTextDiv = selectedDiv ? selectedDiv.querySelector('.selected-text') : null;
        if (selectedTextDiv) {
            selectedTextDiv.innerHTML = `<i class="fas ${iconClass}"></i><span id="selectedKondisiText" class="text-slate-800">${text}</span>`;
        }
        
        const options = document.querySelectorAll('#kondisiOptions .dropdown-option');
        options.forEach(opt => opt.classList.remove('selected'));
        
        const selectedOption = Array.from(options).find(opt => opt.dataset.value === value);
        if (selectedOption) selectedOption.classList.add('selected');
        
        const dropdownOptions = document.getElementById('kondisiOptions');
        const selectedBtn = document.querySelector('#kondisiDropdown .dropdown-selected');
        if (dropdownOptions) dropdownOptions.classList.remove('open');
        if (selectedBtn) selectedBtn.classList.remove('open');
    }
    
    function toggleAssetDropdown() {
        const options = document.getElementById('assetDropdownOptions');
        if (options) options.classList.toggle('open');
    }
    
    // Pilih aset dari dropdown ATAU dari klik marker
    function pilihAset(assetId, assetIdAsset, assetNama) {
        console.log('pilihAset dipanggil:', { assetId, assetIdAsset, assetNama });
        
        selectedAssetId = assetId;
        selectedAssetIdAsset = assetIdAsset;
        selectedAssetNama = assetNama;
        
        // Set hidden inputs
        document.getElementById('selected_asset_id').value = assetId;
        document.getElementById('selected_id_asset').value = assetIdAsset;
        document.getElementById('judul_laporan_hidden').value = assetNama;
        
        // Update tampilan dropdown
        const selectedAssetText = document.getElementById('selectedAssetText');
        if (selectedAssetText) {
            selectedAssetText.innerHTML = `<i class="fas fa-check-circle text-green-600"></i> ${assetNama}`;
        }
        
        // Tutup dropdown options
        const dropdownOptions = document.getElementById('assetDropdownOptions');
        if (dropdownOptions) {
            dropdownOptions.classList.remove('open');
        }
    }
    
    // Cari aset terdekat via AJAX
    async function cariAsetTerdekat(lat, lng) {
        if (alamatTerkunci) return;
        
        try {
            const response = await fetch(`/laporan/aset-terdekat-petugas?lat=${lat}&lng=${lng}&radius=${radiusMeters}`);
            const data = await response.json();
            daftarAset = data.aset || [];
            
            // Hapus marker aset lama
            assetMarkers.forEach(marker => marker.remove());
            assetMarkers = [];
            
            // Tambah marker aset baru - TANPA POPUP
            daftarAset.forEach(aset => {
                const assetIcon = L.divIcon({
                    className: 'asset-marker-icon',
                    html: `<div style="background-color: #F4B400; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #0B2A4A; box-shadow: 0 2px 5px rgba(0,0,0,0.2); cursor: pointer;"><i class="fas fa-box" style="font-size: 12px; color: #0B2A4A;"></i></div>`,
                    iconSize: [28, 28],
                    iconAnchor: [14, 14]
                });
                
                const m = L.marker([aset.lat, aset.lng], { icon: assetIcon }).addTo(map);
                
                // TAMBAHKAN TIDAK ADA POPUP - LANGSUNG PILIH ASET SAAT DI KLIK
                m.on('click', function() {
                    pilihAset(aset.id, aset.id_asset, aset.nama);
                });
                assetMarkers.push(m);
            });
            
            // Update dropdown
            const dropdownContainer = document.getElementById('assetDropdownContainer');
            const noAssetWarning = document.getElementById('noAssetWarning');
            const assetCountInfo = document.getElementById('assetCountInfo');
            const dropdownOptions = document.getElementById('assetDropdownOptions');
            const selectedAssetText = document.getElementById('selectedAssetText');
            
            if (daftarAset.length > 0) {
                dropdownContainer.style.display = 'block';
                noAssetWarning.style.display = 'none';
                
                let optionsHtml = '';
                daftarAset.forEach(aset => {
                    const isSelected = (selectedAssetId == aset.id);
                    const selectedStyle = isSelected ? 'style="background-color: #e0e7ff;"' : '';
                    optionsHtml += `
                        <div class="asset-dropdown-option" onclick="pilihAset(${aset.id}, '${aset.id_asset}', '${aset.nama.replace(/'/g, "\\'")}')" ${selectedStyle}>
                            <i class="fas fa-box"></i>
                            <div class="flex flex-col">
                                <span class="font-bold text-[#1E293B]">${aset.nama}</span>
                                <span class="text-[9px] text-slate-500">${aset.id_asset}</span>
                            </div>
                        </div>
                    `;
                });
                dropdownOptions.innerHTML = optionsHtml;
                
                // Jika hanya 1 aset dan belum ada yang dipilih, pilih otomatis
                if (daftarAset.length === 1 && !selectedAssetId) {
                    pilihAset(daftarAset[0].id, daftarAset[0].id_asset, daftarAset[0].nama);
                }
                
                if (daftarAset.length > 1) {
                    assetCountInfo.innerText = `Terdapat ${daftarAset.length} aset dalam radius ini. Silakan pilih yang sesuai.`;
                    assetCountInfo.style.display = 'block';
                } else {
                    assetCountInfo.style.display = 'none';
                }
            } else {
                dropdownContainer.style.display = 'none';
                noAssetWarning.style.display = 'block';
                // Reset pilihan aset jika tidak ada aset
                if (selectedAssetId) {
                    selectedAssetId = null;
                    selectedAssetIdAsset = null;
                    selectedAssetNama = '';
                    document.getElementById('selected_asset_id').value = '';
                    document.getElementById('selected_id_asset').value = '';
                    document.getElementById('judul_laporan_hidden').value = '';
                    if (selectedAssetText) selectedAssetText.innerHTML = '<i class="fas fa-building"></i> Pilih Aset';
                }
            }
        } catch (error) {
            console.error('Gagal mencari aset terdekat:', error);
        }
    }
    
    async function updatePosition(lat, lng) {
        if (alamatTerkunci) return;
        
        currentLat = lat;
        currentLng = lng;
        
        if (marker) {
            marker.setLatLng([lat, lng]);
            map.panTo([lat, lng]);
        }
        
        if (radiusCircle) {
            radiusCircle.setLatLng([lat, lng]);
        }
        
        document.getElementById('inp_lat').value = lat;
        document.getElementById('inp_lng').value = lng;
        document.getElementById('alamatText').innerText = "MENDAPATKAN ALAMAT...";
        
        try {
            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
            const data = await response.json();
            const fullAddress = data.display_name || "LOKASI TIDAK TERJANGKAU";
            document.getElementById('alamatText').innerText = fullAddress.toUpperCase();
            document.getElementById('inp_alamat').value = fullAddress;
        } catch (e) { 
            const fallback = `KOORDINAT: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            document.getElementById('alamatText').innerText = fallback;
            document.getElementById('inp_alamat').value = fallback;
        }
        
        await cariAsetTerdekat(lat, lng);
    }
    
    function previewImage(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('image_preview');
            output.src = reader.result;
            document.getElementById('image_preview_container').classList.remove('hidden');
        }
        reader.readAsDataURL(file);
    }
    
    document.addEventListener("DOMContentLoaded", function () {
        map = L.map('miniMap', { zoomControl: true, attributionControl: false }).setView([-6.8431, 107.4912], 16);
        lightMapLayer.addTo(map);
        L.control.zoom({ position: 'topright' }).addTo(map);
        
        const customIcon = L.divIcon({
            className: 'custom-marker',
            html: "<div class='marker-pin'></div>",
            iconSize: [30, 30], iconAnchor: [15, 30]
        });
        
        marker = L.marker([-6.8431, 107.4912], { draggable: true, icon: customIcon }).addTo(map);
        
        radiusCircle = L.circle([-6.8431, 107.4912], {
            color: '#14b8a6',
            fillColor: '#14b8a6',
            fillOpacity: 0.15,
            radius: radiusMeters,
            weight: 3,
            opacity: 0.8,
            className: 'radius-circle-glow'
        }).addTo(map);
        
        map.on('click', function(e) {
            if (!alamatTerkunci) updatePosition(e.latlng.lat, e.latlng.lng);
        });
        
        marker.on('dragend', function() {
            if (!alamatTerkunci) {
                let pos = marker.getLatLng();
                updatePosition(pos.lat, pos.lng);
            }
        });
        
        document.getElementById('btnAktifkanGPS').addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    updatePosition(pos.coords.latitude, pos.coords.longitude);
                    map.setView([pos.coords.latitude, pos.coords.longitude], 18);
                }, function(error) {
                    alert('Gagal mengakses GPS. Pastikan GPS menyala.');
                }, { enableHighAccuracy: true });
            } else {
                alert('Browser tidak mendukung GPS');
            }
        });
        
        const toggleBtn = document.getElementById('btnToggleKunci');
        toggleBtn.addEventListener('click', function() {
            if (alamatTerkunci) {
                alamatTerkunci = false;
                toggleBtn.innerHTML = '<i class="fas fa-lock mr-2"></i> Kunci Lokasi';
                toggleBtn.classList.remove('bg-gray-600');
                toggleBtn.classList.add('bg-amber-500');
                alert('Lokasi sudah dibuka. Posisi dapat berubah.');
            } else {
                if (currentLat && currentLng) {
                    alamatTerkunci = true;
                    toggleBtn.innerHTML = '<i class="fas fa-lock-open mr-2"></i> Buka Kunci';
                    toggleBtn.classList.remove('bg-amber-500');
                    toggleBtn.classList.add('bg-gray-600');
                    alert('Lokasi telah dikunci. Posisi tidak akan berubah.');
                } else {
                    alert('Silakan aktifkan GPS atau pilih lokasi di peta terlebih dahulu');
                }
            }
        });
        
        document.getElementById('laporanForm').addEventListener('submit', function(e) {
            // Validasi aset harus dipilih
            if (!selectedAssetId) {
                e.preventDefault();
                alert('Silakan pilih aset yang dilaporkan terlebih dahulu!');
                return false;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="flex items-center justify-center gap-2"><svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Memproses Laporan...</span>';
        });
    });
    
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('kondisiDropdown');
        if (dropdown && !dropdown.contains(event.target)) {
            const options = document.getElementById('kondisiOptions');
            const selected = document.querySelector('#kondisiDropdown .dropdown-selected');
            if (options) options.classList.remove('open');
            if (selected) selected.classList.remove('open');
        }
        
        const assetDropdown = document.getElementById('assetDropdown');
        if (assetDropdown && !assetDropdown.contains(event.target)) {
            const options = document.getElementById('assetDropdownOptions');
            if (options) options.classList.remove('open');
        }
    });
</script>

<style>
    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .animate-marquee {
        animation: marquee 30s linear infinite;
    }
    .marker-pin {
        width: 24px;
        height: 24px;
        border-radius: 50% 50% 50% 0;
        background: #0B2A4A;
        position: absolute;
        transform: rotate(-45deg);
        left: 50%;
        top: 50%;
        margin: -24px 0 0 -12px;
        border: 3px solid #F4B400;
        box-shadow: 0 10px 15px rgba(0,0,0,0.4);
    }
    .leaflet-container { background: #f1f5f9 !important; border-radius: 1rem; }
    input::placeholder { color: #94a3b8 !important; font-size: 11px; }
    html { scroll-behavior: smooth; }
    
    .radius-circle-glow {
        stroke-dasharray: 8 12;
        animation: rotateDash 4s linear infinite, pulseGlow 2s ease-in-out infinite;
        filter: drop-shadow(0 0 5px rgba(20, 184, 166, 0.5));
    }
    
    @keyframes rotateDash {
        to { stroke-dashoffset: -40; }
    }
    
    @keyframes pulseGlow {
        0% { stroke-opacity: 0.6; fill-opacity: 0.1; stroke-width: 2; }
        50% { stroke-opacity: 1; fill-opacity: 0.25; stroke-width: 4; }
        100% { stroke-opacity: 0.6; fill-opacity: 0.1; stroke-width: 2; }
    }
    
    .asset-marker-icon div {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .asset-marker-icon div:hover {
        transform: scale(1.15);
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }
</style>
@endsection