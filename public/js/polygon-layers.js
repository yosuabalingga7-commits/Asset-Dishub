/**
 * Polygon Layers Manager - Dishub KBB
 * Update: Global Scope Access for Custom UI Toggle - Single Contrast Color
 * Lokasi File: G:\Kerja\Dishub_kbb\public\js\polygon-layers.js
 */

function initPolygonLayers(map) {
    // === 1. PENGATURAN HIERARKI (Z-INDEX) ===
    map.createPane('paneKabupaten');
    map.getPane('paneKabupaten').style.zIndex = 200; 
    map.getPane('paneKabupaten').style.pointerEvents = 'none'; 

    // === 2. KONFIGURASI VISUAL & PEWARNAAN ===
    // Update: Menggunakan satu warna kontras agar tidak ramai (Clean Look)
    function getColor(nama) {
        return "#0B2A4A"; // Warna Biru Navy Utama Dishub KBB
    }

    const styleKabupaten = {
        color: "#000000",
        weight: 1,
        opacity: 1,
        fillColor: "transparent", 
        fillOpacity: 0,
        pane: 'paneKabupaten'
    };

    const styleDesa = {
        color: "#0B2A4A",
        weight: 1.5,
        opacity: 0.7,
        dashArray: '5, 5',
        fillColor: "#0B2A4A",
        fillOpacity: 0.05
    };

    // === 3. INISIALISASI LAYER ===
    const layerKabupaten = L.geoJSON(null, { style: styleKabupaten });

    const layerKecamatan = L.geoJSON(null, {
        style: function(feature) {
            return {
                color: "#ffffff", // Garis batas putih agar kontras dengan warna dasar
                weight: 2,
                opacity: 1,
                fillColor: getColor(),
                fillOpacity: 0.3 // Transparansi halus agar jalan di bawahnya tetap terlihat
            };
        },
        onEachFeature: function (feature, layer) {
            let namaKec = feature.properties.NAMOBJ || feature.properties.WADMKC || "Kecamatan";
            layer.on('click', function(e) {
                L.DomEvent.stopPropagation(e);
                let content = map.hasLayer(layerDesa) ? 
                    `<div style="text-align:center; padding: 5px;"><span>Kecamatan: <b>${namaKec}</b></span><br><small style="color: gray;">(Layer Desa Aktif)</small></div>` :
                    `<div style="text-align:center; padding: 5px;"><span>Kecamatan: <b>${namaKec}</b></span></div>`;
                layer.bindPopup(content).openPopup();
            });
            layer.bindTooltip("Kecamatan " + namaKec, { sticky: true });
        }
    });

    const layerDesa = L.geoJSON(null, {
        style: styleDesa,
        onEachFeature: function (feature, layer) {
            let namaDesa = feature.properties.NAMOBJ || "Desa";
            let namaKec = feature.properties.WADMKC || "Kecamatan"; 
            layer.on('click', function(e) {
                L.DomEvent.stopPropagation(e);
                let content = `<div style="text-align:center; padding: 5px;"><span>Kecamatan: <b>${namaKec}</b></span><br><span>Desa/Kel: <b>${namaDesa}</b></span></div>`;
                layer.bindPopup(content).openPopup();
            });
            layer.bindTooltip("Desa " + namaDesa, { sticky: true });
        }
    });

    // --- PERBAIKAN: EKSPOS VARIABEL KE GLOBAL ---
    window.polygonLayers = {
        kecamatan: layerKecamatan,
        desa: layerDesa,
        kabupaten: layerKabupaten
    };

    // === 4. LOAD DATA ===
    fetch('/shp/kabupaten_kbb.json')
        .then(res => res.json())
        .then(data => {
            layerKabupaten.addData(data).addTo(map);
        })
        .catch(err => console.error("Gagal load kabupaten_kbb.json:", err));

    fetch('/shp/kecamatan_kbb.json')
        .then(res => res.json())
        .then(data => {
            layerKecamatan.addData(data).addTo(map); 
        })
        .catch(err => console.error("Gagal load kecamatan_kbb.json:", err));

    fetch('/shp/administrasi_desa.json')
        .then(res => res.json())
        .then(data => {
            layerDesa.addData(data);
        })
        .catch(err => console.error("Gagal load administrasi_desa.json:", err));

    // === 5. KONTROL LAYER ===
    const overlayMaps = {
        "<span style='color: #0B2A4A; font-weight: bold;'>💠 Wilayah Kecamatan</span>": layerKecamatan,
        "<span style='color: #0B2A4A; font-weight: bold;'>💠 Batas Desa</span>": layerDesa
    };

    L.control.layers(null, overlayMaps, {
        collapsed: false, 
        position: 'bottomleft'
    }).addTo(map);
}