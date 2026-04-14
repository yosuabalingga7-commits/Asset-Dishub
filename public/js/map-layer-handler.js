/**
 * Map Layer Handler - GIS Asset Management
 * Menangani logika tampilan layer tanpa refresh halaman dengan Clustering
 * Update: Menambahkan Marker Cluster untuk efisiensi tampilan aset
 * Lokasi File: G:\Kerja\Dishub_kbb\public\js\map-layer-handler.js
 */

// 1. Inisialisasi Group Layer secara Global menggunakan MarkerClusterGroup
// Kita menggunakan MarkerClusterGroup agar aset yang berdekatan ngumpul jadi angka
window.mapLayers = {
    pju: L.markerClusterGroup({ 
        disableClusteringAtZoom: 16, // Zoom 16 ke atas angka hilang, jadi ikon satuan
        maxClusterRadius: 50, // Jarak marker untuk mulai ngumpul
        animate: true 
    }),
    rambu: L.markerClusterGroup({ 
        disableClusteringAtZoom: 16, 
        maxClusterRadius: 50, 
        animate: true 
    }),
    apill: L.markerClusterGroup({ 
        disableClusteringAtZoom: 16, 
        maxClusterRadius: 50, 
        animate: true 
    }),
    cctv: L.markerClusterGroup({ 
        disableClusteringAtZoom: 16, 
        maxClusterRadius: 50, 
        animate: true 
    }),
    halte: L.markerClusterGroup({ 
        disableClusteringAtZoom: 16, 
        maxClusterRadius: 50, 
        animate: true 
    }),
    marka: L.markerClusterGroup({ 
        disableClusteringAtZoom: 16, 
        maxClusterRadius: 50, 
        animate: true 
    })
};

/**
 * Fungsi untuk mengisi data ke dalam layer
 * @param {L.Map} map - Instance map leaflet
 * @param {Array} dataAset - Array objek data dari database
 */
window.initAssetLayers = function(map, dataAset) {
    // Pastikan map tersimpan di window agar bisa diakses fungsi toggle
    window.mainMap = map;

    // Bersihkan semua layer jika sebelumnya sudah ada marker
    Object.values(window.mapLayers).forEach(layer => layer.clearLayers());

    // Loop data aset dan masukkan ke group masing-masing
    dataAset.forEach(item => {
        // Tentukan Marker
        const assetMarker = L.marker([item.latitude, item.longitude])
            .bindPopup(`
                <div class="p-2 font-['Inter']">
                    <p class="text-[10px] font-black uppercase text-slate-400 mb-1">${item.kategori}</p>
                    <h3 class="text-sm font-black text-[#0B2A4A] mb-2">${item.nama}</h3>
                    <div class="space-y-1 text-[11px]">
                        <p><b>Status:</b> <span class="text-emerald-600 font-bold">Baik</span></p>
                        <p><b>Lokasi:</b> ${item.lokasi || '-'}</p>
                    </div>
                    <hr class="my-2 border-slate-100">
                    <a href="/admin/aset/detail/${item.id}" class="block text-center bg-[#0B2A4A] text-white py-2 rounded-lg text-[10px] font-black uppercase tracking-widest">Detail Aset</a>
                </div>
            `);

        // Masukkan ke layer group sesuai kategorinya
        const kategori = item.kategori.toLowerCase();
        if (window.mapLayers[kategori]) {
            // Tambahkan marker ke cluster group
            window.mapLayers[kategori].addLayer(assetMarker);
        }
    });

    // Tampilkan semua layer ke peta saat pertama kali dimuat
    Object.values(window.mapLayers).forEach(layer => {
        window.mainMap.addLayer(layer);
    });
};

/**
 * Fungsi Toggle untuk Checkbox
 * @param {string} category - Nama kategori (pju, rambu, dll)
 */
window.toggleLayer = function(category) {
    const layer = window.mapLayers[category.toLowerCase()];
    
    if (!layer) {
        console.warn(`Layer kategori "${category}" tidak ditemukan.`);
        return;
    }

    // Jika layer sudah ada di map, hapus. Jika belum, tambahkan.
    if (window.mainMap.hasLayer(layer)) {
        window.mainMap.removeLayer(layer);
    } else {
        window.mainMap.addLayer(layer);
    }
};