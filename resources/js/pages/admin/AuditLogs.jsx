import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import api from '../../services/api';
import { Search, ClipboardList, ShieldAlert, Terminal, AlertTriangle, HelpCircle } from 'lucide-react';

// Custom Debounce Hook
function useDebounce(value, delay) {
    const [debouncedValue, setDebouncedValue] = useState(value);

    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedValue(value);
        }, delay);

        return () => {
            clearTimeout(handler);
        };
    }, [value, delay]);

    return debouncedValue;
}

export default function AuditLogs() {
    const [searchParams, setSearchParams] = useSearchParams();

    // Query parameters
    const page = searchParams.get('page') || '1';
    const searchQuery = searchParams.get('search') || '';

    // Local States
    const [logs, setLogs] = useState([]);
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [isLoading, setIsLoading] = useState(true);
    const [searchInput, setSearchInput] = useState(searchQuery);

    // Debounce the search input
    const debouncedSearch = useDebounce(searchInput, 300);

    // Sync debounced search to URL search params
    useEffect(() => {
        // Only update search param if it changed from the current URL value
        if (debouncedSearch !== searchQuery) {
            setSearchParams({
                page: '1',
                search: debouncedSearch
            });
        }
    }, [debouncedSearch]);

    // Keep input in sync with URL if URL changes (e.g., browser back button)
    useEffect(() => {
        setSearchInput(searchQuery);
    }, [searchQuery]);

    // Fetch logs from backend
    const fetchLogs = async () => {
        setIsLoading(true);
        try {
            const res = await api.get('/v1/audit-logs', {
                params: {
                    page,
                    search: searchQuery
                }
            });
            setLogs(res.data || []);
            setPagination({
                current_page: res.current_page || 1,
                last_page: res.last_page || 1,
                total: res.total || 0
            });
        } catch (err) {
            console.error('[AuditLogs] Gagal memuat log audit:', err);
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        fetchLogs();
    }, [page, searchQuery]);

    // Helper to format Risk indicator
    const getRiskInfo = (type) => {
        const t = (type || '').toUpperCase();
        if (t === 'DELETE') {
            return { label: 'CRITICAL', color: 'border-red-600 text-red-700 bg-red-50' };
        }
        if (t === 'CREATE' || t === 'VALIDASI') {
            return { label: 'MEDIUM', color: 'border-blue-600 text-blue-700 bg-blue-50' };
        }
        if (t === 'LOGIN') {
            return { label: 'LOW', color: 'border-emerald-600 text-emerald-700 bg-emerald-50' };
        }
        return { label: 'INFO', color: 'border-slate-300 text-slate-700 bg-slate-50' };
    };

    return (
        <div className="font-sans text-slate-800 flex flex-col gap-6 min-h-screen relative">
            {/* Header section */}
            <div className="border-b border-slate-300 pb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 className="text-xl font-bold tracking-tight uppercase text-slate-900">
                        Audit & Log Aktivitas Sistem
                    </h1>
                    <p className="text-xs text-slate-500 mt-1">
                        Catatan konsolidasi riwayat aktivitas pengguna, triage laporan, surat perintah kerja (SPK), dan manajemen operasional.
                    </p>
                </div>
            </div>

            {/* Dense Filter / Search Bar */}
            <div className="border border-slate-300 bg-white p-4 flex gap-4 items-center justify-between rounded-none shadow-none">
                <div className="relative flex-1 max-w-md">
                    <input
                        type="text"
                        placeholder="Ketik untuk mencari log (nama, modul, deskripsi)..."
                        value={searchInput}
                        onChange={(e) => setSearchInput(e.target.value)}
                        className="w-full appearance-none rounded-none border border-slate-300 focus:border-slate-800 bg-white px-3 py-2 pl-9 text-xs text-slate-800 focus:outline-none placeholder:text-slate-400"
                    />
                    <div className="absolute left-3 top-2.5 text-slate-400">
                        <Search size={14} />
                    </div>
                </div>

                <div className="text-[10px] text-slate-400 uppercase font-mono hidden md:block">
                    {isLoading ? 'Menghubungkan...' : `Total: ${pagination.total} Entri Log`}
                </div>
            </div>

            {/* Dense Audit Table */}
            <div className="border border-slate-300 bg-white rounded-none shadow-none overflow-hidden">
                {isLoading ? (
                    <div className="p-12 text-center text-slate-500 text-xs font-mono uppercase tracking-wider">
                        Memuat riwayat log audit...
                    </div>
                ) : logs.length === 0 ? (
                    <div className="p-12 text-center text-slate-400 text-xs italic">
                        Tidak ada catatan aktivitas ditemukan.
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr className="bg-slate-100 border-b border-slate-300 font-bold text-slate-500 uppercase tracking-wider text-[10px]">
                                    <th className="p-3 w-40">Waktu & Tanggal</th>
                                    <th className="p-3 w-44">Pengguna (Aktor)</th>
                                    <th className="p-3 w-40">Modul / Jenis</th>
                                    <th className="p-3">Aktivitas & Rincian Tindakan</th>
                                    <th className="p-3 w-28">Tingkat Risiko</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-200">
                                {logs.map((item, idx) => {
                                    const risk = getRiskInfo(item.type);
                                    return (
                                        <tr key={item.id || idx} className="hover:bg-slate-50 transition-colors font-mono">
                                            <td className="p-3 text-[10px] text-slate-500 whitespace-nowrap">
                                                {item.time}
                                            </td>
                                            <td className="p-3 whitespace-nowrap">
                                                <div className="font-bold text-slate-950">{item.user}</div>
                                                <div className="text-[9px] text-slate-400 uppercase tracking-wider">{item.role}</div>
                                            </td>
                                            <td className="p-3 whitespace-nowrap">
                                                <span className="text-[10px] font-bold text-blue-700 bg-blue-50 border border-blue-200 px-1.5 py-0.5 rounded-none">
                                                    {item.modul}
                                                </span>
                                            </td>
                                            <td className="p-3 text-slate-700 font-sans">
                                                <div className="font-bold text-slate-900">{item.action} <span className="text-[10px] font-mono text-slate-400 font-normal">{item.target}</span></div>
                                                <div className="text-xs text-slate-500 mt-0.5 font-normal">{item.desc}</div>
                                            </td>
                                            <td className="p-3 whitespace-nowrap">
                                                <span className={`inline-block border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-none ${risk.color}`}>
                                                    {risk.label}
                                                </span>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}

                {/* Pagination Row */}
                {pagination.last_page > 1 && (
                    <div className="border-t border-slate-300 bg-slate-50 p-4 flex items-center justify-between">
                        <span className="text-[10px] text-slate-400 font-mono uppercase">
                            Halaman {pagination.current_page} dari {pagination.last_page} ({pagination.total} total log)
                        </span>

                        <div className="flex gap-1">
                            <button
                                disabled={pagination.current_page === 1}
                                onClick={() => setSearchParams({ page: (pagination.current_page - 1).toString(), search: searchQuery })}
                                className="border border-slate-300 bg-white hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 disabled:hover:bg-white disabled:hover:border-slate-300 active:bg-slate-100 px-3 py-1.5 font-bold uppercase text-[10px] tracking-tight rounded-none transition-colors"
                            >
                                Sebelumnya
                            </button>
                            <button
                                disabled={pagination.current_page === pagination.last_page}
                                onClick={() => setSearchParams({ page: (pagination.current_page + 1).toString(), search: searchQuery })}
                                className="border border-slate-300 bg-white hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 disabled:hover:bg-white disabled:hover:border-slate-300 active:bg-slate-100 px-3 py-1.5 font-bold uppercase text-[10px] tracking-tight rounded-none transition-colors"
                            >
                                Selanjutnya
                            </button>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
}
