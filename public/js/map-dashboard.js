document.addEventListener("DOMContentLoaded", function () {
    const map = L.map('map-dashboard', {
        zoomControl: false,
        attributionControl: false,
        maxZoom: MAP_SETTINGS.maxZoom,
        minZoom: MAP_SETTINGS.minZoom
    }).setView([MAP_SETTINGS.latitude, MAP_SETTINGS.longitude], MAP_SETTINGS.defaultZoom);

    L.tileLayer(MAP_SETTINGS.googleStreet).addTo(map);
    const markerLayer = L.layerGroup().addTo(map);

    /**
     * Fitur Zoom Out Otomatis
     * Menangani klik pada area kosong di peta untuk kembali ke tampilan semula
     */
    map.on('click', function (e) {
        // Jika yang diklik adalah container peta (area kosong), bukan marker
        if (e.originalEvent.target.id === 'map-dashboard' || e.originalEvent.target.classList.contains('leaflet-container')) {
            
            // 1. Kembali ke koordinat awal dan zoom default (KBB)
            map.flyTo([MAP_SETTINGS.latitude, MAP_SETTINGS.longitude], MAP_SETTINGS.defaultZoom);

            // 2. Sembunyikan detail asset jika fungsi hideDetail tersedia di Blade
            if (typeof window.hideDetail === 'function') {
                window.hideDetail();
            }
        }
    });

    function renderDashboardMarkers() {
        if (typeof asetDishub !== 'undefined') {
            asetDishub.forEach(aset => {
                // Menggunakan CircleMarker agar lebih modern sesuai UI Dashboard
                const marker = L.circleMarker([aset.lat, aset.lng], {
                    radius: 8,
                    fillColor: "#4f46e5",
                    color: "#fff",
                    weight: 2,
                    fillOpacity: 1
                }).addTo(markerLayer);

                // Fungsi saat marker diklik
                marker.on('click', function(e) {
                    // PENTING: Mencegah event "click" tembus ke map (propagation)
                    // Agar peta tidak menganggap ini klik area kosong yang memicu zoom out
                    L.DomEvent.stopPropagation(e);

                    // Optional: Zoom sedikit ke arah marker agar fokus (misal zoom ke level 15)
                    map.flyTo([aset.lat, aset.lng], 15);

                    if (typeof window.showDetail === 'function') {
                        window.showDetail(aset); // Memanggil fungsi detail di Blade
                    }
                });
            });
        }
    }

    renderDashboardMarkers();
    
    // Memastikan peta ter-render dengan benar saat modal atau layout termuat
    setTimeout(() => { 
        map.invalidateSize(); 
    }, 300);
});