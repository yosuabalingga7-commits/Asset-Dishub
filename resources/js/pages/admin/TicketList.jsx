import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import api from '../../services/api';
import { Search, Filter, Wrench, Clock, CheckCircle, AlertCircle, X, MapPin, User, Calendar, Plus, ChevronLeft, ChevronRight, Check } from 'lucide-react';

export default function TicketList() {
    const [searchParams, setSearchParams] = useSearchParams();

    // Bind filters to URL query strings
    const page = parseInt(searchParams.get('page') || '1', 10);
    const status = searchParams.get('status') || '';
    const priority = searchParams.get('priority') || '';
    const search = searchParams.get('search') || '';
    const createForReportId = searchParams.get('create_for_report_id') || '';

    const [ticketsData, setTicketsData] = useState({ data: [], meta: {} });
    const [isLoading, setIsLoading] = useState(true);
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [prefilledReport, setPrefilledReport] = useState(null);
    const [technicians, setTechnicians] = useState([]);
    const [categories, setCategories] = useState([]);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [errorMsg, setErrorMsg] = useState('');
    const [successMsg, setSuccessMsg] = useState('');

    const {
        register,
        handleSubmit,
        setValue,
        reset,
        formState: { errors }
    } = useForm({
        defaultValues: {
            report_id: '',
            user_id: '',
            category_id: '',
            jenis_aset: '',
            kepemilikan: 'Dishub',
            priority: 'Sedang',
            deadline: '',
            location_address: '',
            latitude: '',
            longitude: '',
            description: ''
        }
    });

    // Fetch tickets list
    useEffect(() => {
        let active = true;
        setIsLoading(true);

        api.get('/v1/maintenance-tickets', {
            params: { page, status, priority, search }
        })
        .then(res => {
            if (active) {
                setTicketsData(res);
                setIsLoading(false);
            }
        })
        .catch(err => {
            if (active) {
                console.error('[TicketList] Gagal memuat tiket:', err);
                setIsLoading(false);
            }
        });

        return () => { active = false; };
    }, [page, status, priority, search]);

    // Load helpers for creation form (technicians & categories)
    useEffect(() => {
        // Fetch users and categories
        api.get('/governance/users').then(res => {
            const list = res.data || res;
            // Filter only technicians and field roles
            const techRoles = ['petugas_lapangan', 'seksi', 'admin'];
            setTechnicians(Array.isArray(list) ? list.filter(u => techRoles.includes(u.role)) : []);
        }).catch(err => console.error(err));

        api.get('/governance/categories').then(res => {
            setCategories(res.data || res);
        }).catch(err => console.error(err));
    }, []);

    // Prefill report data if redirected from report triage page
    useEffect(() => {
        if (createForReportId) {
            setErrorMsg('');
            setIsCreateModalOpen(true);
            api.get(`/intake/reports/${createForReportId}`)
            .then(res => {
                const report = res.data || res;
                setPrefilledReport(report);
                
                setValue('report_id', report.id);
                setValue('jenis_aset', report.judul_laporan || '');
                setValue('location_address', report.alamat || '');
                setValue('latitude', report.lat || '');
                setValue('longitude', report.lng || '');
                setValue('kepemilikan', report.kepemilikan === 'Pihak Ke-3' ? 'Pihak Ke-3' : 'Dishub');
            })
            .catch(err => {
                console.error('Gagal mengambil rincian laporan untuk tiket:', err);
                setErrorMsg('Laporan rujukan tidak ditemukan.');
            });
        } else {
            setPrefilledReport(null);
            reset({
                report_id: '',
                user_id: '',
                category_id: '',
                jenis_aset: '',
                kepemilikan: 'Dishub',
                priority: 'Sedang',
                deadline: '',
                location_address: '',
                latitude: '',
                longitude: '',
                description: ''
            });
        }
    }, [createForReportId, reset, setValue]);

    const updateFilters = (newFilters) => {
        const params = new URLSearchParams(searchParams);
        Object.entries(newFilters).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                params.set(key, value);
            } else {
                params.delete(key);
            }
        });
        
        if (!newFilters.hasOwnProperty('page')) {
            params.set('page', '1');
        }
        
        setSearchParams(params);
    };

    const handleSearchChange = (e) => updateFilters({ search: e.target.value });
    const handleStatusChange = (e) => updateFilters({ status: e.target.value });
    const handlePriorityChange = (e) => updateFilters({ priority: e.target.value });
    const handlePageChange = (newPage) => updateFilters({ page: newPage.toString() });

    const openCreateModal = () => {
        setErrorMsg('');
        setSuccessMsg('');
        setIsCreateModalOpen(true);
    };

    const closeCreateModal = () => {
        setIsCreateModalOpen(false);
        // Clear url report_id query string if any
        if (createForReportId) {
            const params = new URLSearchParams(searchParams);
            params.delete('create_for_report_id');
            setSearchParams(params);
        }
    };

    // Handle ticket creation submission
    const onSubmit = async (data) => {
        setIsSubmitting(false);
        setErrorMsg('');
        setSuccessMsg('');

        try {
            await api.post('/v1/maintenance-tickets', data);
            setSuccessMsg('Tiket maintenance berhasil dibuat dan ditugaskan.');
            
            // Reload tickets list
            api.get('/v1/maintenance-tickets').then(res => setTicketsData(res));

            setTimeout(() => {
                closeCreateModal();
            }, 1500);
        } catch (error) {
            console.error('Gagal membuat tiket:', error);
            setErrorMsg(error.message || 'Terjadi kesalahan sistem saat membuat tiket perbaikan.');
        } finally {
            setIsSubmitting(false);
        }
    };

    // Helper for priority color badge
    const renderPriorityBadge = (prio) => {
        let color = 'bg-slate-100 text-slate-700 border-slate-300';
        if (prio === 'Tinggi') {
            color = 'bg-orange-50 text-orange-700 border-orange-300';
        } else if (prio === 'Darurat') {
            color = 'bg-red-50 text-red-700 border-red-300';
        } else if (prio === 'Rendah') {
            color = 'bg-green-50 text-green-700 border-green-300';
        }

        return (
            <span className={`px-2 py-0.5 text-[10px] font-mono border rounded-none ${color}`}>
                {prio}
            </span>
        );
    };

    // Helper for ticket status badge
    const renderStatusBadge = (ticketStatus) => {
        const lower = (ticketStatus || '').toLowerCase();
        let color = 'bg-slate-100 text-slate-700 border-slate-300';
        if (lower === 'proses') {
            color = 'bg-blue-50 text-blue-700 border-blue-300';
        } else if (lower === 'selesai') {
            color = 'bg-green-50 text-green-700 border-green-300';
        }

        return (
            <span className={`px-2 py-0.5 text-[10px] font-mono border rounded-none uppercase ${color}`}>
                {ticketStatus}
            </span>
        );
    };

    return (
        <div className="font-sans text-slate-800 flex flex-col gap-6 relative min-h-screen">
            {/* Header Title */}
            <div className="border-b border-slate-300 pb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 className="text-xl font-bold tracking-tight uppercase text-slate-900">
                        Tiket Maintenance & Penugasan
                    </h1>
                    <p className="text-xs text-slate-500 mt-1">
                        Monitoring alur kerja regu lapangan dan terbitkan surat perintah kerja perbaikan infrastruktur.
                    </p>
                </div>
                <button
                    onClick={openCreateModal}
                    className="inline-flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white font-bold px-4 py-2.5 uppercase text-xs tracking-tight transition-colors rounded-none shadow-none"
                >
                    <Plus size={16} />
                    <span>Terbitkan Tiket Baru</span>
                </button>
            </div>

            {/* Filter Panel */}
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
                        placeholder="Cari kode tiket, petugas, lokasi..."
                        className="w-full pl-9 pr-3 py-2 text-xs border border-slate-300 rounded-none bg-slate-50 focus:outline-none focus:border-blue-700"
                    />
                </div>

                {/* Status select */}
                <div>
                    <select
                        value={status}
                        onChange={handleStatusChange}
                        className="w-full px-3 py-2 text-xs border border-slate-300 rounded-none bg-slate-50 focus:outline-none focus:border-blue-700"
                    >
                        <option value="">Semua Status</option>
                        <option value="proses">Proses</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>

                {/* Priority select */}
                <div>
                    <select
                        value={priority}
                        onChange={handlePriorityChange}
                        className="w-full px-3 py-2 text-xs border border-slate-300 rounded-none bg-slate-50 focus:outline-none focus:border-blue-700"
                    >
                        <option value="">Semua Prioritas</option>
                        <option value="Rendah">Rendah</option>
                        <option value="Sedang">Sedang</option>
                        <option value="Tinggi">Tinggi</option>
                        <option value="Darurat">Darurat</option>
                    </select>
                </div>

                {/* Reset Filters button */}
                <div className="flex justify-end">
                    <button
                        onClick={() => updateFilters({ status: '', priority: '', search: '', page: '1' })}
                        className="border border-slate-300 px-4 py-2 text-xs font-bold uppercase hover:bg-slate-50 active:bg-slate-100 transition-colors w-full md:w-auto rounded-none shadow-none"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>

            {/* Dense Flat Table */}
            <div className="border border-slate-300 bg-white rounded-none shadow-none overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="border-b border-slate-300 bg-slate-100 text-slate-700 text-xs font-bold uppercase">
                                <th className="p-3 border-r border-slate-300">Kode Tiket</th>
                                <th className="p-3 border-r border-slate-300">Aset & Kerusakan</th>
                                <th className="p-3 border-r border-slate-300">Lokasi Kerja</th>
                                <th className="p-3 border-r border-slate-300">Petugas Lapangan</th>
                                <th className="p-3 border-r border-slate-300">Prioritas</th>
                                <th className="p-3 border-r border-slate-300">Status</th>
                                <th className="p-3">Deadline</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-200 text-xs">
                            {isLoading ? (
                                <tr>
                                    <td colSpan={7} className="p-8 text-center text-slate-500 font-medium">
                                        Memuat data tiket maintenance...
                                    </td>
                                </tr>
                            ) : ticketsData.data.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="p-8 text-center text-slate-500 italic">
                                        Tidak ada tiket perbaikan terdaftar.
                                    </td>
                                </tr>
                            ) : (
                                ticketsData.data.map((ticket) => (
                                    <tr key={ticket.id} className="hover:bg-slate-50 transition-colors">
                                        <td className="p-3 border-r border-slate-200 font-mono font-bold text-blue-700">
                                            {ticket.ticket_code}
                                        </td>
                                        <td className="p-3 border-r border-slate-200">
                                            <div className="font-bold text-slate-900">{ticket.jenis_aset}</div>
                                            <div className="text-[10px] text-slate-500 mt-0.5">{ticket.description}</div>
                                        </td>
                                        <td className="p-3 border-r border-slate-200">
                                            <div className="leading-normal flex items-start gap-1">
                                                <MapPin size={12} className="text-slate-400 shrink-0 mt-0.5" />
                                                <span className="line-clamp-2">{ticket.location_address}</span>
                                            </div>
                                        </td>
                                        <td className="p-3 border-r border-slate-200">
                                            <div className="font-bold">{ticket.technician_name}</div>
                                            <div className="text-[10px] text-slate-500 uppercase tracking-wide">ID: {ticket.user_id}</div>
                                        </td>
                                        <td className="p-3 border-r border-slate-200 text-center">
                                            {renderPriorityBadge(ticket.priority)}
                                        </td>
                                        <td className="p-3 border-r border-slate-200 text-center">
                                            {renderStatusBadge(ticket.status)}
                                        </td>
                                        <td className="p-3 font-mono font-bold text-slate-600">
                                            {ticket.deadline || '-'}
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Table pagination footer */}
                {!isLoading && ticketsData.meta && ticketsData.meta.last_page > 1 && (
                    <div className="border-t border-slate-300 p-4 bg-slate-100 flex items-center justify-between text-xs">
                        <span className="text-slate-600 font-mono">
                            Halaman {ticketsData.meta.current_page} dari {ticketsData.meta.last_page} ({ticketsData.meta.total} Tiket)
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
                                disabled={page >= ticketsData.meta.last_page}
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

            {/* Creation Modal with flat overlay (sharp edges, rounded-none) */}
            {isCreateModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                    {/* Backdrop */}
                    <div
                        className="fixed inset-0 bg-slate-900/80 rounded-none cursor-pointer"
                        onClick={closeCreateModal}
                    />

                    {/* Modal body */}
                    <div className="relative w-full max-w-xl bg-white border border-slate-300 rounded-none z-10 flex flex-col max-h-[90vh]">
                        {/* Modal Header */}
                        <div className="border-b border-slate-300 p-4 bg-slate-50 flex items-center justify-between">
                            <div>
                                <h3 className="font-bold text-slate-900 uppercase tracking-tight text-sm flex items-center gap-2">
                                    <Wrench size={16} className="text-blue-700" />
                                    <span>Penerbitan Surat Perintah Kerja</span>
                                </h3>
                                {prefilledReport && (
                                    <p className="text-[10px] text-blue-700 font-mono mt-0.5">
                                        RUJUKAN LAPORAN: {prefilledReport.ticket_number}
                                    </p>
                                )}
                            </div>
                            <button
                                onClick={closeCreateModal}
                                className="text-slate-400 hover:text-slate-700 p-1"
                                aria-label="Close modal"
                            >
                                <X size={20} />
                            </button>
                        </div>

                        {/* Modal Form Scroll Area */}
                        <form onSubmit={handleSubmit(onSubmit)} className="overflow-y-auto p-6 flex flex-col gap-4 flex-1">
                            {errorMsg && (
                                <div className="border border-red-300 bg-red-50 text-red-700 p-3 text-xs rounded-none">
                                    {errorMsg}
                                </div>
                            )}
                            {successMsg && (
                                <div className="border border-green-300 bg-green-50 text-green-700 p-3 text-xs rounded-none flex items-center gap-1.5 font-bold uppercase">
                                    <Check size={16} />
                                    <span>{successMsg}</span>
                                </div>
                            )}

                            {/* Hidden Fields */}
                            <input type="hidden" {...register('report_id')} />
                            <input type="hidden" {...register('latitude')} />
                            <input type="hidden" {...register('longitude')} />

                            {/* Prefilled warning if coordinates are missing */}
                            {prefilledReport && (!prefilledReport.lat || !prefilledReport.lng) && (
                                <div className="border border-amber-300 bg-amber-50 text-amber-800 p-2.5 text-xs rounded-none">
                                    Warning: Laporan rujukan tidak menyertakan koordinat koordinat GPS. Tiket diterbitkan sebagai perbaikan manual.
                                </div>
                            )}

                            {/* Group: Category & Asset Description */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div className="flex flex-col gap-1">
                                    <label className="text-[10px] font-bold uppercase text-slate-700">Kategori Aset *</label>
                                    <select
                                        {...register('category_id', { required: 'Kategori wajib dipilih' })}
                                        className={`border ${errors.category_id ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-xs rounded-none bg-slate-50 focus:outline-none focus:border-blue-700`}
                                    >
                                        <option value="">-- Pilih Kategori --</option>
                                        {categories.map((cat) => (
                                            <option key={cat.id} value={cat.id}>
                                                {cat.nama_kategori}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.category_id && <span className="text-[10px] text-red-600">{errors.category_id.message}</span>}
                                </div>

                                <div className="flex flex-col gap-1">
                                    <label className="text-[10px] font-bold uppercase text-slate-700">Nama / Tipe Aset *</label>
                                    <input
                                        type="text"
                                        {...register('jenis_aset', { required: 'Nama/Tipe aset wajib diisi' })}
                                        className={`border ${errors.jenis_aset ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-xs rounded-none bg-slate-50 focus:outline-none focus:border-blue-700`}
                                        placeholder="Contoh: Lampu Merkuri PJU 120W"
                                    />
                                    {errors.jenis_aset && <span className="text-[10px] text-red-600">{errors.jenis_aset.message}</span>}
                                </div>
                            </div>

                            {/* Group: Ownership & Priority */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div className="flex flex-col gap-1">
                                    <label className="text-[10px] font-bold uppercase text-slate-700">Kepemilikan *</label>
                                    <select
                                        {...register('kepemilikan')}
                                        className="border border-slate-300 px-3 py-2 text-xs rounded-none bg-slate-50 focus:outline-none focus:border-blue-700"
                                    >
                                        <option value="Dishub">Dishub Kabupaten Bandung Barat</option>
                                        <option value="Pihak Ke-3">Pihak Ketiga (Vendor / Swasta)</option>
                                    </select>
                                </div>

                                <div className="flex flex-col gap-1">
                                    <label className="text-[10px] font-bold uppercase text-slate-700">Tingkat Prioritas *</label>
                                    <select
                                        {...register('priority')}
                                        className="border border-slate-300 px-3 py-2 text-xs rounded-none bg-slate-50 focus:outline-none focus:border-blue-700"
                                    >
                                        <option value="Rendah">Rendah (Pencegahan / Rutin)</option>
                                        <option value="Sedang">Sedang (Malfungsi Sebagian)</option>
                                        <option value="Tinggi">Tinggi (Mati Total / Jalan Utama)</option>
                                        <option value="Darurat">Darurat (Rentan Kecelakaan/Kriminal)</option>
                                    </select>
                                </div>
                            </div>

                            {/* Group: Technician & Deadline */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div className="flex flex-col gap-1">
                                    <label className="text-[10px] font-bold uppercase text-slate-700">Petugas Pelaksana *</label>
                                    <select
                                        {...register('user_id', { required: 'Petugas lapangan wajib ditunjuk' })}
                                        className={`border ${errors.user_id ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-xs rounded-none bg-slate-50 focus:outline-none focus:border-blue-700`}
                                    >
                                        <option value="">-- Pilih Petugas --</option>
                                        {technicians.map((tech) => (
                                            <option key={tech.id} value={tech.id}>
                                                {tech.name} ({tech.role})
                                            </option>
                                        ))}
                                    </select>
                                    {errors.user_id && <span className="text-[10px] text-red-600">{errors.user_id.message}</span>}
                                </div>

                                <div className="flex flex-col gap-1">
                                    <label className="text-[10px] font-bold uppercase text-slate-700">Batas Waktu Perbaikan *</label>
                                    <input
                                        type="date"
                                        {...register('deadline', { required: 'Batas tanggal perbaikan wajib ditentukan' })}
                                        className={`border ${errors.deadline ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-xs rounded-none bg-slate-50 focus:outline-none focus:border-blue-700`}
                                    />
                                    {errors.deadline && <span className="text-[10px] text-red-600">{errors.deadline.message}</span>}
                                </div>
                            </div>

                            {/* Address details */}
                            <div className="flex flex-col gap-1">
                                <label className="text-[10px] font-bold uppercase text-slate-700">Alamat Lokasi Pengerjaan *</label>
                                <input
                                    type="text"
                                    {...register('location_address', { required: 'Alamat kerja wajib diisi' })}
                                    className={`border ${errors.location_address ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-xs rounded-none bg-slate-50 focus:outline-none focus:border-blue-700`}
                                    placeholder="Jalan, Desa, Kecamatan, Bandung Barat"
                                />
                                {errors.location_address && <span className="text-[10px] text-red-600">{errors.location_address.message}</span>}
                            </div>

                            {/* Description */}
                            <div className="flex flex-col gap-1">
                                <label className="text-[10px] font-bold uppercase text-slate-700">Instruksi / Catatan Teknis</label>
                                <textarea
                                    {...register('description')}
                                    rows={3}
                                    className="border border-slate-300 px-3 py-2 text-xs rounded-none bg-slate-50 focus:outline-none focus:border-blue-700 resize-none"
                                    placeholder="Tuliskan keluhan atau rincian instruksi kerja untuk petugas di lapangan..."
                                />
                            </div>

                            {/* Submit button */}
                            <div className="border-t border-slate-200 pt-4 mt-2 flex justify-end gap-3">
                                <button
                                    type="button"
                                    onClick={closeCreateModal}
                                    className="border border-slate-300 text-slate-700 font-bold px-4 py-2 uppercase text-xs tracking-tight transition-colors rounded-none hover:bg-slate-50 bg-white"
                                >
                                    Batalkan
                                </button>
                                <button
                                    type="submit"
                                    disabled={isSubmitting}
                                    className="bg-blue-700 hover:bg-blue-800 text-white font-bold px-5 py-2 uppercase text-xs tracking-tight transition-colors rounded-none shadow-none disabled:opacity-50"
                                >
                                    {isSubmitting ? 'Memproses Tiket...' : 'Simpan & Kirim SPK'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
