// resources/js/components/gis/AssetMarkers.jsx

import React, { useMemo } from 'react';
import { Marker, Tooltip } from 'react-leaflet';
import MarkerClusterGroup from 'react-leaflet-cluster';
import L from 'leaflet';

// Store Zustand (UI dan Domain Data)
import useGisUIStore from '../../store/useGisUIStore';
import useLitasStore from '../../store/useLitasStore';

/**
 * ============================================================================
 * AssetMarkers (Data Injector Layer - OPTIMIZED)
 * ============================================================================
 * Menyadap dan merender seluruh aset fisik Dinas Perhubungan KBB.
 *
 * PERUBAHAN ARSITEKTUR:
 * - Membaca dari `mapAssets` (bukan `assets`) — state terfilter viewport.
 * - Menambahkan <MarkerClusterGroup> sebagai fallback klien untuk lingkungan
 *   yang tidak memiliki dukungan clustering PostGIS server-side (Protected Variations).
 */

// ─── PIN MARKER ASET ─────────────────────────────────────────────────────────
const getSvgForEmoji = (emoji) => {
    switch (emoji) {
        case '💡':
            return `<svg viewBox="0 0 24 24" width="14" height="14" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A5 5 0 0 0 8 8c0 1 .3 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"></path><path d="M9 18h6"></path><path d="M10 22h4"></path></svg>`;
        case '🛑':
        case '🚦':
            return `<svg viewBox="0 0 24 24" width="14" height="14" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="2" width="12" height="20" rx="2"></rect><circle cx="12" cy="7" r="1.5"></circle><circle cx="12" cy="12" r="1.5"></circle><circle cx="12" cy="17" r="1.5"></circle></svg>`;
        case '🚧':
            return `<svg viewBox="0 0 24 24" width="14" height="14" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`;
        case '📹':
            return `<svg viewBox="0 0 24 24" width="14" height="14" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="m22 8-6 4 6 4V8Z"></path><rect x="2" y="6" width="14" height="12" rx="2" ry="2"></rect></svg>`;
        case '🚌':
        case '🏢':
            return `<svg viewBox="0 0 24 24" width="14" height="14" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="16" rx="2"></rect><path d="M4 11h16"></path><path d="M8 15h.01"></path><path d="M16 15h.01"></path><path d="M6 19v2"></path><path d="M18 19v2"></path></svg>`;
        default:
            return `<svg viewBox="0 0 24 24" width="14" height="14" stroke="white" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>`;
    }
};

const createAssetIcon = (status, emoji) => {
    let color = '#16a34a'; // Default: Baik (Hijau Zamrud)
    if (status === 'Rusak') color = '#f59e0b';       // Rusak (Amber)
    if (status === 'Kritis') color = '#dc2626';       // Kritis (Merah)
    if (status === 'Proses Perbaikan' || status === 'Proses') color = '#2563eb'; // Proses (Biru)

    const svgIcon = getSvgForEmoji(emoji);

    return L.divIcon({
        className: 'custom-asset-pin bg-transparent border-none',
        html: `
            <div class="relative flex flex-col items-center" style="transform: translate(-50%, -100%);">
                <div class="w-8 h-8 rounded-full border-2 border-white flex items-center justify-center shadow-lg transition-transform duration-200 hover:scale-110" style="background-color: ${color};">
                    ${svgIcon}
                </div>
                <div class="w-0 h-0 border-l-[6px] border-r-[6px] border-t-8 border-l-transparent border-r-transparent" style="border-top-color: ${color}; margin-top: -2px;"></div>
            </div>
        `,
        iconSize: [32, 40],
        iconAnchor: [0, 0]
    });
};

// ─── IKON KLASTER SERVER-SIDE ─────────────────────────────────────────────────
const createClusterIconFromCount = (count) => {
    let size = 36;
    let fontSize = 11;
    if (count >= 10 && count < 50) { size = 44; fontSize = 12; }
    if (count >= 50) { size = 54; fontSize = 14; }

    return L.divIcon({
        className: 'custom-cluster-icon bg-transparent border-none',
        html: `
            <div style="width:${size}px;height:${size}px;background:#1e40af;color:#fff;
                border-radius:50%;border:3px solid #93c5fd;
                box-shadow:0 0 0 4px rgba(37,99,235,0.2);
                display:flex;align-items:center;justify-content:center;
                font-family:monospace;font-size:${fontSize}px;font-weight:900;">
                ${count}
            </div>
        `,
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2]
    });
};

// ─── IKON KLASTER CLIENT-SIDE (MarkerClusterGroup) ────────────────────────────
const createClientClusterIcon = (cluster) => {
    const count = cluster.getChildCount();
    let size = 36;
    if (count >= 10 && count < 50) size = 44;
    if (count >= 50) size = 54;

    return L.divIcon({
        className: 'bg-transparent border-none',
        html: `
            <div style="width:${size}px;height:${size}px;background:#1e40af;color:#fff;
                border-radius:50%;border:3px solid #93c5fd;
                box-shadow:0 0 0 4px rgba(37,99,235,0.2);
                display:flex;align-items:center;justify-content:center;
                font-family:monospace;font-size:12px;font-weight:900;">
                ${count}
            </div>
        `,
        iconSize: [size, size],
        iconAnchor: [size / 2, size / 2]
    });
};

export default function AssetMarkers() {
    // Membaca dari mapAssets (viewport-filtered) — bukan catalogAssets
    const mapAssets = useLitasStore((state) => state.mapAssets);

    // Selectors individual untuk mencegah re-render reaktif tak terkendali
    const openPanel = useGisUIStore((state) => state.openPanel);
    const closePanelsToTheRight = useGisUIStore((state) => state.closePanelsToTheRight);
    const setSelectedAssetId = useGisUIStore((state) => state.setSelectedAssetId);

    // Sanitisasi koordinat sebelum dilempar ke Leaflet
    const sanitizedAssets = useMemo(() => {
        return mapAssets.map(asset => {
            const latVal = parseFloat(asset.lat);
            const lngVal = parseFloat(asset.lng);
            if (isNaN(latVal) || isNaN(lngVal)) return null;
            return { ...asset, lat: latVal, lng: lngVal };
        }).filter(Boolean);
    }, [mapAssets]);

    const handleMarkerClick = (asset, e) => {
        e.originalEvent.stopPropagation();
        const map = e.target._map;

        if (asset.is_cluster) {
            // Klik klaster server-side: zoom-in ke pusat klaster instan
            map.setView([asset.lat, asset.lng], map.getZoom() + 2, { animate: false });
        } else {
            // Klik aset individu: buka laci detail (no map setView/flyTo)
            setSelectedAssetId(asset.id);
            closePanelsToTheRight(-1);
            openPanel('detil-aset', `Detail Aset: ${asset.id_asset}`, asset);
        }
    };

    // Segregasikan rendering berdasarkan sifat muatan data spasial
    const { rawPoints, serverClusters } = useMemo(() => {
        const points = [];
        const clusters = [];
        sanitizedAssets.forEach(item => {
            if (item.is_cluster) {
                clusters.push(item);
            } else {
                points.push(item);
            }
        });
        return { rawPoints: points, serverClusters: clusters };
    }, [sanitizedAssets]);

    return (
        <React.Fragment>
            {/* 1. Klaster Spasial Server-Side (dari PostGIS — Zoom rendah) */}
            {serverClusters.map((cluster) => (
                <Marker
                    key={`scluster-${cluster.id}`}
                    position={[cluster.lat, cluster.lng]}
                    icon={createClusterIconFromCount(cluster.count)}
                    eventHandlers={{ click: (e) => handleMarkerClick(cluster, e) }}
                >
                    <Tooltip direction="top" offset={[0, -20]} opacity={0.95}>
                        <div className="font-sans text-xs text-slate-800 p-1 text-center font-bold">
                            {cluster.count} Aset di wilayah ini
                        </div>
                    </Tooltip>
                </Marker>
            ))}

            {/* 2. Aset Individu — dibungkus MarkerClusterGroup untuk fallback klien */}
            {rawPoints.length > 0 && (
                <MarkerClusterGroup
                    chunkedLoading
                    iconCreateFunction={createClientClusterIcon}
                    maxClusterRadius={60}
                    spiderfyOnMaxZoom={true}
                    showCoverageOnHover={false}
                    zoomToBoundsOnClick={true}
                >
                    {rawPoints.map((asset) => {
                        const iconMarker = asset.icon_marker || '📍';
                        return (
                            <Marker
                                key={`asset-${asset.id}`}
                                position={[asset.lat, asset.lng]}
                                icon={createAssetIcon(asset.status, iconMarker)}
                                eventHandlers={{ click: (e) => handleMarkerClick(asset, e) }}
                            >
                                <Tooltip direction="top" offset={[0, -28]} opacity={0.95}>
                                    <div className="font-sans text-xs p-1.5 space-y-1 text-left min-w-[160px] bg-white rounded shadow-sm">
                                        <p className="font-black text-[9px] uppercase tracking-wider text-slate-400 leading-none">
                                            {asset.kategori}
                                        </p>
                                        <h4 className="font-bold text-slate-900 leading-tight text-[12px]">
                                            {asset.nama}
                                        </h4>
                                        <p className="text-[9px] text-slate-500 font-medium">{asset.alamat}</p>
                                        <div className="flex items-center gap-1.5 pt-1 border-t border-slate-100">
                                            <span className="w-2 h-2 rounded-full shrink-0" style={{
                                                backgroundColor: asset.status === 'Baik' ? '#10b981' : asset.status === 'Rusak' ? '#f59e0b' : '#dc2626'
                                            }}></span>
                                            <span className="text-[9px] font-black uppercase text-slate-500">
                                                {asset.status}
                                            </span>
                                        </div>
                                    </div>
                                </Tooltip>
                            </Marker>
                        );
                    })}
                </MarkerClusterGroup>
            )}
        </React.Fragment>
    );
}