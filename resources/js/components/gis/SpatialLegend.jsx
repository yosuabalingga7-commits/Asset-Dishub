import React, { useMemo } from 'react';
import { Layers } from 'lucide-react';

// Store Zustand (UI Controller)
import useGisUIStore from '../../store/useGisStore';

/**
 * ============================================================================
 * SpatialLegend (Map Index / Legenda Spasial - Z-30 Floating)
 * ============================================================================
 * Menampilkan kamus indeks status penanda yang aktif di peta.
 * Hanya akan di-render jika salah satu layer ('assets' atau 'reports') diaktifkan.
 *
 * VIBRANT LIGHT THEME: bg-white, border-slate-200/80, shadow-lg.
 */
export default function SpatialLegend() {
    // Menggunakan custom shallow comparison array agar tidak re-render saat peta di-zoom/geser
    const activeLayers = useGisUIStore(
        (state) => state.activeLayers,
        (prev, next) => prev.length === next.length && prev.every((v, i) => v === next[i])
    );

    const hasAssetsActive = activeLayers.includes('assets');
    const hasReportsActive = activeLayers.includes('reports');

    // Katalog Indeks Legenda Aset Dishub KBB
    const assetLegendItems = useMemo(() => [
        { label: 'Aset Baik / Berfungsi', color: '#10b981' },
        { label: 'Aset Rusak / Gangguan', color: '#f59e0b' },
        { label: 'Aset Kritis / Mati Total', color: '#dc2626' },
        { label: 'Proses Perbaikan', color: '#2563eb' }
    ], []);

    // Katalog Indeks Legenda Pengaduan Lapangan
    const reportLegendItems = useMemo(() => [
        { label: 'Laporan Warga (Pulsing Red)', color: '#e11d48' },
        { label: 'Laporan Petugas (Pulsing Amber)', color: '#f59e0b' }
    ], []);

    // Sembunyikan legenda jika tidak ada layer data yang aktif
    if (!hasAssetsActive && !hasReportsActive) return null;

    return (
        <div className="pointer-events-auto bg-white border border-slate-200/80 shadow-lg w-52 flex flex-col font-sans select-none text-slate-800 animate-in fade-in slide-in-from-bottom-2 duration-200">

            {/* Header */}
            <div className="flex items-center gap-1.5 px-3 py-2 bg-slate-50 border-b border-slate-200/80">
                <Layers size={12} className="text-[#2563eb]" />
                <h4 className="text-[9px] font-black text-slate-600 uppercase tracking-widest leading-none">
                    Legenda Spasial
                </h4>
            </div>

            {/* List Item */}
            <div className="p-3 space-y-3 text-left">

                {/* Bagian A: Status Kondisi Aset */}
                {hasAssetsActive && (
                    <div className="space-y-2">
                        <span className="text-[8px] font-black text-slate-400 uppercase tracking-wider block">
                            Kondisi Aset Dishub
                        </span>
                        <div className="space-y-1.5">
                            {assetLegendItems.map((item, idx) => (
                                <div key={idx} className="flex items-center gap-2">
                                    <span
                                        className="w-2.5 h-2.5 rounded-full border border-white shadow-sm shrink-0"
                                        style={{ backgroundColor: item.color }}
                                    ></span>
                                    <span className="text-[10px] font-semibold text-slate-600 leading-none">
                                        {item.label}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* Bagian B: Indikator Pengaduan (Radar) */}
                {hasReportsActive && (
                    <div className="space-y-2 pt-2.5 border-t border-slate-100">
                        <span className="text-[8px] font-black text-slate-400 uppercase tracking-wider block">
                            Aduan Masuk (Radar)
                        </span>
                        <div className="space-y-2">
                            {reportLegendItems.map((item, idx) => (
                                <div key={idx} className="flex items-center gap-2">
                                    <div className="relative w-3.5 h-3.5 flex items-center justify-center shrink-0">
                                        <span
                                            className="absolute inline-flex h-full w-full rounded-full opacity-60 animate-ping"
                                            style={{ backgroundColor: item.color }}
                                        ></span>
                                        <span
                                            className="relative inline-flex rounded-full h-2 w-2 border border-white shadow"
                                            style={{ backgroundColor: item.color }}
                                        ></span>
                                    </div>
                                    <span className="text-[10px] font-semibold text-slate-600 leading-none">
                                        {item.label}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

            </div>
        </div>
    );
}