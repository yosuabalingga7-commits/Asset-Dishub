// resources/js/components/gis/panels/ReportCatalogPanel.jsx

import React, { useState, useMemo, useEffect } from 'react';
import { Search, AlertTriangle, Radio, Users, Shield, Clipboard } from 'lucide-react';

// Store Zustand (UI dan Domain Data)
import useGisUIStore from '../../../store/useGisUIStore';
import useLitasStore from '../../../store/useLitasStore';

/**
 * ============================================================================
 * ReportCatalogPanel (Pengaduan Explorer Sub-Panel)
 * ============================================================================
 * Menyediakan antarmuka pencarian dan penelusuran laporan pengaduan terbaru.
 * Mengelompokkan laporan berdasarkan sumber: Masyarakat vs Petugas.
 *
 * VIBRANT LIGHT THEME: bg-white, border-slate-200/80, text-slate-700.
 */

const SOURCE_META = {
    'masyarakat': { icon: Users, label: 'Laporan Masyarakat', color: '#e11d48' },
    'petugas':    { icon: Shield, label: 'Laporan Petugas',    color: '#f59e0b' },
    'Lainnya':    { icon: Clipboard, label: 'Lainnya',            color: '#64748b' },
};

const STATUS_COLOR = {
    masuk:    { bg: '#dc2626', label: 'MASUK' },
    proses:   { bg: '#2563eb', label: 'PROSES' },
    selesai:  { bg: '#16a34a', label: 'SELESAI' },
    ditolak:  { bg: '#64748b', label: 'DITOLAK' },
};

export default function ReportCatalogPanel() {
    const recentReports = useLitasStore((state) => state.recentReports);
    const fetchRecentReports = useLitasStore((state) => state.fetchRecentReports);
    const isReportsLoading = useLitasStore((state) => state.isReportsLoading);

    const openPanel = useGisUIStore((state) => state.openPanel);
    const closePanelsToTheRight = useGisUIStore((state) => state.closePanelsToTheRight);
    const setSelectedReportId = useGisUIStore((state) => state.setSelectedReportId);
    const selectedReportId = useGisUIStore((state) => state.selectedReportId);

    const [searchQuery, setSearchQuery] = useState('');
    const [expandedSource, setExpandedSource] = useState(null);

    // Auto-hydrate data secara global jika penyimpanan lokal masih kosong saat laci dibuka
    useEffect(() => {
        if (recentReports.length === 0 && !isReportsLoading) {
            fetchRecentReports();
        }
    }, [recentReports.length, isReportsLoading, fetchRecentReports]);

    // 1. FILTER & GROUPING DATA REAL-TIME O(N) berdasarkan sumber laporan
    const groupedAndFilteredReports = useMemo(() => {
        const groups = {};
        const query = searchQuery.toLowerCase().trim();

        recentReports.forEach(report => {
            const matchesQuery = !query ||
                (report.judul_laporan && report.judul_laporan.toLowerCase().includes(query)) ||
                (report.ticket_number && report.ticket_number.toLowerCase().includes(query)) ||
                (report.alamat && report.alamat.toLowerCase().includes(query)) ||
                (report.nama_pelapor && report.nama_pelapor.toLowerCase().includes(query));

            if (matchesQuery) {
                const src = (report.source || 'Lainnya').toLowerCase();
                const key = src === 'petugas' ? 'petugas' : src === 'masyarakat' ? 'masyarakat' : 'Lainnya';
                if (!groups[key]) groups[key] = [];
                groups[key].push(report);
            }
        });

        return groups;
    }, [recentReports, searchQuery]);

    // Set akordion pertama terbuka otomatis saat pencarian diketik
    useEffect(() => {
        if (searchQuery.trim() !== '') {
            const firstKey = Object.keys(groupedAndFilteredReports)[0];
            if (firstKey) setExpandedSource(firstKey);
        }
    }, [searchQuery, groupedAndFilteredReports]);

    const handleReportClick = (report) => {
        // 1. Tandai ID laporan terpilih di penyimpanan keadaan visual
        setSelectedReportId(report.id);

        // 2. Bersihkan laci melayang sebelah kanan
        closePanelsToTheRight(-1);

        // 3. Tampilkan panel rincian spesifik laporan
        openPanel('detil-laporan', `Laporan: #${report.ticket_number}`, report);
    };

    return (
        <div className="flex flex-col h-full bg-white pb-6 font-sans text-slate-700 text-left">

            {/* SEARCH BAR (Sticky Header) */}
            <div className="px-4 py-2.5 border-b border-slate-200 bg-slate-50/80 sticky top-0 z-10 backdrop-blur-sm">
                <div className="relative group">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#e11d48]" size={14} />
                    <input
                        type="text"
                        placeholder="Cari tiket, judul, atau alamat..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="w-full bg-white border border-slate-200 rounded-none py-1.5 pl-8 pr-3 text-[11px] font-semibold text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#e11d48] transition-all"
                    />
                </div>
            </div>

            {/* CATALOG LIST CONTAINER */}
            <div className="flex-1 overflow-y-auto custom-scrollbar flex flex-col">
                {isReportsLoading ? (
                    <div className="p-8 text-center space-y-3">
                        <div className="w-8 h-8 border-4 border-[#e11d48] border-t-transparent rounded-full animate-spin mx-auto"></div>
                        <p className="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Memuat Laporan...</p>
                    </div>
                ) : Object.keys(groupedAndFilteredReports).length > 0 ? (
                    Object.keys(groupedAndFilteredReports).map((srcKey) => {
                        const isExpanded = expandedSource === srcKey;
                        const srcReports = groupedAndFilteredReports[srcKey] || [];
                        const meta = SOURCE_META[srcKey] || SOURCE_META['Lainnya'];

                        return (
                            <div key={srcKey} className="flex flex-col border-b border-slate-200/80">

                                {/* Akordion Header */}
                                <button
                                    onClick={() => setExpandedSource(isExpanded ? null : srcKey)}
                                    className="flex items-center justify-between px-4 py-3 bg-slate-50 hover:bg-slate-100/80 border-b border-slate-200/80 transition-colors w-full text-left rounded-none outline-none"
                                >
                                    <div className="flex items-center gap-2 min-w-0">
                                        <Radio
                                            size={13}
                                            className={`shrink-0 transition-transform duration-200 ${isExpanded ? 'text-[#e11d48]' : 'text-slate-400'}`}
                                        />
                                        <span className="shrink-0 flex items-center justify-center w-5 h-5 rounded bg-slate-100 border border-slate-200/60">
                                            <meta.icon size={12} style={{ color: meta.color }} />
                                        </span>
                                        <span className="text-[11px] font-black uppercase text-slate-600 truncate tracking-wide">
                                            {meta.label}
                                        </span>
                                    </div>
                                    <span className="bg-slate-200/60 text-slate-600 font-mono text-[9px] font-black px-1.5 py-0.5 rounded-sm shadow-inner shrink-0 border border-slate-300/50">
                                        {srcReports.length}
                                    </span>
                                </button>

                                {/* Daftar Laporan di dalam Rumpun Sumber */}
                                {isExpanded && (
                                    <div className="flex flex-col bg-white animate-in slide-in-from-top-1 duration-150">
                                        {srcReports.map((report) => {
                                            const isSelected = selectedReportId === report.id;
                                            const statusKey = (report.status || 'masuk').toLowerCase();
                                            const statusMeta = STATUS_COLOR[statusKey] || STATUS_COLOR.masuk;

                                            return (
                                                <button
                                                    key={report.id}
                                                    onClick={() => handleReportClick(report)}
                                                    className={`flex items-center gap-3 px-5 py-2.5 border-b border-slate-100 hover:bg-slate-50 transition-colors w-full text-left rounded-none outline-none border-l-[3px]
                                                        ${isSelected ? 'bg-rose-50 border-l-[#e11d48]' : 'border-l-transparent'}`}
                                                >
                                                    {/* Penanda status dinamis */}
                                                    <span
                                                        className="w-2.5 h-2.5 rounded-full shrink-0 shadow-sm border border-white"
                                                        style={{ backgroundColor: statusMeta.bg }}
                                                    />

                                                    <div className="flex flex-col leading-none min-w-0 flex-1">
                                                        <span className={`text-[11px] leading-tight truncate ${isSelected ? 'font-bold text-rose-600' : 'font-semibold text-slate-700'}`}>
                                                            {report.judul_laporan}
                                                        </span>
                                                        <div className="flex items-center gap-2 mt-1">
                                                            <span className="text-[9px] text-slate-400 font-bold uppercase tracking-wide">
                                                                #{report.ticket_number}
                                                            </span>
                                                            <span
                                                                className="text-[8px] font-black uppercase tracking-wide px-1 py-0.5 border border-slate-200 leading-none"
                                                                style={{ backgroundColor: statusMeta.bg + '15', color: statusMeta.bg }}
                                                            >
                                                                {statusMeta.label}
                                                            </span>
                                                        </div>
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
                        <AlertTriangle className="mx-auto text-slate-300" size={24} />
                        <p className="text-[10px] font-black uppercase tracking-wider">Laporan tidak ditemukan</p>
                    </div>
                )}
            </div>
        </div>
    );
}
