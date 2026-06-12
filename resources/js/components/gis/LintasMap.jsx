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

    // State Lokal untuk GeoJSON Batas Administratif
    const [geoJsonData, setGeoData] = useState(null);
    const [isGeoLoading, setGeoLoading] = useState(true);

    // Memuat berkas administrasi desa KBB secara asinkron dari folder public Laravel
    useEffect(() => {
        let isMounted = true;
        setGeoLoading(true);

        fetch('/shp/administrasi_desa.json')
            .then((res) => {
                if (!res.ok) throw new Error('Berkas GeoJSON desa tidak ditemukan.');
                return res.json();
            })
            .then((data) => {
                if (isMounted) {
                    setGeoData(data);
                    setGeoLoading(false);
                }
            })
            .catch((err) => {
                console.error('[LintasMap] Gagal memuat batas administrasi KBB:', err);
                if (isMounted) setGeoLoading(false);
            });

        return () => { isMounted = false; };
    }, []);

    // [OPTIMASI PURE FABRICATION]: GEOMETRIC DOWNSAMPLING 75%
    // Memangkas kerapatan kordinat masking di luar KBB agar Leaflet lancar menggambar SVG
    const kbbMaskingPolygon = useMemo(() => {
        if (!geoJsonData || !geoJsonData.features) return null;

        const outerWorldBounds = [
            [90, -360], [90, 360], [-90, 360], [-90, -360], [90, -360]
        ];

        const innerHoles = [];

        geoJsonData.features.forEach((feature) => {
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

        return [outerWorldBounds, ...innerHoles];
    }, [geoJsonData]);

    const tileUrl = useMemo(() => {
        switch (activeBaseMap) {
            case 'satellite':
                return 'https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}';
            case 'dark':
                return 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png';
            case 'street':
            case 'light':
            default:
                return 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png';
        }
    }, [activeBaseMap]);

    const geoJsonStyle = useMemo(() => ({
        color: activeBaseMap === 'dark' ? '#00e5ff' : '#2563eb',
        weight: 1.2,
        fillColor: 'transparent',
        fillOpacity: 0,
        opacity: 0.35
    }), [activeBaseMap]);

    return (
        <div className="w-full h-full relative z-0 overflow-hidden bg-slate-50">

            {isGeoLoading && (
                <div className="absolute inset-0 bg-white/80 flex items-center justify-center z-999">
                    <div className="text-center space-y-4">
                        <div className="w-10 h-10 border-4 border-[#2563eb] border-t-transparent rounded-full animate-spin mx-auto"></div>
                        <p className="text-[10px] text-slate-600 font-black uppercase tracking-[0.2em] animate-pulse">Menyiapkan Batas Spasial KBB...</p>
                    </div>
                </div>
            )}

            <MapContainer
                center={initialCenter}
                zoom={initialZoom}
                zoomControl={false}
                className="w-full h-full"
                maxZoom={18}
                minZoom={8}
            >
                <MapControllers />

                <TileLayer
                    url={tileUrl}
                    attribution='&copy; Mimika-DataHub &copy; OpenStreetMap'
                />

                {kbbMaskingPolygon && (
                    <Polygon
                        positions={kbbMaskingPolygon}
                        pathOptions={{
                            color: '#000000',
                            fillColor: '#0f172a',
                            fillOpacity: 0.85,
                            stroke: false,
                            interactive: false
                        }}
                    />
                )}

                {/* 
                    [OPTIMASI REACT-LEAFLET KEY]: 
                    Gunakan key dinamis berbasis basemap aktif agar GeoJSON hanya digambar ulang 
                    ketika warna garis dasar berubah, bukan setiap kali peta digeser!
                */}
                {geoJsonData && activeLayers.includes('boundaries') && (
                    <GeoJSON
                        key={`boundaries-layer-${activeBaseMap}`}
                        data={geoJsonData}
                        style={geoJsonStyle}
                        interactive={false}
                    />
                )}

                {activeLayers.includes('assets') && (
                    <AssetMarkers />
                )}

                {activeLayers.includes('reports') && (
                    <ReportMarkers />
                )}

            </MapContainer>
        </div>
    );
}