// resources/js/components/gis/LintasMap.jsx

import React, { useEffect, useState, useMemo } from 'react';
import { MapContainer, TileLayer, GeoJSON, Polygon } from 'react-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

// Store Zustand (UI dan Domain Data)
import useGisUIStore from '../../store/useGisUIStore';

// Controller Spasial
import MapControllers from './MapControllers';

// Impor komponen penanda
import AssetMarkers from './AssetMarkers';
import ReportMarkers from './ReportMarkers';

/**
 * ============================================================================
 * LintasMap (Infinite Canvas Layer - Z-0 - OPTIMIZED)
 * ============================================================================
 * Kanvas spasial utama Kabupaten Bandung Barat.
 * Dioptimalkan untuk mencegah siklus re-render masif saat kamera bergeser
 * dan memangkas verteks GeoJSON Masking seberat 4.9MB menggunakan Downsampling 75%.
 */

// Konfigurasi Ikon Default Leaflet agar tidak pecah saat Vite melakukan bundling
import icon from 'leaflet/dist/images/marker-icon.png';
import iconShadow from 'leaflet/dist/images/marker-shadow.png';

let DefaultIcon = L.icon({
    iconUrl: icon,
    shadowUrl: iconShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
});
L.Marker.prototype.options.icon = DefaultIcon;

export default function LintasMap() {
    // 1. Ambil State Visual dari useGisUIStore (Hanya ambil yang stabil untuk mencegah re-render)
    const activeBaseMap = useGisUIStore((state) => state.activeBaseMap);
    const mapOpacity = useGisUIStore((state) => state.mapOpacity);
    const activeLayers = useGisUIStore((state) => state.activeLayers);

    // [OPTIMASI GRASP CONTROLLER]: Ambil koordinat awal sekali saja (tidak reaktif)
    // Ini mencegah LintasMap me-re-render ulang seluruh peta saat kamera digeser (drag/pan)
    const initialCenter = useMemo(() => useGisUIStore.getState().mapCenter, []);
    const initialZoom = useMemo(() => useGisUIStore.getState().mapZoom, []);

    // State Lokal untuk GeoJSON Batas Administratif & Masking
    const [maskingData, setMaskingData] = useState(null);
    const [isMaskingLoading, setMaskingLoading] = useState(true);
    const [villageData, setVillageData] = useState(null);
    const [isVillageLoading, setVillageLoading] = useState(false);

    // Memuat berkas batas kabupaten KBB untuk masking secara asinkron dari folder public Laravel
    useEffect(() => {
        let isMounted = true;
        setMaskingLoading(true);

        fetch('/shp/kabupaten_kbb.json')
            .then((res) => {
                if (!res.ok) throw new Error('Berkas GeoJSON kabupaten tidak ditemukan.');
                return res.json();
            })
            .then((data) => {
                if (isMounted) {
                    setMaskingData(data);
                    setMaskingLoading(false);
                }
            })
            .catch((err) => {
                console.error('[LintasMap] Gagal memuat batas kabupaten KBB:', err);
                if (isMounted) setMaskingLoading(false);
            });

        return () => { isMounted = false; };
    }, []);

    // Lazy load berkas administrasi desa KBB secara asinkron ketika layer boundaries diaktifkan
    useEffect(() => {
        if (!activeLayers.includes('boundaries') || villageData || isVillageLoading) return;

        let isMounted = true;
        setVillageLoading(true);

        fetch('/shp/administrasi_desa.json')
            .then((res) => {
                if (!res.ok) throw new Error('Berkas GeoJSON desa tidak ditemukan.');
                return res.json();
            })
            .then((data) => {
                if (isMounted) {
                    setVillageData(data);
                    setVillageLoading(false);
                }
            })
            .catch((err) => {
                console.error('[LintasMap] Gagal memuat batas desa KBB:', err);
                if (isMounted) setVillageLoading(false);
            });

        return () => { isMounted = false; };
    }, [activeLayers, villageData, isVillageLoading]);

    // [OPTIMASI PURE FABRICATION]: GEOMETRIC DOWNSAMPLING 75%
    // Memangkas kerapatan kordinat masking di luar KBB agar Leaflet lancar menggambar SVG
    const kbbMaskingPolygon = useMemo(() => {
        if (!maskingData || !maskingData.features) return null;

        const outerWorldBounds = [
            [90, -360], [90, 360], [-90, 360], [-90, -360], [90, -360]
        ];

        const innerHoles = [];

        maskingData.features.forEach((feature) => {
            const geom = feature.geometry;
            if (!geom) return;

            if (geom.type === 'Polygon') {
                geom.coordinates.forEach((ring) => {
                    // Downsample: ambil setiap koordinat ke-4 (memangkas 75% verteks berat)
                    const downsampled = ring.filter((_, idx) => idx % 4 === 0 || idx === ring.length - 1);
                    const latLngRing = downsampled.map(coord => [coord[1], coord[0]]);
                    innerHoles.push(latLngRing);
                });
            } else if (geom.type === 'MultiPolygon') {
                geom.coordinates.forEach((polygon) => {
                    polygon.forEach((ring) => {
                        // Downsample: ambil setiap koordinat ke-4
                        const downsampled = ring.filter((_, idx) => idx % 4 === 0 || idx === ring.length - 1);
                        const latLngRing = downsampled.map(coord => [coord[1], coord[0]]);
                        innerHoles.push(latLngRing);
                    });
                });
            }
        });

        // Struktur [Outer, Hole1, Hole2, ...] akan meredupkan bagian luar lubang
        return [outerWorldBounds, ...innerHoles];
    }, [maskingData]);

    // 4. RESOLUSI TILE SERVER BASEMAP (Dinamis dari Zustand)
    const tileUrl = useMemo(() => {
        switch (activeBaseMap) {
            case 'satellite':
                // Google Satelit High-Res (Tanpa Label Jalan)
                return 'https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}';
            case 'street':
                // OpenStreetMap Standard
                return 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
            case 'dark':
            default:
                // CartoDB Dark Matter (Sangat direkomendasikan untuk menyorot emisi emiter)
                return 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
        }
    }, [activeBaseMap]);

    // Batas gaya visual garis pembatas desa KBB
    const geoJsonStyle = {
        color: '#2563eb', // Royal Blue, always readable on light/dark/satellite maps
        weight: 1.2,
        fillColor: 'transparent',
        fillOpacity: 0,
        opacity: 0.35
    };

    return (
        <div className="w-full h-full relative z-0 overflow-hidden bg-slate-50">

            {/* Indikator Loading di dasar layar jika GeoJSON belum beres di-parse */}
            {isMaskingLoading && (
                <div className="absolute inset-0 bg-white/80 flex items-center justify-center z-[999]">
                    <div className="text-center space-y-4">
                        <div className="w-10 h-10 border-4 border-[#2563eb] border-t-transparent rounded-full animate-spin mx-auto shadow-[0_0_15px_rgba(37,99,235,0.2)]"></div>
                        <p className="text-[10px] text-[#2563eb] font-black uppercase tracking-[0.2em] animate-pulse">Menyiapkan Batas Spasial KBB...</p>
                    </div>
                </div>
            )}

            {/* Indikator Loading khusus memuat batas desa */}
            {isVillageLoading && (
                <div className="absolute top-4 left-1/2 transform -translate-x-1/2 bg-white/95 border border-slate-200 px-3 py-1.5 flex items-center gap-2 text-left shadow-lg z-[999] rounded">
                    <div className="w-4 h-4 border-2 border-[#2563eb] border-t-transparent rounded-full animate-spin"></div>
                    <span className="text-[10px] text-[#2563eb] font-bold uppercase tracking-wider">Memuat Batas Desa KBB...</span>
                </div>
            )}

            <MapContainer
                center={initialCenter}
                zoom={initialZoom}
                zoomControl={false} // Dimatikan karena kita buat tombol zoom kustom di HUD
                className="w-full h-full"
                maxZoom={18}
                minZoom={8}
            >
                {/* 
                    [GRASP CONTROLLER]
                    Menyisipkan pengendali spasial independen di dalam konteks MapContainer 
                */}
                <MapControllers />

                {/* Layer 1: Basemap */}
                <TileLayer
                    url={tileUrl}
                    attribution='&copy; Mimika-DataHub &copy; OpenStreetMap'
                />

                {/* Layer 2: Masking luar wilayah Bandung Barat */}
                {kbbMaskingPolygon && (
                    <Polygon
                        positions={kbbMaskingPolygon}
                        pathOptions={{
                            color: 'transparent',
                            fillColor: activeBaseMap === 'dark' 
                                ? 'rgba(15,23,42,0.88)' 
                                : activeBaseMap === 'satellite' 
                                ? 'rgba(15,23,42,0.65)' 
                                : 'rgba(241,245,249,0.82)',
                            fillOpacity: 1,
                            stroke: false,
                            interactive: false
                        }}
                    />
                )}

                {/* Layer 3: Batas Garis Administrasi Desa KBB (selalu tampil jika data tersedia) */}
                {villageData && (
                    <GeoJSON
                        data={villageData}
                        style={geoJsonStyle}
                        interactive={false}
                    />
                )}

                {/* Layer 4: Penanda Spasial (Markers) */}
                {activeLayers.includes('assets') && (
                    <AssetMarkers opacity={mapOpacity / 100} />
                )}

                {activeLayers.includes('reports') && (
                    <ReportMarkers />
                )}

            </MapContainer>
        </div>
    );
}