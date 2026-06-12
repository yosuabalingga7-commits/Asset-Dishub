// resources/js/components/gis/MapHUD.jsx

import React from 'react';
import { Plus, Minus, Maximize2, Compass } from 'lucide-react';

// Store Zustand (UI Controller)
import useGisUIStore from '../../store/useGisUIStore';

/**
 * ============================================================================
 * MapHUD (Heads-Up Display Control - Z-30 Floating)
 * ============================================================================
 * Terletak melayang di pojok kanan bawah peta (Z-30) dalam kondisi pointer-events-auto.
 * Menyediakan tombol pemicu event kendali kamera peta (Zoom, Re-center) 
 * serta monitor koordinat digital yang reaktif terhadap pergeseran Leaflet.
 */

export function CoordinateTracker() {
    const mapCenter = useGisUIStore((state) => state.mapCenter);
    const mapZoom = useGisUIStore((state) => state.mapZoom);

    return (
        <div className="pointer-events-auto bg-white/90 backdrop-blur-md border border-slate-200/80 px-3 py-2 flex items-center gap-3 text-left shadow-lg w-52 select-none">
            <Compass size={14} className="text-[#2563eb] animate-pulse shrink-0" />
            <div className="flex flex-col font-mono text-[9px] font-bold text-slate-700 leading-none">
                <span className="text-[8px] font-sans font-black text-slate-400 uppercase tracking-wider block">Kordinat Tracker</span>
                <span className="mt-1 block">
                    LAT: {mapCenter[0].toFixed(5)} | LNG: {mapCenter[1].toFixed(5)}
                </span>
                <span className="text-[7.5px] text-[#2563eb] font-sans font-black uppercase tracking-widest mt-1 block">
                    ZOOM LEVEL: {mapZoom}
                </span>
            </div>
        </div>
    );
}

export function ZoomControls() {
    const triggerZoomIn = () => window.dispatchEvent(new Event('map-zoom-in'));
    const triggerZoomOut = () => window.dispatchEvent(new Event('map-zoom-out'));
    const triggerResetView = () => window.dispatchEvent(new Event('map-reset-view'));

    return (
        <div className="pointer-events-auto flex flex-col bg-white/90 backdrop-blur-md border border-slate-200/80 shadow-lg rounded-none overflow-hidden divide-y divide-slate-200 select-none text-slate-800">
            {/* Zoom In */}
            <button
                onClick={triggerZoomIn}
                className="w-10 h-10 flex items-center justify-center text-slate-600 hover:bg-slate-100 hover:text-[#2563eb] transition-colors active:bg-slate-200 rounded-none outline-none cursor-pointer"
                title="Perbesar Peta (Zoom In)"
            >
                <Plus size={18} strokeWidth={2.5} />
            </button>

            {/* Reset View / Re-Center */}
            <button
                onClick={triggerResetView}
                className="w-10 h-9 flex items-center justify-center text-slate-500 hover:bg-slate-100 hover:text-[#2563eb] transition-colors active:bg-slate-200 group rounded-none outline-none cursor-pointer"
                title="Kembalikan Fokus Peta ke Bandung Barat"
            >
                <Maximize2 size={13} strokeWidth={2.5} className="group-hover:scale-110 transition-transform duration-200" />
            </button>

            {/* Zoom Out */}
            <button
                onClick={triggerZoomOut}
                className="w-10 h-10 flex items-center justify-center text-slate-600 hover:bg-slate-100 hover:text-[#2563eb] transition-colors active:bg-slate-200 rounded-none outline-none cursor-pointer"
                title="Perkecil Peta (Zoom Out)"
            >
                <Minus size={18} strokeWidth={2.5} />
            </button>
        </div>
    );
}