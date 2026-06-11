// resources/js/components/gis/MapControllers.jsx

import React, { useEffect } from 'react';
import { useMap, useMapEvents } from 'react-leaflet';

// Store Zustand untuk sinkronisasi koordinat & zoom
import useGisUIStore from '../../store/useGisUIStore';

/**
 * ============================================================================
 * MapControllers (Pure Fabrication - Spatial Event Controller)
 * ============================================================================
 * Bertindak sebagai "Jembatan Sensoris" antara Leaflet Map Engine dan React.
 * Menangani semua event kamera (Zoom/Drag) secara instan, serta mendengarkan 
 * instruksi event eksternal dari komponen lain tanpa merusak siklus hidup peta.
 */

export default function MapControllers() {
    const map = useMap();

    // 1. SINKRONISASI LEAFLET TO ZUSTAND STORE
    useMapEvents({
        // Terjadi saat peta selesai digeser (drag/pan) oleh pengguna
        moveend: () => {
            const center = map.getCenter();
            // Lakukan modifikasi langsung (direct write) ke store untuk efisiensi
            useGisUIStore.getState().setMapCenter([center.lat, center.lng]);
        },
        // Terjadi saat tingkat zoom peta berubah
        zoomend: () => {
            useGisUIStore.getState().setMapZoom(map.getZoom());
        }
    });

    // 2. SINKRONISASI EXTERNAL EVENT WINDOW TO LEAFLET
    useEffect(() => {
        // Event A: Perintah Zoom-In dari tombol kustom HUD
        const handleZoomIn = () => {
            map.zoomIn();
        };

        // Event B: Perintah Zoom-Out dari tombol kustom HUD
        const handleZoomOut = () => {
            map.zoomOut();
        };

        // Event C: Perintah mengembalikan peta ke titik fokus KBB
        const handleResetView = () => {
            // Kembali ke pusat Bandung Barat [-6.8431, 107.4912] dengan animasi halus
            map.setView([-6.8431, 107.4912], 11, { animate: true, duration: 1.2 });
        };

        // Event D: Perintah terbang ke koordinat tertentu (contoh: Saat mengklik baris list aset)
        const handleFlyToCoords = (e) => {
            const { lat, lng, zoom } = e.detail;
            if (lat && lng) {
                map.flyTo([lat, lng], zoom || 16, {
                    animate: true,
                    duration: 1.5 // Efek sinematik meluncur 1.5 detik
                });
            }
        };

        // Daftarkan seluruh pendengar event ke global window object
        window.addEventListener('map-zoom-in', handleZoomIn);
        window.addEventListener('map-zoom-out', handleZoomOut);
        window.addEventListener('map-reset-view', handleResetView);
        window.addEventListener('map-fly-to-coords', handleFlyToCoords);

        // Bersihkan seluruh listener saat komponen unmount untuk cegah kebocoran memori
        return () => {
            window.removeEventListener('map-zoom-in', handleZoomIn);
            window.removeEventListener('map-zoom-out', handleZoomOut);
            window.removeEventListener('map-reset-view', handleResetView);
            window.removeEventListener('map-fly-to-coords', handleFlyToCoords);
        };
    }, [map]);

    return null; // Komponen ini murni controller, tidak merender elemen visual HTML apa pun
}