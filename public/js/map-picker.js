/**
 * Map Picker Manager - Dishub KBB
 * Fitur: Geocoding Pintar (Nama Tempat & Koordinat)
 * Lokasi File: G:\Kerja\Dishub_kbb\public\js\map-picker.js
 */

function initMapPicker(mapId, latInputId, lngInputId, alamatInputId, defaultPos = [-6.8431, 107.4912]) {
    
    // 1. Inisialisasi Map
    const map = L.map(mapId, {
        attributionControl: false
    }).setView(defaultPos, 13);

    // Simpan instance map ke window agar bisa diakses secara global jika perlu
    window.map = map;

    // Layer Utama (Google Streets)
    const googleStreets = L.tileLayer('http://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
    }).addTo(map);

    // Tambahkan kontrol Layer (Opsional agar user bisa ganti view)
    const baseLayers = {
        "Google Streets": googleStreets,
        "Satellite": L.tileLayer('http://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', { subdomains: ['mt0', 'mt1', 'mt2', 'mt3'] })
    };
    L.control.layers(baseLayers).addTo(map);

    // 2. Marker Draggable
    let marker = L.marker(defaultPos, {
        draggable: true
    }).addTo(map);

    // Fungsi Utama: Update Semua Field (Lat, Lng, dan Alamat)
    async function updateAllFields(lat, lng) {
        document.getElementById(latInputId).value = lat.toFixed(8);
        document.getElementById(lngInputId).value = lng.toFixed(8);

        const alamatField = document.getElementById(alamatInputId);
        if (alamatField) {
            alamatField.value = "Mendeteksi lokasi...";
            try {
                // Menggunakan Nominatim untuk Reverse Geocoding
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`);
                const data = await response.json();
                alamatField.value = data.display_name || "Alamat tidak ditemukan";
            } catch (error) {
                alamatField.value = "Gagal memuat alamat";
            }
        }
    }

    // Event: Saat Marker Digeser
    marker.on('dragend', function (e) {
        const position = marker.getLatLng();
        updateAllFields(position.lat, position.lng);
    });

    // Event: Saat Map Diklik
    map.on('click', function (e) {
        const { lat, lng } = e.latlng;
        marker.setLatLng([lat, lng]);
        updateAllFields(lat, lng);
    });

    // 3. FITUR PENCARIAN TEMPAT (Geocoder)
    // PERBAIKAN: Gunakan deteksi yang konsisten dengan library Leaflet-Control-Geocoder
    setTimeout(() => {
        // Cek semua kemungkinan nama fungsi geocoder di Leaflet
        const GeocoderLib = L.Control.Geocoder || L.Control.geocoder;

        if (typeof GeocoderLib !== 'undefined') {
            try {
                const searchControl = GeocoderLib({
                    defaultMarkGeocode: false,
                    placeholder: "Cari Lokasi...",
                    collapsed: false,
                    position: 'topleft'
                })
                .on('markgeocode', function (e) {
                    const center = e.geocode.center;
                    marker.setLatLng(center);
                    map.flyTo(center, 17);
                    updateAllFields(center.lat, center.lng);
                })
                .addTo(map);
            } catch (e) {
                console.warn("Geocoder UI gagal dimuat, sistem fallback aktif.");
            }
        } else {
            console.error("Leaflet Geocoder library is not loaded yet. Pastikan script CDN sudah benar di Blade.");
        }
    }, 500); 

    // 4. FUNGSI GLOBAL: Cari Berdasarkan Nama Tempat atau Koordinat
    window.searchByCoords = async function() {
        const searchInput = document.getElementById('searchCoords');
        if (!searchInput) return;

        const input = searchInput.value.trim();
        if (!input) return alert("Masukkan nama tempat atau koordinat!");

        // Deteksi apakah input adalah Koordinat (Lat, Lng)
        const coordRegex = /^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/;

        if (coordRegex.test(input)) {
            const split = input.split(',');
            const lat = parseFloat(split[0].trim());
            const lng = parseFloat(split[1].trim());

            const newPos = [lat, lng];
            marker.setLatLng(newPos);
            map.flyTo(newPos, 18);
            updateAllFields(lat, lng);
        } else {
            const query = input + " Kabupaten Bandung Barat";
            
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`);
                const data = await response.json();

                if (data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    const newPos = [lat, lng];

                    marker.setLatLng(newPos);
                    map.flyTo(newPos, 17);
                    updateAllFields(lat, lng);
                    
                    const alamatField = document.getElementById(alamatInputId);
                    if (alamatField) alamatField.value = data[0].display_name;
                } else {
                    alert("Lokasi '" + input + "' tidak ditemukan di wilayah KBB.");
                }
            } catch (error) {
                alert("Gagal menghubungi server peta.");
            }
        }
    };

    return map;
}