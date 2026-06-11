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
 * DIOPTIMALKAN (GRASP Low Coupling): Tidak memiliki silsilah useEffect penarik data.
 * Memisahkan tanggung jawab penarikan data ke level MapControllers.jsx.
 */

const createAssetIcon = (status, emoji) => {
    let color = '#16a34a'; // Default: Baik (Hijau)
    if (status === 'Rusak') color = '#fbbf24'; // Rusak (Kuning/Oranye)
    if (status === 'Kritis') color = '#dc2626'; // Kritis (Merah)
    if (status === 'Proses Perbaikan' || status === 'Proses') color = '#3b82f6'; // Proses Perbaikan (Biru)

    return L.divIcon({
        className: 'custom-asset-pin bg-transparent border-none',
        html: `
            <div class="relative flex flex-col items-center" style="transform: translate(-50%, -100%);">
                <div class="w-8 h-8 rounded-full border-2 border-white flex items-center justify-center shadow-lg transition-transform duration-200 hover:scale-110" style="background-color: ${color};">
                    <span class="text-sm select-none">${emoji || '📍'}</span>
                </div>
                <div class="w-0 h-0 border-l-[6px] border-r-[6px] border-t-8 border-l-transparent border-r-transparent" style="border-t-color: ${color}; margin-top: -2px;"></div>
            </div>
        `,
        iconSize: [32, 40],
        iconAnchor: [0, 0]
    });
};

const createClusterIcon = (cluster) => {
    const count = cluster.getChildCount();
    return createClusterIconFromCount(count);
};

const createClusterIconFromCount = (count) => {
    let sizeClass = 'w-9 h-9 text-xs';
    if (count >= 10 && count < 50) sizeClass = 'w-11 h-11 text-sm';
    if (count >= 50) sizeClass = 'w-14 h-14 text-base';

    return L.divIcon({
        className: 'custom-cluster-icon bg-transparent border-none',
        html: `
            <div class="${sizeClass} bg-slate-900/90 text-[#00e5ff] flex items-center justify-center rounded-full border-[3px] border-[#00e5ff] shadow-[0_0_15px_rgba(0,229,255,0.4)] font-mono font-black select-none">
                ${count}
            </div>
        `,
        iconSize: [56, 56],
        iconAnchor: [28, 28]
    });
};

export default function AssetMarkers() {
    const assets = useLitasStore((state) => state.assets);
    const { openPanel, closePanelsToTheRight, setSelectedAssetId, selectedAssetId } = useGisUIStore();

    // Memastikan koordinat aman dalam format float desimal sebelum dilempar ke Leaflet
    const sanitizedAssets = useMemo(() => {
        return assets.map(asset => {
            const latVal = parseFloat(asset.lat);
            const lngVal = parseFloat(asset.lng);

            if (isNaN(latVal) || isNaN(lngVal)) return null;

            return {
                ...asset,
                lat: latVal,
                lng: lngVal
            };
        }).filter(Boolean);
    }, [assets]);

    const handleMarkerClick = (asset, e) => {
        e.originalEvent.stopPropagation();
        const map = e.target._map;

        if (asset.is_cluster) {
            // Jika klaster server diklik, perbesar kamera spasial ke pusat klaster tersebut
            map.flyTo([asset.lat, asset.lng], map.getZoom() + 2, { animate: true });
        } else {
            // Jika aset individu diklik, fokuskan kamera dan tampilkan laci detail
            map.flyTo([asset.lat, asset.lng], 16, { animate: true });
            setSelectedAssetId(asset.id);
            closePanelsToTheRight(-1);
            openPanel('detil-aset', `Detail Aset: ${asset.id_asset}`, asset);
        }
    };

    // Segregasikan rendering berdasarkan sifat muatan data spasial (Polimorfis)
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
            {/* 1. Render Klaster Spasial Dinamis (Server-Side Clusters - Zoom Rendah) */}
            {serverClusters.map((cluster) => (
                <Marker
                    key={cluster.id}
                    position={[cluster.lat, cluster.lng]}
                    icon={createClusterIconFromCount(cluster.count)}
                    eventHandlers={{
                        click: (e) => handleMarkerClick(cluster, e)
                    }}
                >
                    <Tooltip direction="top" offset={[0, -20]} opacity={0.90}>
                        <div className="font-sans text-xs text-slate-800 p-1 text-center font-bold">
                            Terdapat {cluster.count} Aset di wilayah ini
                        </div>
                    </Tooltip>
                </Marker>
            ))}

            {/* 2. Render Aset Fisik Individu Terklaster (Client-Side Clusters - Zoom Tinggi) */}
            <MarkerClusterGroup
                chunkedLoading
                iconCreateFunction={createClusterIcon}
                showCoverageOnHover={false}
                maxClusterRadius={40}
                spiderfyOnMaxZoom={true}
            >
                {rawPoints.map((asset) => {
                    const iconMarker = asset.icon_marker || '📍';

                    return (
                        <Marker
                            key={`asset-${asset.id}`}
                            position={[asset.lat, asset.lng]}
                            icon={createAssetIcon(asset.status, iconMarker)}
                            eventHandlers={{
                                click: (e) => handleMarkerClick(asset, e)
                            }}
                        >
                            <Tooltip direction="top" offset={[0, -28]} opacity={0.95}>
                                <div className="font-sans text-xs text-slate-800 p-1 space-y-1 text-left min-w-35">
                                    <p className="font-black text-[10px] uppercase tracking-wider text-slate-400 leading-none">
                                        {asset.kategori}
                                    </p>
                                    <h4 className="font-bold text-slate-900 leading-tight">
                                        {asset.nama}
                                    </h4>
                                    <div className="flex items-center gap-1.5 pt-1 border-t border-slate-100">
                                        <span className="w-2 h-2 rounded-full" style={{
                                            backgroundColor: asset.status === 'Baik' ? '#16a34a' : asset.status === 'Rusak' ? '#fbbf24' : '#dc2626'
                                        }}></span>
                                        <span className="text-[9px] font-black uppercase text-slate-500">
                                            Kondisi: {asset.status}
                                        </span>
                                    </div>
                                </div>
                            </Tooltip>
                        </Marker>
                    );
                })}
            </MarkerClusterGroup>
        </React.Fragment>
    );
}