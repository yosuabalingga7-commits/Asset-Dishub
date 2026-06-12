// resources/js/components/gis/panels/ReportCatalogPanel.jsx

import React, { useState, useMemo, useEffect } from 'react';
import { Search, ChevronDown, AlertTriangle } from 'lucide-react';

// Store Zustand (UI dan Domain Data)
import useGisUIStore from '../../../store/useGisUIStore';
import useLitasStore from '../../../store/useLitasStore';

/**
 * ============================================================================
 * ReportCatalogPanel (Katalog Laporan Sub-Panel)
 * ============================================================================
 * Menyediakan antarmuka pencarian dan penelusuran laporan pengaduan.
 */

export default function ReportCatalogPanel() {
    const { recentReports, fetchRecentReports, isReportsLoading } = useLitasStore();
    const { openPanel, closePanelsToTheRight, setSelectedReportId, selectedReportId } = useGisUIStore();

    const [searchQuery, setSearchQuery] = useState('');
    const [expandedStatus, setExpandedStatus] = useState('Masuk');

    // Auto-hydrate data secara global jika penyimpanan lokal masih kosong saat laci dibuka
    useEffect(() => {
        if (recentReports.length === 0 && !isReportsLoading) {
            fetchRecentReports();
        }
    }, [recentReports.length, isReportsLoading, fetchRecentReports]);

    // FILTER & GROUPING DATA REAL-TIME
    const groupedAndFilteredReports = useMemo(() => {
        const groups = {};
        const query = searchQuery.toLowerCase().trim();

        recentReports.forEach(report => {
            // Logika pencarian fuzzy
            const matchesQuery = !query ||
                (report.judul_laporan && report.judul_laporan.toLowerCase().includes(query)) ||
                (report.ticket_number && report.ticket_number.toLowerCase().includes(query)) ||
                (report.nama_pelapor && report.nama_pelapor.toLowerCase().includes(query));

            if (matchesQuery) {
                const statusName = report.status || 'Masuk';
                if (!groups[statusName]) groups[statusName] = [];
                groups[statusName].push(report);
            }
        });

        return groups;
    }, [recentReports, searchQuery]);

    const handleReportClick = (report) => {
        const latVal = parseFloat(report.lat);
        const lngVal = parseFloat(report.lng);

        if (isNaN(latVal) || isNaN(lngVal)) {
            console.warn('[ReportCatalogPanel] Koordinat laporan tidak valid:', report);
            return;
        }

        // 1. Dihapus atas instruksi (Prohibited Map Camera Movement)
        // 2. Tandai ID laporan terpilih di dalam penyimpanan keadaan visual
        setSelectedReportId(report.id);

        // 3. Bersihkan tumpukan laci melayang sebelah kanan
        closePanelsToTheRight(-1);

        // 4. Tampilkan panel rincian spesifik laporan
        openPanel('detil-laporan', `Investigasi: ${report.ticket_number}`, report);
    };

    return (
        <div className="flex flex-col h-full bg-white pb-6 font-sans text-slate-800 text-left">
            {/* SEARCH BAR (Sticky Header) */}
            <div className="px-4 py-2.5 border-b border-slate-200 bg-slate-50 sticky top-0 z-10">
                <div className="relative group">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#2563eb]" size={14} />
                    <input
                        type="text"
                        placeholder="Cari judul, tiket, pelapor..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="w-full bg-white border border-slate-200 rounded-none py-1.5 pl-8 pr-3 text-[11px] font-semibold text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#2563eb] transition-all"
                    />
                </div>
            </div>

            {/* CATALOG LIST CONTAINER */}
            <div className="flex-1 overflow-y-auto custom-scrollbar flex flex-col">
                {isReportsLoading ? (
                    <div className="p-8 text-center space-y-3">
                        <div className="w-8 h-8 border-4 border-[#2563eb] border-t-transparent rounded-full animate-spin mx-auto"></div>
                        <p className="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Memuat Database PostgreSQL...</p>
                    </div>
                ) : Object.keys(groupedAndFilteredReports).length > 0 ? (
                    Object.keys(groupedAndFilteredReports).map((statusName) => {
                        const isExpanded = expandedStatus === statusName;
                        const statusReports = groupedAndFilteredReports[statusName] || [];

                        return (
                            <div key={statusName} className="flex flex-col border-b border-slate-100">
                                {/* Akordion Header */}
                                <button
                                    onClick={() => setExpandedStatus(isExpanded ? null : statusName)}
                                    className="flex items-center justify-between px-4 py-3 bg-slate-50 hover:bg-slate-100/60 border-b border-slate-100 transition-colors w-full text-left rounded-none outline-none"
                                >
                                    <div className="flex items-center gap-2 min-w-0">
                                        <ChevronDown
                                            size={14}
                                            className={`text-slate-400 shrink-0 transition-transform duration-200 ${isExpanded ? 'rotate-180' : ''}`}
                                        />
                                        <span className="text-[11px] font-black uppercase text-slate-700 truncate tracking-wide">
                                            Status: {statusName}
                                        </span>
                                    </div>
                                    <span className="bg-slate-200 text-slate-600 font-mono text-[9px] font-black px-1.5 py-0.5 rounded-sm shadow-inner shrink-0">
                                        {statusReports.length}
                                    </span>
                                </button>

                                {/* List Laporan */}
                                {isExpanded && (
                                    <div className="flex flex-col bg-white animate-in slide-in-from-top-1 duration-150">
                                        {statusReports.map((report) => {
                                            const isSelected = selectedReportId === report.id;
                                            return (
                                                <button
                                                    key={report.id}
                                                    onClick={() => handleReportClick(report)}
                                                    className={`flex items-center gap-3 px-6 py-2.5 border-b border-slate-100 hover:bg-slate-50 transition-colors w-full text-left rounded-none outline-none border-l-[3px]
                                                        ${isSelected ? 'bg-[#2563eb]/5 border-l-[#2563eb]' : 'border-l-transparent'}`}
                                                >
                                                    <div className="flex flex-col leading-none min-w-0">
                                                        <span className={`text-[11px] leading-tight truncate ${isSelected ? 'font-bold text-[#2563eb]' : 'font-semibold text-slate-800'}`}>
                                                            {report.judul_laporan}
                                                        </span>
                                                        <span className="text-[9px] text-slate-400 font-bold uppercase tracking-wide mt-1">
                                                            #{report.ticket_number} • {report.nama_pelapor || 'Anonim'}
                                                        </span>
                                                    </div>
                                                </button>
                                            );
                                        })}
                                    </div>
                                )}
                            </div>
                        );
                    })
                ) : (
                    <div className="p-8 text-center text-slate-400 space-y-2">
                        <AlertTriangle className="mx-auto text-slate-350" size={24} />
                        <p className="text-[10px] font-black uppercase tracking-wider">Laporan tidak ditemukan</p>
                    </div>
                )}
            </div>
        </div>
    );
}
