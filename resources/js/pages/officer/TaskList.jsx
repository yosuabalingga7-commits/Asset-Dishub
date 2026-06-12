import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import api from '../../services/api';
import { Search, Filter, Wrench, Clock, CheckCircle, X, MapPin, Calendar, Camera, Send, AlertOctagon, ChevronLeft, ChevronRight } from 'lucide-react';

export default function TaskList() {
    const [searchParams, setSearchParams] = useSearchParams();

    // Bind filters to URL query params
    const page = parseInt(searchParams.get('page') || '1', 10);
    const status = searchParams.get('status') || '';
    const priority = searchParams.get('priority') || '';
    const search = searchParams.get('search') || '';

    const [tasksData, setTasksData] = useState({ data: [], meta: {} });
    const [isLoading, setIsLoading] = useState(true);
    const [selectedTask, setSelectedTask] = useState(null);
    const [isDrawerOpen, setIsDrawerOpen] = useState(false);
    const [isSubmittingCompletion, setIsSubmittingCompletion] = useState(false);
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
            completion_notes: ''
        }
    });

    // Fetch assigned tasks from scoped API
    useEffect(() => {
        let active = true;
        setIsLoading(true);

        api.get('/v1/maintenance-tickets', {
            params: { page, status, priority, search }
        })
        .then(res => {
            if (active) {
                setTasksData(res);
                setIsLoading(false);
            }
        })
        .catch(err => {
            if (active) {
                console.error('[TaskList] Gagal memuat daftar tugas:', err);
                setIsLoading(false);
            }
        });

        return () => { active = false; };
    }, [page, status, priority, search]);

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

    const openDetails = (task) => {
        setSelectedTask(task);
        setErrorMsg('');
        setSuccessMsg('');
        setIsDrawerOpen(true);
        reset({ completion_notes: '' });
    };

    const closeDetails = () => {
        setIsDrawerOpen(false);
    };

    // Submitting task completion proof
    const onSubmitCompletion = async (data) => {
        setIsSubmittingCompletion(true);
        setErrorMsg('');
        setSuccessMsg('');

        try {
            const formData = new FormData();
            formData.append('completion_notes', data.completion_notes);
            if (data.foto_perbaikan && data.foto_perbaikan[0]) {
                formData.append('foto_perbaikan', data.foto_perbaikan[0]);
            }

            const res = await api.patch(`/v1/maintenance-tickets/${selectedTask.id}/complete`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            setSuccessMsg('Laporan perbaikan berhasil terkirim. Status aset diperbarui.');
            setSelectedTask(res.data || res);

            // Refresh tasks list
            api.get('/v1/maintenance-tickets', {
                params: { page, status, priority, search }
            }).then(response => setTasksData(response));

            setTimeout(() => {
                closeDetails();
            }, 1500);

        } catch (error) {
            console.error('Gagal memproses penyelesaian tugas:', error);
            setErrorMsg(error.message || 'Terjadi kesalahan sistem saat mengunggah bukti perbaikan.');
        } finally {
            setIsSubmittingCompletion(false);
        }
    };

    // Helper priority badge
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

    // Helper status badge
    const renderStatusBadge = (taskStatus) => {
        const lower = (taskStatus || '').toLowerCase();
        let color = 'bg-slate-100 text-slate-700 border-slate-300';
        if (lower === 'proses') {
            color = 'bg-blue-50 text-blue-700 border-blue-300';
        } else if (lower === 'selesai') {
            color = 'bg-green-50 text-green-700 border-green-300';
        }

        return (
            <span className={`px-2 py-0.5 text-[10px] font-mono border rounded-none uppercase ${color}`}>
                {taskStatus}
            </span>
        );
    };

    return (
        <div className="font-sans text-slate-800 flex flex-col gap-6 relative min-h-screen">
            {/* Page Header */}
            <div className="border-b border-slate-300 pb-4">
                <h1 className="text-xl font-bold tracking-tight uppercase text-slate-900">
                    Tugas Lapangan Saya
                </h1>
                <p className="text-xs text-slate-500 mt-1">
                    Kelola, pantau, dan unggah laporan penyelesaian tugas perbaikan aset yang ditugaskan kepada Anda.
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
                        placeholder="Cari kode tiket, lokasi, aset..."
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

                {/* Reset filters */}
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
                                <th className="p-3 border-r border-slate-300">Prioritas</th>
                                <th className="p-3 border-r border-slate-300">Status</th>
                                <th className="p-3 border-r border-slate-300">Batas Waktu</th>
                                <th className="p-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-200 text-xs">
                            {isLoading ? (
                                <tr>
                                    <td colSpan={7} className="p-8 text-center text-slate-500 font-medium">
                                        Memuat daftar tugas...
                                    </td>
                                </tr>
                            ) : tasksData.data.length === 0 ? (
                                <tr>
                                    <td colSpan={7} className="p-8 text-center text-slate-500 italic">
                                        Tidak ada tugas perbaikan terdaftar untuk Anda.
                                    </td>
                                </tr>
                            ) : (
                                tasksData.data.map((task) => (
                                    <tr key={task.id} className="hover:bg-slate-50 transition-colors">
                                        <td className="p-3 border-r border-slate-200 font-mono font-bold text-blue-700">
                                            {task.ticket_code}
                                        </td>
                                        <td className="p-3 border-r border-slate-200">
                                            <div className="font-bold text-slate-900">{task.jenis_aset}</div>
                                            <div className="text-[10px] text-slate-500 mt-0.5">{task.description}</div>
                                        </td>
                                        <td className="p-3 border-r border-slate-200">
                                            <div className="leading-normal flex items-start gap-1">
                                                <MapPin size={12} className="text-slate-400 shrink-0 mt-0.5" />
                                                <span className="line-clamp-2">{task.location_address}</span>
                                            </div>
                                        </td>
                                        <td className="p-3 border-r border-slate-200 text-center">
                                            {renderPriorityBadge(task.priority)}
                                        </td>
                                        <td className="p-3 border-r border-slate-200 text-center">
                                            {renderStatusBadge(task.status)}
                                        </td>
                                        <td className="p-3 border-r border-slate-200 font-mono font-bold text-slate-600">
                                            {task.deadline || '-'}
                                        </td>
                                        <td className="p-3 text-center">
                                            <button
                                                onClick={() => openDetails(task)}
                                                className="bg-blue-700 hover:bg-blue-800 text-white font-bold px-3 py-1.5 uppercase text-[10px] tracking-tight transition-colors rounded-none shadow-none"
                                            >
                                                Pengerjaan
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Table pagination */}
                {!isLoading && tasksData.meta && tasksData.meta.last_page > 1 && (
                    <div className="border-t border-slate-300 p-4 bg-slate-100 flex items-center justify-between text-xs">
                        <span className="text-slate-600 font-mono">
                            Halaman {tasksData.meta.current_page} dari {tasksData.meta.last_page} ({tasksData.meta.total} Tugas)
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
                                disabled={page >= tasksData.meta.last_page}
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

            {/* Slide-out details drawer with flat overlay (bg-slate-900/80) */}
            {isDrawerOpen && selectedTask && (
                <div className="fixed inset-0 z-50 flex justify-end">
                    {/* Backdrop */}
                    <div
                        className="fixed inset-0 bg-slate-900/80 rounded-none cursor-pointer"
                        onClick={closeDetails}
                    />

                    {/* Drawer container */}
                    <div className="relative w-full max-w-lg bg-white border-l border-slate-300 h-full overflow-y-auto z-10 flex flex-col rounded-none shadow-none">
                        {/* Drawer Header */}
                        <div className="border-b border-slate-300 p-4 bg-slate-50 flex items-center justify-between">
                            <div>
                                <h3 className="font-bold text-slate-900 uppercase tracking-tight text-sm flex items-center gap-1.5">
                                    <Wrench size={16} className="text-blue-700" />
                                    <span>Pengerjaan Tugas Lapangan</span>
                                </h3>
                                <p className="text-[10px] text-slate-500 font-mono mt-0.5">
                                    TIKET: {selectedTask.ticket_code}
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
                            {errorMsg && (
                                <div className="border border-red-300 bg-red-50 text-red-700 p-3 text-xs rounded-none">
                                    {errorMsg}
                                </div>
                            )}
                            {successMsg && (
                                <div className="border border-green-300 bg-green-50 text-green-700 p-3 text-xs rounded-none font-bold uppercase">
                                    {successMsg}
                                </div>
                            )}

                            {/* Details Grid */}
                            <div className="border border-slate-200 p-4 bg-slate-50 rounded-none text-xs flex flex-col gap-3">
                                <div className="grid grid-cols-2 gap-3">
                                    <div>
                                        <span className="text-slate-500 font-bold uppercase text-[9px]">Aset Penugasan</span>
                                        <div className="font-bold text-slate-800 mt-0.5">{selectedTask.jenis_aset}</div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-bold uppercase text-[9px]">Tingkat Prioritas</span>
                                        <div className="mt-0.5">{renderPriorityBadge(selectedTask.priority)}</div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-bold uppercase text-[9px]">Status SPK</span>
                                        <div className="mt-0.5">{renderStatusBadge(selectedTask.status)}</div>
                                    </div>
                                    <div>
                                        <span className="text-slate-500 font-bold uppercase text-[9px]">Batas Waktu</span>
                                        <div className="font-bold text-slate-800 flex items-center gap-1 mt-0.5">
                                            <Calendar size={12} className="text-blue-700" />
                                            <span>{selectedTask.deadline || '-'}</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="border-t border-slate-200 pt-2">
                                    <span className="text-slate-500 font-bold uppercase text-[9px]">Instruksi Pekerjaan</span>
                                    <div className="text-slate-700 leading-normal mt-0.5">{selectedTask.description}</div>
                                </div>

                                <div className="border-t border-slate-200 pt-2">
                                    <span className="text-slate-500 font-bold uppercase text-[9px]">Alamat Penugasan</span>
                                    <div className="text-slate-700 leading-normal flex items-start gap-1 mt-0.5">
                                        <MapPin size={12} className="text-blue-700 mt-0.5 shrink-0" />
                                        <span>{selectedTask.location_address}</span>
                                    </div>
                                </div>
                            </div>

                            {/* Verification photo if finished */}
                            {selectedTask.status === 'selesai' && (
                                <div className="flex flex-col gap-3">
                                    <div className="border border-green-300 bg-green-50 text-green-800 p-3 text-xs rounded-none">
                                        <strong>Pekerjaan Telah Selesai.</strong> Laporan pengerjaan telah dikirimkan ke admin.
                                        <br />
                                        <span className="text-slate-500 italic mt-1 block">Catatan Perbaikan: {selectedTask.completion_notes}</span>
                                    </div>
                                    <div>
                                        <label className="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Foto Hasil Perbaikan</label>
                                        <div className="border border-slate-300 mt-1 bg-slate-100 h-44 flex items-center justify-center overflow-hidden rounded-none">
                                            {selectedTask.foto_perbaikan ? (
                                                <img
                                                    src={selectedTask.foto_perbaikan.startsWith('http') ? selectedTask.foto_perbaikan : `/storage/${selectedTask.foto_perbaikan}`}
                                                    alt="Bukti Perbaikan"
                                                    className="w-full h-full object-cover"
                                                />
                                            ) : (
                                                <span className="text-xs text-slate-400">Tidak ada foto bukti perbaikan</span>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}

                            {/* Report Completion form (only active if status is not selesai) */}
                            {selectedTask.status !== 'selesai' && (
                                <form onSubmit={handleSubmit(onSubmitCompletion)} className="border border-slate-300 p-4 bg-slate-50 rounded-none flex flex-col gap-4 mt-auto">
                                    <h4 className="font-bold uppercase tracking-tight text-xs text-slate-900 border-b border-slate-200 pb-1 flex items-center gap-1.5">
                                        <Camera size={14} className="text-blue-700" />
                                        <span>Laporan Penyelesaian Perbaikan</span>
                                    </h4>

                                    <div className="flex flex-col gap-1">
                                        <label className="text-[10px] font-bold uppercase text-slate-700 flex items-center gap-1.5">
                                            <span>Foto Hasil Perbaikan *</span>
                                        </label>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            {...register('foto_perbaikan', { required: 'Foto bukti perbaikan wajib diunggah' })}
                                            className={`border ${errors.foto_perbaikan ? 'border-red-500' : 'border-slate-300'} text-xs bg-white file:border-0 file:bg-slate-300 file:px-3 file:py-1.5 file:text-xs file:font-bold file:uppercase file:hover:bg-slate-400 file:cursor-pointer rounded-none`}
                                        />
                                        {errors.foto_perbaikan && (
                                            <span className="text-[10px] text-red-600">{errors.foto_perbaikan.message}</span>
                                        )}
                                    </div>

                                    <div className="flex flex-col gap-1">
                                        <label className="text-[10px] font-bold uppercase text-slate-700">Catatan Tindakan Lapangan *</label>
                                        <textarea
                                            {...register('completion_notes', { required: 'Catatan tindakan perbaikan wajib diisi' })}
                                            rows={2}
                                            className={`border ${errors.completion_notes ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-xs rounded-none bg-white focus:outline-none focus:border-blue-700 resize-none`}
                                            placeholder="Jelaskan tindakan perbaikan yang telah dilakukan di lapangan..."
                                        />
                                        {errors.completion_notes && (
                                            <span className="text-[10px] text-red-600">{errors.completion_notes.message}</span>
                                        )}
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={isSubmittingCompletion}
                                        className="bg-blue-700 hover:bg-blue-800 text-white font-bold py-2.5 text-xs uppercase tracking-tight transition-colors rounded-none shadow-none disabled:opacity-50 flex items-center justify-center gap-2"
                                    >
                                        <Send size={14} />
                                        <span>{isSubmittingCompletion ? 'Mengirimkan...' : 'Kirim Bukti & Selesaikan SPK'}</span>
                                    </button>
                                </form>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
