import React, { useEffect, useMemo } from 'react';
import { useMap, useMapEvents } from 'react-leaflet';

// Store Zustand untuk sinkronisasi koordinat & zoom
import useGisUIStore from '../../store/useGisUIStore';
import useLitasStore from '../../store/useLitasStore';

// Simple debounce utility for high frequency events
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

/**
 * ============================================================================
 * SPATIAL SINGLETON REGISTRY (Pure Fabrication)
 * ============================================================================
 * Menyimpan pointer mutatif instansi peta Leaflet secara non-reaktif.
 * Mencegah kebocoran siklus hidup peta ke dalam manajemen state Virtual DOM React.
 */
export const mapRegistry = {
    instance: null,
    set: (map) => { mapRegistry.instance = map; },
    get: () => mapRegistry.instance,
    clear: () => { mapRegistry.instance = null; }
};

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

    // 1. REGISTRASI INSTANCE NON-REAKTIF (Protected Variations)
    useEffect(() => {
        mapRegistry.set(map);

        // Pemicuan hidrasi data spasial awal saat pertama kali dipasang (Mounted)
        const bounds = map.getBounds();
        const zoom = map.getZoom();
        const serializableBounds = {
            minLat: bounds.getSouth(),
            minLng: bounds.getWest(),
            maxLat: bounds.getNorth(),
            maxLng: bounds.getEast()
        };

        useGisUIStore.getState().setMapBounds(serializableBounds);
        useLitasStore.getState().fetchAssets(serializableBounds, zoom);

        return () => {
            mapRegistry.clear();
        };
    }, [map]);

    // 2. SINKRONISASI LEAFLET TO ZUSTAND STORE (BBOX & Zoom Tracking - DEBOUNCED)
    const handleMapMoveEnd = useMemo(() => {
        return debounce(() => {
            const center = map.getCenter();
            const zoom = map.getZoom();
            const bounds = map.getBounds();

            // Ekstrak parameter batas koordinat viewport secara serializable
            const serializableBounds = {
                minLat: bounds.getSouth(),
                minLng: bounds.getWest(),
                maxLat: bounds.getNorth(),
                maxLng: bounds.getEast()
            };

            // Lakukan modifikasi langsung (direct write) ke store untuk efisiensi eksekusi
            useGisUIStore.getState().setMapCenter([center.lat, center.lng]);
            useGisUIStore.getState().setMapZoom(zoom);
            useGisUIStore.getState().setMapBounds(serializableBounds);

            // Trigger pemuatan data baru berbasis BBOX viewport terkini
            useLitasStore.getState().fetchAssets(serializableBounds, zoom);
        }, 300);
    }, [map]);

    useMapEvents({
        // Terjadi saat peta selesai digeser (drag/pan) atau di-zoom oleh pengguna
        moveend: handleMapMoveEnd
    });

    // 3. JEMBATAN INSTRUKSI IMPERATIF (Indirection / Controller)
    useEffect(() => {
        // Event A: Perintah Zoom-In dari tombol kustom HUD
        const handleZoomIn = () => {
            map.zoomIn();
        };

        // Event B: Perintah Zoom-Out dari tombol kustom HUD
        const handleZoomOut = () => {
            map.zoomOut();
        };

        // Event C: Perintah mengembalikan peta ke titik fokus KBB (instant, no lag)
        const handleResetView = () => {
            map.setView([-6.8431, 107.4912], 11, { animate: false });
        };

        // Daftarkan seluruh pendengar event ke global window object
        window.addEventListener('map-zoom-in', handleZoomIn);
        window.addEventListener('map-zoom-out', handleZoomOut);
        window.addEventListener('map-reset-view', handleResetView);

        // Bersihkan seluruh listener saat komponen unmount untuk mencegah kebocoran memori
        return () => {
            window.removeEventListener('map-zoom-in', handleZoomIn);
            window.removeEventListener('map-zoom-out', handleZoomOut);
            window.removeEventListener('map-reset-view', handleResetView);
        };
    }, [map]);

    return null; // Komponen ini murni controller, tidak merender elemen visual HTML apa pun
}