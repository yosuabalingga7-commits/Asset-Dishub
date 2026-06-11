// resources/js/components/gis/ReportMarkers.jsx

import React, { useMemo } from 'react';
import { Marker, Tooltip } from 'react-leaflet';
import L from 'leaflet';

// Store Zustand (UI dan Domain Data)
import useGisUIStore from '../../store/useGisUIStore';
import useLitasStore from '../../store/useLitasStore';

/**
 * ============================================================================
 * ReportMarkers (Pulsing Radar Layer - OPTIMIZED)
 * ============================================================================
 * Menyadap dan merender seluruh laporan pengaduan masuk yang butuh verifikasi.
 * DIOPTIMALKAN: Menghilangkan useEffect auto-fetcher untuk menghindari loop hidrasi
 * saat database kosong. Hidrasi didelegasikan terpusat di gis-app.jsx.
 */

const createRadarIcon = (source) => {
    const isPublic = source === 'masyarakat';
    const colorClass = isPublic ? 'bg-rose-600' : 'bg-amber-500';
    const ringClass = isPublic ? 'bg-rose-500' : 'bg-amber-400';

    return L.divIcon({
        className: 'custom-radar-pin bg-transparent border-none',
        html: `
            <div class="relative w-8 h-8 flex items-center justify-center" style="transform: translate(-16px, -16px);">
                <span class="absolute inline-flex h-7 w-7 rounded-full ${ringClass} opacity-75 animate-ping"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 ${colorClass} border-2 border-white shadow-xl flex items-center justify-center">
                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                </span>
            </div>
        `,
        iconSize: [32, 32],
        iconAnchor: [0, 0]
    });
};

export default function ReportMarkers() {
    const recentReports = useLitasStore((state) => state.recentReports);
    const { openPanel, closePanelsToTheRight, setSelectedReportId } = useGisUIStore();

    const activeReports = useMemo(() => {
        return recentReports.map(report => {
            const latVal = parseFloat(report.lat);
            const lngVal = parseFloat(report.lng);

            if (isNaN(latVal) || isNaN(lngVal)) return null;

            return {
                ...report,
                lat: latVal,
                lng: lngVal
            };
        }).filter(Boolean).filter(r => r.status.toLowerCase() !== 'selesai' && r.status.toLowerCase() !== 'ditolak');
    }, [recentReports]);

    const handleReportClick = (report, e) => {
        e.originalEvent.stopPropagation();
        e.target._map.flyTo([report.lat, report.lng], 16, { animate: true });
        setSelectedReportId(report.id);
        closePanelsToTheRight(-1);
        openPanel('detil-laporan', `Investigasi: ${report.ticket_number}`, report);
    };

    return (
        <React.Fragment>
            {activeReports.map((report) => (
                <Marker
                    key={`report-${report.id}`}
                    position={[report.lat, report.lng]}
                    icon={createRadarIcon(report.source)}
                    eventHandlers={{
                        click: (e) => handleReportClick(report, e)
                    }}
                >
                    <Tooltip direction="top" offset={[0, -10]} opacity={0.95}>
                        <div className="font-sans text-xs text-slate-800 p-1 space-y-1 text-left min-w-[150px]">
                            <div className="flex justify-between items-center gap-2 border-b border-slate-100 pb-1">
                                <span className="text-[8px] font-black text-rose-600 bg-rose-50 px-1.5 py-0.5 uppercase tracking-wider leading-none">
                                    Aduan {report.source}
                                </span>
                                <span className="font-mono text-[9px] font-bold text-slate-400">
                                    #{report.ticket_number}
                                </span>
                            </div>
                            <h4 className="font-bold text-slate-900 leading-tight">
                                {report.judul_laporan}
                            </h4>
                            <p className="text-[10px] font-medium text-slate-500 line-clamp-1 italic">
                                "{report.deskripsi_keluhan || report.deskripsi || '-'}"
                            </p>
                        </div>
                    </Tooltip>
                </Marker>
            ))}
        </React.Fragment>
    );
}