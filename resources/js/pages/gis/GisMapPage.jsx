// resources/js/pages/gis/GisMapPage.jsx
// Wraps the existing GIS React island into the SPA router context.
// The full GIS app (LintasMap, PanelOrchestrator, etc.) is preserved as-is.
// It is simply re-rendered here inside the SPA shell instead of via Blade.

import React, { useEffect } from 'react';
import LintasMap from '../../components/gis/LintasMap';
import GisSidebar from '../../components/layout/GisSidebar';
import PanelOrchestrator from '../../components/gis/PanelOrchestrator';
import { CoordinateTracker, ZoomControls } from '../../components/gis/MapHUD';
import SpatialLegend from '../../components/gis/SpatialLegend';
import useLitasStore from '../../store/useLitasStore';

export default function GisMapPage() {
    const fetchAssets = useLitasStore((state) => state.fetchAssets);
    const fetchRecentReports = useLitasStore((state) => state.fetchRecentReports);
    const fetchReportStats = useLitasStore((state) => state.fetchReportStats);

    useEffect(() => {
        const hydrate = async () => {
            await Promise.all([fetchAssets(), fetchRecentReports(), fetchReportStats()]);
        };
        hydrate();
    }, [fetchAssets, fetchRecentReports, fetchReportStats]);

    return (
        // Full bleed — overrides the lintas-content padding for map view
        <div
            className="absolute inset-0 flex"
            style={{ top: 0, left: 0, right: 0, bottom: 0 }}
        >
            {/* Z-0: Map canvas */}
            <div className="absolute inset-0 z-0">
                <LintasMap />
            </div>

            {/* Z-40: Slim sidebar */}
            <div className="absolute top-0 bottom-0 left-0 w-16 z-40">
                <GisSidebar />
            </div>

            {/* Z-30: Stacking panel drawers */}
            <div className="absolute top-0 bottom-0 left-16 z-30 pointer-events-none">
                <PanelOrchestrator />
            </div>

            {/* Z-30: HUD bottom-right */}
            <div className="absolute bottom-6 right-6 z-30 pointer-events-none flex flex-row items-end gap-3">
                <div className="flex flex-col items-end gap-3 pointer-events-none">
                    <CoordinateTracker />
                    <SpatialLegend />
                </div>
                <div className="pointer-events-none">
                    <ZoomControls />
                </div>
            </div>
        </div>
    );
}
