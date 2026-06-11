// resources/js/components/gis/PanelOrchestrator.jsx

import React, { useMemo } from 'react';
import { X, Sliders, FolderGit, AlertTriangle, Info } from 'lucide-react';

// Store Zustand (UI Controller)
import useGisUIStore from '../../store/useGisUIStore';

// Impor sub-panel (Fase 5)
import LayerControlPanel from './panels/LayerControlPanel';

/**
 * ============================================================================
 * PanelOrchestrator (The Stacking Drawer - Z-30)
 * ============================================================================
 * Manajer tumpukan laci visual yang bertugas me-render dan menyusun laci 
 * menu secara mendatar (axis-X) dengan lebar tetap 280px (Ultra Compact).
 * Menerapkan prinsip GRASP Polymorphism & Indirection.
 */

const PANEL_WIDTH = 280; // Lebar tetap laci menu

export default function PanelOrchestrator() {
    const activePanels = useGisUIStore((state) => state.activePanels);
    const closePanel = useGisUIStore((state) => state.closePanel);
    const closePanelsToTheRight = useGisUIStore((state) => state.closePanelsToTheRight);

    // Renderer Polimorfik untuk menentukan isi konten laci (Cohesive Delegator)
    const renderPanelContent = (panel) => {
        switch (panel.type) {
            case 'konfigurasi':
                return <LayerControlPanel />;
            case 'katalog-aset':
                return (
                    <div className="p-4 text-xs font-semibold text-slate-500 text-center py-12">
                        <FolderGit className="mx-auto text-slate-300 mb-3" size={32} />
                        <p className="uppercase tracking-wider">Katalog Aset</p>
                        <p className="text-[10px] text-slate-400 font-normal mt-1 leading-normal">Sedang disiapkan untuk integrasi data PostgreSQL...</p>
                    </div>
                );
            case 'katalog-laporan':
                return (
                    <div className="p-4 text-xs font-semibold text-slate-500 text-center py-12">
                        <AlertTriangle className="mx-auto text-slate-300 mb-3" size={32} />
                        <p className="uppercase tracking-wider">Katalog Laporan</p>
                        <p className="text-[10px] text-slate-400 font-normal mt-1 leading-normal">Sedang disiapkan untuk integrasi data PostgreSQL...</p>
                    </div>
                );
            case 'tentang':
                return (
                    <div className="p-5 text-left text-slate-600 space-y-4">
                        <div className="flex items-center gap-2 border-b pb-2">
                            <Info size={16} className="text-[#2563eb]" />
                            <h4 className="text-[10px] font-black uppercase tracking-widest text-[#2563eb]">Sistem LINTAS KBB</h4>
                        </div>
                        <p className="text-[11px] leading-relaxed text-justify font-medium">
                            LINTAS (Layanan Inventaris & Sistem Tata Aset) KBB adalah instrumen geospasial taktis milik Dinas Perhubungan Kabupaten Bandung Barat untuk memantau inventaris jalan secara real-time.
                        </p>
                        <div className="bg-slate-50 p-3 border text-[9px] font-black text-slate-400 uppercase tracking-wider text-center">
                            Versi 4.0.0 (PostGIS Enabled)
                        </div>
                    </div>
                );
            case 'detil-aset':
                return (
                    <div className="p-4 text-xs font-semibold text-slate-500 text-center py-12">
                        <FolderGit className="mx-auto text-slate-300 mb-3 animate-pulse" size={32} />
                        <p className="uppercase tracking-wider">Detail Aset</p>
                        <p className="text-[10px] text-slate-400 font-normal mt-1 leading-normal">Memuat metadata {panel.data?.id_asset || 'aset'}...</p>
                    </div>
                );
            case 'detil-laporan':
                return (
                    <div className="p-4 text-xs font-semibold text-slate-500 text-center py-12">
                        <AlertTriangle className="mx-auto text-slate-300 mb-3 animate-pulse" size={32} />
                        <p className="uppercase tracking-wider">Detail Laporan</p>
                        <p className="text-[10px] text-slate-400 font-normal mt-1 leading-normal">Memuat berkas pengaduan #{panel.data?.ticket_number || 'laporan'}...</p>
                    </div>
                );
            default:
                return <div className="p-4 text-xs">Konten panel tidak dikenal.</div>;
        }
    };

    return (
        <div className="relative h-full w-full flex items-start pointer-events-none">
            {activePanels.map((panel, index) => {
                const isFloating = panel.type === 'detil-aset' || panel.type === 'detil-laporan';

                // Hitung posisi geser mendatar (X-Axis) secara otomatis
                const xOffset = index * PANEL_WIDTH;

                return (
                    <div
                        key={panel.id}
                        // pointer-events-auto dikunci pada level laci agar peta di baliknya tidak ter-klik tidak sengaja
                        className={`absolute pointer-events-auto h-full bg-white flex flex-col transition-all duration-300 ease-in-out select-none
                            ${isFloating
                                ? 'shadow-2xl border-r border-slate-200'
                                : 'border-r border-slate-200 shadow-none'
                            }`}
                        style={{
                            left: 0,
                            top: 0,
                            bottom: 0,
                            width: `${PANEL_WIDTH}px`,
                            transform: `translateX(${xOffset}px)`,
                            zIndex: 30 - index // Laci yang baru terbuka menimpa bayangan laci sebelumnya
                        }}
                    >
                        {/* HEADER LACI (Gaya GFW Siku Kaku) */}
                        <div className="px-4 h-12 border-b border-slate-200 flex justify-between items-center bg-slate-50 shrink-0">
                            <div className="flex flex-col text-left">
                                <span className="text-[9px] font-black text-[#2563eb] uppercase tracking-widest leading-none">
                                    {panel.type.replace('-', ' ')}
                                </span>
                                <h3 className="text-xs font-bold text-slate-800 truncate max-w-50 mt-1 leading-none uppercase">
                                    {panel.title}
                                </h3>
                            </div>

                            <button
                                onClick={() => closePanel(panel.id)}
                                className="p-1 rounded-none hover:bg-slate-200 text-slate-400 hover:text-rose-500 transition-colors active:scale-95 outline-none"
                            >
                                <X size={14} strokeWidth={2.5} />
                            </button>
                        </div>

                        {/* BODY LACI (Scrollable Content) */}
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