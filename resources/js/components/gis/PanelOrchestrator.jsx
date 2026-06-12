// resources/js/components/gis/PanelOrchestrator.jsx

import React, { useMemo } from 'react';
import { X, Sliders, FolderGit, AlertTriangle, Info } from 'lucide-react';

// Store Zustand (UI Controller)
import useGisUIStore from '../../store/useGisUIStore';

// Sub-Panel Komponen (GRASP Polymorphism)
import LayerControlPanel    from './panels/LayerControlPanel';
import AssetCatalogPanel    from './panels/AssetCatalogPanel';
import ReportCatalogPanel   from './panels/ReportCatalogPanel';
import DetailAssetPanel     from './panels/DetailAssetPanel';
import DetailReportPanel    from './panels/DetailReportPanel';

/**
 * ============================================================================
 * PanelOrchestrator (The Stacking Drawer - Z-30)
 * ============================================================================
 * Manajer tumpukan laci visual yang bertugas me-render dan menyusun laci
 * menu secara mendatar (axis-X) dengan lebar tetap 280px (Ultra Compact).
 * Menerapkan prinsip GRASP Polymorphism & Indirection.
 *
 * PALET WARNA: Cyber-Slate Dark Glassmorphism
 * - Background Laci  : bg-slate-900/95 + backdrop-blur-md
 * - Header Laci      : bg-slate-950/80
 * - Border            : border-white/10
 * - Teks Utama        : text-slate-100
 * - Teks Sub          : text-slate-400
 * - Aksen Aktif       : #2563EB (Electric Blue)
 */

const PANEL_WIDTH = 280;

// Label yang lebih bersih untuk header laci
const PANEL_TITLES = {
    'konfigurasi':    'Konfigurasi',
    'katalog-aset':   'Katalog Aset',
    'katalog-laporan':'Katalog Laporan',
    'tentang':        'Tentang Sistem',
    'detil-aset':     'Detail Aset',
    'detil-laporan':  'Detail Laporan',
};

export default function PanelOrchestrator() {
    const activePanels          = useGisUIStore((state) => state.activePanels);
    const closePanel            = useGisUIStore((state) => state.closePanel);
    const closePanelsToTheRight = useGisUIStore((state) => state.closePanelsToTheRight);

    // Renderer Polimorfik — setiap case mendelegasikan ke komponen spesifik (GRASP)
    const renderPanelContent = (panel) => {
        switch (panel.type) {

            case 'konfigurasi':
                return <LayerControlPanel />;

            case 'katalog-aset':
                return <AssetCatalogPanel />;

            case 'katalog-laporan':
                return <ReportCatalogPanel />;

            case 'tentang':
                return (
                    <div className="p-5 text-left text-slate-700 space-y-4">
                        <div className="flex items-center gap-2 border-b border-slate-200 pb-2">
                            <Info size={16} className="text-[#2563eb]" />
                            <h4 className="text-[10px] font-black uppercase tracking-widest text-[#2563eb]">Sistem LINTAS KBB</h4>
                        </div>
                        <p className="text-[11px] leading-relaxed text-justify font-medium text-slate-500">
                            LINTAS (Layanan Inventaris &amp; Sistem Tata Aset) KBB adalah instrumen geospasial taktis milik Dinas Perhubungan Kabupaten Bandung Barat untuk memantau inventaris jalan secara real-time.
                        </p>
                        <div className="bg-slate-50 p-3 border border-slate-200 text-[9px] font-black text-slate-400 uppercase tracking-wider text-center">
                            Versi 4.0.0 (PostGIS Enabled)
                        </div>
                    </div>
                );

            case 'detil-aset':
                return <DetailAssetPanel assetData={panel.data} panelId={panel.id} />;

            case 'detil-laporan':
                return <DetailReportPanel reportData={panel.data} panelId={panel.id} />;

            default:
                return (
                    <div className="p-4 text-xs text-slate-500">
                        Konten panel tidak dikenal.
                    </div>
                );
        }
    };

    return (
        <div className="relative h-full w-full flex items-start pointer-events-none">
            {activePanels.map((panel, index) => {
                const isFloating = panel.type === 'detil-aset' || panel.type === 'detil-laporan';

                // Hitung posisi geser mendatar (X-Axis) secara otomatis
                const xOffset = index * PANEL_WIDTH;
                const typeLabel = PANEL_TITLES[panel.type] || panel.type;

                return (
                    <div
                        key={panel.id}
                        className={`absolute pointer-events-auto h-full flex flex-col transition-transform duration-300 ease-in-out select-none
                            bg-white
                            ${isFloating
                                ? 'shadow-2xl border-r border-slate-200/80'
                                : 'border-r border-slate-200/80 shadow-md'
                            }`}
                        style={{
                            left: 0,
                            top: 0,
                            bottom: 0,
                            width: `${PANEL_WIDTH}px`,
                            transform: `translateX(${xOffset}px)`,
                            zIndex: 30 - index,
                        }}
                    >
                        {/* HEADER LACI — Vibrant Light */}
                        <div className="px-4 h-12 border-b border-slate-200/80 flex justify-between items-center bg-slate-50 shrink-0">
                            <div className="flex flex-col text-left">
                                <span className="text-[9px] font-black text-[#2563eb] uppercase tracking-widest leading-none">
                                    {typeLabel}
                                </span>
                                <h3 className="text-xs font-bold text-slate-800 truncate max-w-[200px] mt-1 leading-none uppercase">
                                    {panel.title}
                                </h3>
                            </div>

                            <button
                                onClick={() => closePanel(panel.id)}
                                className="p-1 rounded-none hover:bg-rose-50 text-slate-400 hover:text-rose-500 transition-colors active:scale-95 outline-none"
                            >
                                <X size={14} strokeWidth={2.5} />
                            </button>
                        </div>

                        {/* BODY LACI — Scrollable, white background */}
                        <div
                            className="flex-1 overflow-y-auto custom-scrollbar bg-white"
                            onClick={() => !isFloating && closePanelsToTheRight(index)}
                        >
                            {renderPanelContent(panel)}
                        </div>

                    </div>
                );
            })}
        </div>
    );
}