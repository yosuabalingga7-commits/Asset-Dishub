document.addEventListener("DOMContentLoaded", function () {
    const map = L.map('map-aset', {
        maxZoom: MAP_SETTINGS.maxZoom,
        minZoom: MAP_SETTINGS.minZoom
    }).setView([MAP_SETTINGS.latitude, MAP_SETTINGS.longitude], MAP_SETTINGS.defaultZoom);

    L.tileLayer(MAP_SETTINGS.googleSatellite).addTo(map);

    // Marker sementara untuk menandai klik
    let tempMarker = null;

    map.on('click', function(e) {
        const { lat, lng } = e.latlng;
        
        // Hapus marker lama jika ada
        if (tempMarker) map.removeLayer(tempMarker);

        // Tambah marker baru di titik klik
        tempMarker = L.marker([lat, lng]).addTo(map)
            .bindPopup(`Koordinat Terpilih:<br>Lat: ${lat.toFixed(6)}<br>Lng: ${lng.toFixed(6)}`)
            .openPopup();

        // Jika ada element info di Blade, update isinya
        const infoBox = document.getElementById('coordinateInfo');
        const textElement = document.getElementById('latlngText');
        if (infoBox && textElement) {
            infoBox.classList.remove('hidden');
            textElement.innerText = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        }
    });

    setTimeout(() => { map.invalidateSize(); }, 300);
});