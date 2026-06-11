// resources/js/gis-app.jsx

import React, { useEffect } from 'react';
import { createRoot } from 'react-dom/client';

// Layer 0: Peta Spasial (Leaflet Engine)
import LintasMap from './components/gis/LintasMap';

// Layer 1: Navigasi Global (Navbar Atas)
import GisNavbar from './components/layout/GisNavbar';

// Layer 2: Jangkar Ramping (Sidebar Kiri)
import GisSidebar from './components/layout/GisSidebar';

// Layer 3: Pengendali Laci Bertumpuk (Panel Orchestrator)
import PanelOrchestrator from './components/gis/PanelOrchestrator';

// Layer 4: HUD & Legenda (Heads-Up Display)
import MapHUD from './components/gis/MapHUD';
import SpatialLegend from './components/gis/SpatialLegend';

// Store Zustand untuk Hidrasi Data Otomatis saat Bootstrapping
import useLitasStore from './store/useLitasStore';

/**
 * ============================================================================
 * GISMainApp (The Root Orchestrator & App Entry Point)
 * ============================================================================
 * Merangkai seluruh tumpukan visual (Z-0 hingga Z-50) menjadi satu kesatuan 
 * ekosistem spasial tertutup yang bebas dari interferensi Blade Engine standar.
 * Menjamin tidak ada scrollbar bocor, rendering peta bleed 100% viewport.
 */

function GISMainApp() {
    const fetchAssets = useLitasStore((state) => state.fetchAssets);
    const fetchRecentReports = useLitasStore((state) => state.fetchRecentReports);
    const fetchReportStats = useLitasStore((state) => state.fetchReportStats);

    // [GRASP INFORMATION EXPERT]
    // Hidrasi data spasial awal dari PostgreSQL/PostGIS ke memori Zustand Store 
    // dilakukan sekali di tingkat Root saat aplikasi dipasang (Mounted).
    useEffect(() => {
        const hydrateDatabase = async () => {
            try {
                await Promise.all([
                    fetchAssets(),
                    fetchRecentReports(),
                    fetchReportStats()
                ]);
                console.log('✅ [LINTAS KBB Spasial] Hidrasi database spasial PostgreSQL berhasil.');
            } catch (error) {
                console.error('❌ [LINTAS KBB Spasial] Gagal memuat data awal dari backend:', error);
            }
        };

        hydrateDatabase();
    }, [fetchAssets, fetchRecentReports, fetchReportStats]);

    return (
        <div className="h-screen w-screen flex flex-col overflow-hidden bg-[#0A192F] text-slate-800 select-none">

            {/* =================================================================
                LAYER 1: GLOBAL CONTEXT (h-16, Z-50)
                Berada di puncak paling atas, memotong koordinat Y=0 ke Y=64.
            ================================================================= */}
            <header className="w-full h-16 shrink-0 relative z-50">
                <GisNavbar />
            </header>

            {/* =================================================================
                WORKSPACE WRAPPER (Sisa tinggi layar penuh)
                Wadah sisa ruang di bawah Navbar setinggi 100vh - 64px.
            ================================================================= */}
            <div className="flex-1 flex w-full relative overflow-hidden">

                {/* 
                    LAYER 0: THE INFINITE CANVAS (Z-0)
                    Mengisi penuh sisa ruang workspace di dasar visual peta.
                */}
                <div className="absolute inset-0 z-0">
                    <LintasMap />
                </div>

                {/* 
                    LAYER 2: THE SLIM ANCHOR (w-16, Z-40)
                    Sidebar ramping yang berdiri tegak di tepi kiri workspace.
                */}
                <div className="absolute top-0 bottom-0 left-0 w-16 z-40">
                    <GisSidebar />
                </div>

                {/* 
                    LAYER 3: THE STACKING DRAWERS (Z-30)
                    Sliding panel orchestrator dimulai di samping sidebar (left-16).
                    pointer-events-none dikunci agar area kosong di kanan panel tetap tembus ke peta.
                */}
                <div className="absolute top-0 bottom-0 left-16 z-30 pointer-events-none">
                    <PanelOrchestrator />
                </div>

                {/* 
                    LAYER 4: HUD & SPATIAL LEGEND (Z-30)
                    Sumbu mengambang di pojok kanan bawah peta (Z-30).
                    Gaya bertumpuk flex-col untuk navigasi kustom.
                */}
                <div className="absolute bottom-8 right-8 z-30 pointer-events-none flex flex-col items-end gap-4">
                    {/* Legenda visual kondisi aset */}
                    <SpatialLegend />

                    {/* Kontrol zoom taktis Leaflet */}
                    <MapHUD />
                </div>

            </div>

        </div>
    );
}

// BOOTSTRAPPING ENGINE: Pasang React DOM ke kontainer Blade Laravel
const container = document.getElementById('gis-root');
if (container) {
    const root = createRoot(container);
    root.render(<GISMainApp />);
}