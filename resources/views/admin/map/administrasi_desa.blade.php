{{-- 
    FILE: administrasi_desa.blade.php
    LOGIKA UPDATE:
    1. Popup menampilkan Kecamatan terlebih dahulu baru Desa (Sesuai Permintaan).
    2. Layer Kecamatan dipastikan tampil di atas Layer Desa.
    3. Warna diupdate ke Palet Profesional (GIS Modern).
--}}

(function() {
    // 1. Inject CSS UI Panel Modern
    const customStyle = document.createElement('style');
    customStyle.innerHTML = `
        .leaflet-control-layers {
            border: none !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
            border-radius: 16px !important;
            padding: 12px 16px !important;
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px) !important;
            font-family: 'Inter', sans-serif !important;
        }
        .layer-control-label {
            font-size: 10px; font-weight: 800; text-transform: uppercase;
            color: #94a3b8; letter-spacing: 0.05em; margin-bottom: 10px; display: block;
            border-bottom: 1px solid #f1f5f9; padding-bottom: 6px;
        }
        .popup-container { min-width: 160px; font-family: 'Inter', sans-serif; }
        .popup-title { color: #0f172a; font-size: 14px; margin-bottom: 2px; letter-spacing: -0.01em; }
        .popup-sub { color: #94a3b8; font-size: 10px; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
        .popup-divider { margin: 8px 0; border: 0; border-top: 1px solid #f1f5f9; }
    `;
    document.head.appendChild(customStyle);

    // Path 3 File JSON
    const pathKab = "{{ asset('shp/kbb_garis_pembatas.json') }}";
    const pathKec = "{{ asset('shp/administrasi_kecamatan_line.json') }}";
    const pathDesa = "{{ asset('shp/administrasi_desa.json') }}";

    const layerKabupaten = L.layerGroup();
    const layerKecamatan = L.layerGroup();
    const layerDesa = L.layerGroup();

    async function initGisMap() {
        try {
            const [resKab, resKec, resDesa] = await Promise.all([
                fetch(pathKab).then(r => r.json()),
                fetch(pathKec).then(r => r.json()),
                fetch(pathDesa).then(r => r.json())
            ]);

            // --- 1. LAYER KABUPATEN (DEEP CHARCOAL) ---
            L.geoJSON(resKab, {
                filter: f => f.geometry !== null && f.properties.STSBTS <= 4,
                style: { 
                    color: "#2d3436", 
                    weight: 3, 
                    opacity: 1, 
                    interactive: false 
                }
            }).addTo(layerKabupaten);

            // --- 2. LAYER KECAMATAN (CRIMSON RED) ---
            L.geoJSON(resKec, {
                filter: f => f.geometry !== null,
                style: { 
                    color: "#c0392b", 
                    weight: 2, 
                    opacity: 0.9, 
                    interactive: false 
                }
            }).addTo(layerKecamatan);

            // --- 3. LAYER DESA (SLATE BLUE & POPUP) ---
            L.geoJSON(resDesa, {
                style: {
                    color: "transparent", 
                    fillColor: "#0984e3", 
                    fillOpacity: 0.06
                },
                onEachFeature: function(feature, layer) {
                    const n_desa = feature.properties.NAMOBJ || "Tidak Diketahui";
                    const n_kec = feature.properties.WADMKC || "Tidak Diketahui";
                    
                    layer.bindPopup(`
                        <div class="popup-container">
                            <div class="popup-sub">Kecamatan</div>
                            <div class="popup-title"><b>${n_kec.toUpperCase()}</b></div>
                            <div class="popup-divider"></div>
                            <div class="popup-sub">Desa</div>
                            <div style="color: #475569; font-weight: 600; font-size: 13px;">${n_desa}</div>
                        </div>
                    `);

                    layer.on('mouseover', function() { 
                        this.setStyle({ fillOpacity: 0.15, fillColor: "#0984e3" }); 
                    });
                    layer.on('mouseout', function() { 
                        this.setStyle({ fillOpacity: 0.06 }); 
                    });
                    
                    layer.on('click', function(e) {
                        map.fitBounds(e.target.getBounds());
                        L.DomEvent.stopPropagation(e);
                    });
                }
            }).addTo(layerDesa);

            // Masukkan ke Map
            layerKabupaten.addTo(map);
            layerDesa.addTo(map);
            layerKecamatan.addTo(map);

            // Pengaturan Urutan Tampilan
            layerKecamatan.bringToFront();
            layerKabupaten.bringToBack();

            // Kontrol Panel
            const overlays = {
                "<span class='layer-control-label'>Administrasi Wilayah</span>": {}, 
                "<b style='color: #2d3436;'>⬛ Batas Kabupaten</b>": layerKabupaten,
                "<b style='color: #c0392b;'>🟥 Batas Kecamatan</b>": layerKecamatan,
                "<b style='color: #74b9ff;'>🟦 Area Desa</b>": layerDesa
            };
            
            L.control.layers(null, overlays, { 
                collapsed: false, 
                position: 'topright' 
            }).addTo(map);

            console.log("✅ Peta Dishub KBB Berhasil Dimuat dengan Warna Profesional.");

        } catch (error) {
            console.error("❌ Gagal memuat file GeoJSON:", error);
        }
    }

    initGisMap();

    map.on('click', function() {
        if (typeof MAP_SETTINGS !== 'undefined') {
            map.flyTo([MAP_SETTINGS.latitude, MAP_SETTINGS.longitude], MAP_SETTINGS.defaultZoom);
        }
    });

})();