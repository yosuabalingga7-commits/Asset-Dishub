import React from 'react';
import { Plus, Minus, Maximize2, Compass } from 'lucide-react';

// Store Zustand (UI Controller)
import useGisUIStore from '../../store/useGisUIStore';

/**
 * ============================================================================
 * CoordinateTracker (Sub-Component - GRASP High Cohesion)
 * ============================================================================
 * Murni merender box informasi koordinat GPS aktif dan level zoom peta secara real-time.
 * Menggunakan selector granular untuk mencegah rendering berlebih saat peta bergerak.
 */
export function CoordinateTracker() {
    // Membaca kordinat dengan optimasi custom comparator untuk mendeteksi perubahan koordinat presisi
    const mapCenter = useGisUIStore(
        (state) => state.mapCenter,
        (prev, next) => prev[0] === next[0] && prev[1] === next[1]
    );
    const mapZoom = useGisUIStore((state) => state.mapZoom);

    return (
        <div className="pointer-events-auto bg-white border border-slate-200/80 shadow-lg w-52 p-3 flex items-center gap-2.5 text-left select-none animate-in fade-in slide-in-from-bottom-2 duration-200">
            <Compass size={14} className="text-[#2563eb] shrink-0" />
            <div className="flex flex-col font-mono text-[9px] font-bold text-slate-600 leading-none gap-1">
                <span className="text-[8px] font-sans font-black text-slate-400 uppercase tracking-wider">
                    Koordinat Tracker
                </span>
                <span className="text-slate-700 font-mono">
                    {mapCenter[0].toFixed(5)}, {mapCenter[1].toFixed(5)}
                </span>
                <span className="text-[8px] text-[#2563eb] font-sans font-black uppercase tracking-widest">
                    Zoom Level: {mapZoom}
                </span>
            </div>
        </div>
    );
}

/**
 * ============================================================================
 * ZoomControls (Sub-Component - GRASP High Cohesion)
 * ============================================================================
 * Murni merender tombol kendali zoom (+ / re-center / -) dalam stack vertikal ramping.
 * Terisolasi sepenuhnya dari state koordinat sehingga tidak akan re-render saat peta digeser.
 */
export function ZoomControls() {
    const triggerZoomIn = () => window.dispatchEvent(new Event('map-zoom-in'));
    const triggerZoomOut = () => window.dispatchEvent(new Event('map-zoom-out'));
    const triggerResetView = () => window.dispatchEvent(new Event('map-reset-view'));

    return (
        <div className="pointer-events-auto flex flex-col bg-white border border-slate-200/80 shadow-lg overflow-hidden divide-y divide-slate-100 select-none">
            {/* Zoom In */}
            <button
                onClick={triggerZoomIn}
                className="w-10 h-10 flex items-center justify-center text-slate-600 hover:bg-slate-50 hover:text-[#2563eb] transition-colors active:bg-slate-100 outline-none cursor-pointer"
                title="Perbesar Peta (Zoom In)"
            >
                <Plus size={18} strokeWidth={2.5} />
            </button>

            {/* Reset View / Re-Center */}
            <button
                onClick={triggerResetView}
                className="w-10 h-9 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-[#2563eb] transition-colors active:bg-slate-100 group outline-none cursor-pointer"
                title="Kembalikan Fokus Peta ke Bandung Barat"
            >
                <Maximize2 size={13} strokeWidth={2.5} className="group-hover:scale-110 transition-transform duration-200" />
            </button>

            {/* Zoom Out */}
            <button
                onClick={triggerZoomOut}
                className="w-10 h-10 flex items-center justify-center text-slate-600 hover:bg-slate-50 hover:text-[#2563eb] transition-colors active:bg-slate-100 outline-none cursor-pointer"
                title="Perkecil Peta (Zoom Out)"
            >
                <Minus size={18} strokeWidth={2.5} />
            </button>
        </div>
    );
}

/**
 * Default Export sebagai wrapper gabungan untuk fallback kompatibilitas ke belakang
 */
export default function MapHUD() {
    return (
        <div className="flex flex-row items-end gap-2 pointer-events-none select-none">
            <CoordinateTracker />
            <ZoomControls />
        </div>
    );
}