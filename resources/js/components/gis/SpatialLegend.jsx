// resources/js/components/gis/SpatialLegend.jsx

import React, { useMemo } from 'react';
import { Layers, HelpCircle } from 'lucide-react';

// Store Zustand (UI Controller)
import useGisUIStore from '../../store/useGisStore';

/**
 * ============================================================================
 * SpatialLegend (Map Index / Legenda Spasial - Z-30 Floating)
 * ============================================================================
 * Menampilkan kamus indeks status penanda yang aktif di peta.
 * Hanya akan di-render jika salah satu layer ('assets' atau 'reports') diaktifkan.
 * Menjamin pembacaan peta oleh pimpinan/petugas tidak ambigu.
 */

export default function SpatialLegend() {
    const activeLayers = useGisUIStore((state) => state.activeLayers);

    const hasAssetsActive = activeLayers.includes('assets');
    const hasReportsActive = activeLayers.includes('reports');

    // Katalog Indeks Legenda Aset Dishub KBB (Information Expert)
    const assetLegendItems = useMemo(() => [
        { label: 'Aset Baik / Berfungsi', color: '#16a34a', type: 'circle' },
        { label: 'Aset Rusak / Gangguan', color: '#fbbf24', type: 'circle' },
        { label: 'Aset Kritis / Mati Total', color: '#dc2626', type: 'circle' },
        { label: 'Masuk Proses Perbaikan', color: '#3b82f6', type: 'circle' }
    ], []);

    // Katalog Indeks Legenda Pengaduan Lapangan
    const reportLegendItems = useMemo(() => [
        { label: 'Laporan Warga (Pulsing Red)', color: '#e11d48', type: 'radar' },
        { label: 'Laporan Petugas (Pulsing Amber)', color: '#f59e0b', type: 'radar' }
    ], []);

    // Jika tidak ada layer data yang diaktifkan, sembunyikan legenda agar peta bersih
    if (!hasAssetsActive && !hasReportsActive) return null;

    return (
        <div className="pointer-events-auto bg-white/95 backdrop-blur-sm border border-slate-350 shadow-2xl rounded-none w-52 animate-in fade-in slide-in-from-bottom-4 flex flex-col font-sans select-none text-slate-800">

            {/* Header Legenda */}
            <div className="flex items-center gap-1.5 px-3 py-2 bg-slate-50 border-b border-slate-200 text-left">
                <Layers size={12} className="text-[#2563eb]" />
                <h4 className="text-[9px] font-black text-slate-700 uppercase tracking-widest leading-none">
                    Legenda Spasial
                </h4>
            </div>

            {/* List Item Legenda */}
            <div className="p-3 space-y-3.5 text-left">

                {/* Bagian A: Status Kondisi Aset */}
                {hasAssetsActive && (
                    <div className="space-y-2">
                        <span className="text-[8px] font-black text-slate-400 uppercase tracking-wider block">Kondisi Aset Dishub</span>
                        <div className="space-y-1.5">
                            {assetLegendItems.map((item, idx) => (
                                <div key={idx} className="flex items-center gap-2.5">
                                    <span className="w-2.5 h-2.5 rounded-full border border-white shadow-sm shrink-0" style={{ backgroundColor: item.color }}></span>
                                    <span className="text-[10px] font-bold text-slate-600 leading-none">{item.label}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                {/* Bagian B: Indikator Pengaduan Krisis (Radar) */}
                {hasReportsActive && (
                    <div className="space-y-2 pt-2.5 border-t border-slate-100">
                        <span className="text-[8px] font-black text-slate-400 uppercase tracking-wider block">Aduan Masuk (Radar)</span>
                        <div className="space-y-2">
                            {reportLegendItems.map((item, idx) => (
                                <div key={idx} className="flex items-center gap-2.5">
                                    <div className="relative w-3.5 h-3.5 flex items-center justify-center shrink-0">
                                        {/* Detak radar cincin */}
                                        <span className="absolute inline-flex h-full w-full rounded-full opacity-65 animate-ping" style={{ backgroundColor: item.color }}></span>
                                        {/* Inti titik */}
                                        <span className="relative inline-flex rounded-full h-2 w-2 border border-white" style={{ backgroundColor: item.color }}></span>
                                    </div>
                                    <span className="text-[10px] font-bold text-slate-600 leading-none">{item.label}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

            </div>
        </div>
    );
}