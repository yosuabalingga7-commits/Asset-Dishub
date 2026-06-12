import React, { useState, useEffect } from 'react';
import { useSearchParams, Link } from 'react-router-dom';
import api from '../../services/api';
import { Search, Filter, ClipboardList, CheckCircle, Clock, AlertOctagon, X, User, MapPin, Phone, Eye, ShieldCheck, ChevronLeft, ChevronRight } from 'lucide-react';

export default function ReportList() {
    const [searchParams, setSearchParams] = useSearchParams();

    // Bind filters & pagination directly to URL search params
    const page = parseInt(searchParams.get('page') || '1', 10);
    const source = searchParams.get('source') || '';
    const status = searchParams.get('status') || '';
    const search = searchParams.get('search') || '';

    const [reportsData, setReportsData] = useState({ data: [], meta: {} });
    const [isLoading, setIsLoading] = useState(true);
    const [selectedReport, setSelectedReport] = useState(null);
    const [isDrawerOpen, setIsDrawerOpen] = useState(false);
    
    // Form state for validation
    const [kepemilikan, setKepemilikan] = useState('Dishub');
    const [catatanAdmin, setCatatanAdmin] = useState('');
    const [isSubmittingValidation, setIsSubmittingValidation] = useState(false);

    // Fetch reports on parameter change
    useEffect(() => {
        let active = true;
        setIsLoading(true);

        api.get('/v1/reports', {
            params: { page, source, status, search }
        })
        .then(res => {
            if (active) {
                setReportsData(res);
                setIsLoading(false);
            }
        })
        .catch(err => {
            if (active) {
                console.error('[ReportList] Gagal memuat data:', err);
                setIsLoading(false);
            }
        });

        return () => { active = false; };
    }, [page, source, status, search]);

    // Update URL query parameters helper
    const updateFilters = (newFilters) => {
        const params = new URLSearchParams(searchParams);
        Object.entries(newFilters).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                params.set(key, value);
            } else {
                params.delete(key);
            }
        });
        
        // Reset page to 1 on any filter change
        if (!newFilters.hasOwnProperty('page')) {
            params.set('page', '1');
        }
        
        setSearchParams(params);
    };

    // Optimistic UI update logic for validation
    const handleValidateReport = async (reportId) => {
        const originalData = { ...reportsData };
        const originalSelectedReport = selectedReport ? { ...selectedReport } : null;

        // 1. Optimistic Update: Update UI instantly
        setReportsData(prev => {
            const updated = prev.data.map(item => {
                if (item.id === reportId) {
                    return {
                        ...item,
                        is_validated: true,
                        kepemilikan: kepemilikan,
                        status: 'Proses Perbaikan',
                        catatan_admin: catatanAdmin || 'Validasi otomatis.'
                    };
                }
                return item;
            });
            return { ...prev, data: updated };
        });

        if (selectedReport && selectedReport.id === reportId) {
            setSelectedReport(prev => ({
                ...prev,
                is_validated: true,
                kepemilikan: kepemilikan,
                status: 'Proses Perbaikan',
                catatan_admin: catatanAdmin || 'Validasi otomatis.'
            }));
        }

        setIsSubmittingValidation(true);

        // 2. Perform API Call
        try {
            await api.patch(`/v1/reports/${reportId}/validate`, {
                kepemilikan,
                catatan_admin: catatanAdmin
            });
            setIsSubmittingValidation(false);
            setCatatanAdmin('');
        } catch (error) {
            console.error('[ReportList] Gagal memvalidasi, rolling back UI:', error);
            // Rollback UI on failure
            setReportsData(originalData);
            if (originalSelectedReport) {
                setSelectedReport(originalSelectedReport);
            }
            alert(error.message || 'Gagal memvalidasi laporan. Silakan coba kembali.');
            setIsSubmittingValidation(false);
        }
    };

    const handleSearchChange = (e) => {
        updateFilters({ search: e.target.value });
    };

    const handleSourceChange = (e) => {
        updateFilters({ source: e.target.value });
    };

    const handleStatusChange = (e) => {
        updateFilters({ status: e.target.value });
    };

    const handlePageChange = (newPage) => {
        updateFilters({ page: newPage.toString() });
    };

    const openDetails = (report) => {
        setSelectedReport(report);
        setKepemilikan(report.kepemilikan || 'Dishub');
        setCatatanAdmin(report.catatan_admin || '');
        setIsDrawerOpen(true);
    };

    const closeDetails = () => {
        setIsDrawerOpen(false);
    };

    // Helper for status badge colors
    const renderStatusBadge = (statusStr) => {
        const lower = (statusStr || '').toLowerCase();
        let bg = 'bg-slate-100 text-slate-700 border-slate-300';
        
        if (lower === 'masuk') {
            bg = 'bg-yellow-50 text-yellow-700 border-yellow-300';
        } else if (lower === 'proses perbaikan' || lower === 'proses') {
            bg = 'bg-blue-50 text-blue-700 border-blue-300';
        } else if (lower === 'selesai' || lower === 'baik') {
            bg = 'bg-green-50 text-green-700 border-green-300';
        } else if (lower === 'ditolak') {
            bg = 'bg-red-50 text-red-700 border-red-300';
        }

        return (
            <span className={`inline-flex items-center px-2 py-0.5 text-xs font-mono border rounded-none ${bg}`}>
                {statusStr || 'MASUK'}
            </span>
        );
    };

    return (
        <div className="font-sans text-slate-800 flex flex-col gap-6 relative min-h-screen">
            {/* Header section */}
            <div className="border-b border-slate-300 pb-4">
                <h1 className="text-xl font-bold tracking-tight uppercase text-slate-900">
                    Daftar Laporan Masuk
                </h1>
                <p className="text-xs text-slate-500 mt-1">
                    Kelola dan validasi semua laporan kerusakan infrastruktur dari warga dan petugas lapangan.
                </p>
            </div>

            {/* Filter Hub */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white border border-slate-300 p-4 rounded-none shadow-none">
                {/* Search */}
                <div className="relative">
                    <span className="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <Search size={16} />
                    </span>
                    <input
                        type="text"
                        value={search}
                        onChange={handleSearchChange}
                        placeholder="Cari tiket, judul, pelapor..."
                        className="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-none bg-slate-50 focus:outline-none focus:border-blue-700"
                    />
                </div>

                {/* Source Filter */}
                <div>
                    <select
                        value={source}
                        onChange={handleSourceChange}
                        className="w-full px-3 py-2 text-xs border border-slate-300 rounded-none bg-slate-50 focus:outline-none focus:border-blue-700"
                    >
                        <option value="">Semua Sumber Laporan</option>
                        <option value="masyarakat">Masyarakat Umum</option>
                        <option value="petugas">Petugas Lapangan</option>
                    </select>
                </div>

                {/* Status Filter */}
                <div>
                    <select
                        value={status}
                        onChange={handleStatusChange}
                        className="w-full px-3 py-2 text-xs border border-slate-300 rounded-none bg-slate-50 focus:outline-none focus:border-blue-700"
                    >
                        <option value="">Semua Status</option>
                        <option value="masuk">Masuk / Baru</option>
                        <option value="Proses Perbaikan">Proses Perbaikan</option>
                        <option value="Selesai">Selesai / Diperbaiki</option>
                        <option value="Ditolak">Ditolak</option>
                    </select>
                </div>

                {/* Reset Filters button */}
                <div className="flex justify-end">
                    <button
                        onClick={() => updateFilters({ source: '', status: '', search: '', page: '1' })}
                        className="border border-slate-300 px-4 py-2 text-xs font-bold uppercase hover:bg-slate-50 active:bg-slate-100 transition-colors w-full md:w-auto rounded-none shadow-none"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>

            {/* Dense Data Table */}
            <div className="border border-slate-300 bg-white rounded-none shadow-none overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="border-b border-slate-300 bg-slate-100 text-slate-700 text-xs font-bold uppercase">
                                <th className="p-3 border-r border-slate-300">No. Tiket</th>
                                <th className="p-3 border-r border-slate-300">Judul Temuan</th>
                                <th className="p-3 border-r border-slate-300">Sumber</th>
                                <th className="p-3 border-r border-slate-300">Pelapor</th>
                                <th className="p-3 border-r border-slate-300">Status</th>
                                <th className="p-3 border-r border-slate-300">Aset Validasi</th>
                                <th className="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-200 text-xs">
                            {isLoading ? (
                                <tr>
                                    <td colSpan={7} className="p-8 text-center text-slate-500 font-medium">
                                        Memuat data laporan...
                                    </td>
                                </tr>
                            ) : reportsData.data.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="p-8 text-center text-slate-500 italic">
                                        Tidak ada data laporan ditemukan.
                                    </td>
                                </tr>
                            ) : (
                                reportsData.data.map((report) => (
                                    <tr key={report.id} className="hover:bg-slate-50 transition-colors">
                                        <td className="p-3 border-r border-slate-200 font-mono font-bold text-blue-700">
                                            {report.ticket_number}
                                        </td>
                                        <td className="p-3 border-r border-slate-200 font-medium text-slate-900">
                                            {report.judul_laporan}
                                        </td>
                                        <td className="p-3 border-r border-slate-200 uppercase font-bold text-slate-500 text-[10px] tracking-wider">
                                            {report.source}
                                        </td>
                                        <td className="p-3 border-r border-slate-200">
                                            <div className="font-bold">{report.nama_pelapor || report.nama_petugas}</div>
                                            <div className="text-[10px] text-slate-500">{report.kontak_pelapor || report.no_wa}</div>
                                        </td>
                                        <td className="p-3 border-r border-slate-200 text-center">
                                            {renderStatusBadge(report.status)}
                                        </td>
                                        <td className="p-3 border-r border-slate-200 font-mono font-medium">
                                            {report.is_validated ? (
                                                <span className="text-green-700 font-bold uppercase">
                                                    {report.kepemilikan}
                                                </span>
                                            ) : (
                                                <span className="text-slate-400 italic">Belum Triage</span>
                                            )}
                                        </td>
                                        <td className="p-3 text-center">
                                            <button
                                                onClick={() => openDetails(report)}
                                                className="inline-flex items-center gap-1.5 bg-blue-700 hover:bg-blue-800 text-white font-bold px-3 py-1.5 uppercase text-[10px] tracking-tight transition-colors rounded-none shadow-none"
                                            >
                                                <Eye size={12} />
                                                <span>Detail</span>
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Table Pagination footer */}
                {!isLoading && reportsData.meta && reportsData.meta.last_page > 1 && (
                    <div className="border-t border-slate-300 p-4 bg-slate-100 flex items-center justify-between text-xs">
                        <span className="text-slate-600 font-mono">
                            Halaman {reportsData.meta.current_page} dari {reportsData.meta.last_page} ({reportsData.meta.total} Laporan)
                        </span>
                        <div className="flex gap-2">
                            <button
                                disabled={page <= 1}
                                onClick={() => handlePageChange(page - 1)}
                                className="border border-slate-300 p-2 hover:bg-slate-200 disabled:opacity-50 disabled:hover:bg-transparent transition-colors rounded-none bg-white text-slate-700"
                                aria-label="Previous Page"
                            >
                                <ChevronLeft size={16} />
                            </button>
                            <button
                                disabled={page >= reportsData.meta.last_page}
                                onClick={() => handlePageChange(page + 1)}
                                className="border border-slate-300 p-2 hover:bg-slate-200 disabled:opacity-50 disabled:hover:bg-transparent transition-colors rounded-none bg-white text-slate-700"
                                aria-label="Next Page"
                            >
                                <ChevronRight size={16} />
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Slide-out validation Drawer / Modal with flat Overlay */}
            {isDrawerOpen && selectedReport && (
                <div className="fixed inset-0 z-50 flex justify-end">
                    {/* Flat backdrop (bg-slate-900/80, zero transition rounded) */}
                    <div
                        className="fixed inset-0 bg-slate-900/80 rounded-none cursor-pointer"
                        onClick={closeDetails}
                    />

                    {/* Drawer container */}
                    <div className="relative w-full max-w-lg bg-white border-l border-slate-300 h-full overflow-y-auto z-10 flex flex-col rounded-none shadow-none">
                        {/* Drawer Header */}
                        <div className="border-b border-slate-300 p-4 bg-slate-50 flex items-center justify-between">
                            <div>
                                <h3 className="font-bold text-slate-900 uppercase tracking-tight text-sm">
                                    Detail & Validasi Laporan
                                </h3>
                                <p className="text-[10px] text-slate-500 font-mono mt-0.5">
                                    TIKET: {selectedReport.ticket_number}
                                </p>
                            </div>
                            <button
                                onClick={closeDetails}
                                className="text-slate-400 hover:text-slate-700 p-1"
                                aria-label="Close details"
                            >
                                <X size={20} />
                            </button>
                        </div>

                        {/* Drawer Content */}
                        <div className="p-6 flex-1 flex flex-col gap-6">
                            {/* Photo display */}
                            <div>
                                <label className="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Foto Laporan Lapangan</label>
                                <div className="border border-slate-300 mt-1 bg-slate-100 h-48 flex items-center justify-center overflow-hidden rounded-none">
                                    {selectedReport.foto ? (
                                        <img
                                            src={selectedReport.foto.startsWith('http') ? selectedReport.foto : `/storage/${selectedReport.foto}`}
                                            alt={selectedReport.judul_laporan}
                                            className="w-full h-full object-cover"
                                        />
                                    ) : (
                                        <span className="text-xs text-slate-400">Tidak ada foto terlampir</span>
                                    )}
                                </div>
                            </div>

                            {/* Details Grid */}
                            <div className="grid grid-cols-2 gap-4 border border-slate-200 p-4 bg-slate-50 rounded-none text-xs">
                                <div>
                                    <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider">Pelapor</span>
                                    <div className="font-bold text-slate-800 flex items-center gap-1.5 mt-0.5">
                                        <User size={12} className="text-blue-700" />
                                        <span>{selectedReport.nama_pelapor || selectedReport.nama_petugas}</span>
                                    </div>
                                </div>
                                <div>
                                    <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider">Kontak Pelapor</span>
                                    <div className="font-mono text-slate-800 flex items-center gap-1.5 mt-0.5">
                                        <Phone size={12} className="text-blue-700" />
                                        <span>{selectedReport.kontak_pelapor || selectedReport.no_wa}</span>
                                    </div>
                                </div>
                                <div className="col-span-2 border-t border-slate-200 pt-2">
                                    <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider">Judul Temuan</span>
                                    <div className="font-bold text-slate-800 mt-0.5">{selectedReport.judul_laporan}</div>
                                </div>
                                <div className="col-span-2 border-t border-slate-200 pt-2">
                                    <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider">Alamat Kejadian</span>
                                    <div className="text-slate-700 leading-normal flex items-start gap-1.5 mt-0.5">
                                        <MapPin size={12} className="text-blue-700 mt-0.5 shrink-0" />
                                        <span>{selectedReport.alamat}</span>
                                    </div>
                                </div>
                                {selectedReport.lat && selectedReport.lng && (
                                    <div className="col-span-2 border-t border-slate-200 pt-2">
                                        <span className="text-slate-500 font-bold uppercase text-[9px] tracking-wider">Kordinat GPS</span>
                                        <div className="font-mono text-slate-700 mt-0.5">
                                            {selectedReport.lat.toFixed(6)}, {selectedReport.lng.toFixed(6)}
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Status and logs */}
                            <div className="flex flex-col gap-2">
                                <label className="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Status Saat Ini</label>
                                <div className="flex items-center gap-2">
                                    {renderStatusBadge(selectedReport.status)}
                                    {selectedReport.is_validated && (
                                        <span className="bg-green-700 text-white font-bold font-mono px-2 py-0.5 text-[10px] uppercase flex items-center gap-1">
                                            <ShieldCheck size={10} />
                                            <span>Valid</span>
                                        </span>
                                    )}
                                </div>
                            </div>

                            {/* Validation / Triage Action Box */}
                            <div className="border border-slate-300 p-4 bg-slate-50 rounded-none flex flex-col gap-4 mt-auto">
                                <h4 className="font-bold uppercase tracking-tight text-xs text-slate-900 border-b border-slate-200 pb-1 flex items-center gap-2">
                                    <ShieldCheck size={14} className="text-blue-700" />
                                    <span>Triage & Validasi Aset</span>
                                </h4>

                                {selectedReport.is_validated ? (
                                    <div className="flex flex-col gap-3">
                                        <div className="border border-green-300 bg-green-50 text-green-800 p-3 text-xs leading-normal rounded-none">
                                            <strong>Laporan Telah Valid.</strong> Aset ini diverifikasi di bawah naungan: <strong>{selectedReport.kepemilikan.toUpperCase()}</strong>.
                                            <br />
                                            <span className="text-slate-500 italic mt-1 block">Catatan Admin: {selectedReport.catatan_admin}</span>
                                        </div>
                                        {/* Direct button to build a ticket from this report */}
                                        <Link
                                            to={`/workflow/tickets?create_for_report_id=${selectedReport.id}`}
                                            onClick={closeDetails}
                                            className="bg-blue-700 hover:bg-blue-800 text-white font-bold py-2.5 text-xs text-center uppercase tracking-tight transition-colors rounded-none shadow-none"
                                        >
                                            Terbitkan Tiket Maintenance
                                        </Link>
                                    </div>
                                ) : (
                                    <div className="flex flex-col gap-3">
                                        <div className="flex flex-col gap-1">
                                            <label className="text-[10px] font-bold uppercase text-slate-700">Kepemilikan Aset *</label>
                                            <select
                                                value={kepemilikan}
                                                onChange={(e) => setKepemilikan(e.target.value)}
                                                className="border border-slate-300 px-3 py-2 text-xs rounded-none bg-white focus:outline-none focus:border-blue-700"
                                            >
                                                <option value="Dishub">Dishub Kabupaten Bandung Barat</option>
                                                <option value="Pihak Ke-3">Pihak Ketiga / Vendor Swasta</option>
                                            </select>
                                        </div>

                                        <div className="flex flex-col gap-1">
                                            <label className="text-[10px] font-bold uppercase text-slate-700">Catatan Validasi Admin</label>
                                            <textarea
                                                value={catatanAdmin}
                                                onChange={(e) => setCatatanAdmin(e.target.value)}
                                                rows={2}
                                                className="border border-slate-300 px-3 py-2 text-xs rounded-none bg-white focus:outline-none focus:border-blue-700 resize-none"
                                                placeholder="Berikan keterangan tambahan perihal keputusan validasi..."
                                            />
                                        </div>

                                        <button
                                            onClick={() => handleValidateReport(selectedReport.id)}
                                            disabled={isSubmittingValidation}
                                            className="bg-blue-700 hover:bg-blue-800 text-white font-bold py-2.5 text-xs uppercase tracking-tight transition-colors rounded-none shadow-none disabled:opacity-50"
                                        >
                                            {isSubmittingValidation ? 'Memproses Validasi...' : 'Validasi Laporan (Triage)'}
                                        </button>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
