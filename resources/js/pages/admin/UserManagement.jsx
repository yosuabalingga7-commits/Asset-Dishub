import React, { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useForm } from 'react-hook-form';
import api from '../../services/api';
import useAuthStore from '../../store/useAuthStore';
import { Search, UserPlus, Edit, Trash2, X, Plus, AlertCircle, RefreshCw } from 'lucide-react';

export default function UserManagement() {
    const { user: currentUser } = useAuthStore();
    const [searchParams, setSearchParams] = useSearchParams();

    // Query params synced to state
    const page = searchParams.get('page') || '1';
    const searchQuery = searchParams.get('search') || '';
    const roleFilter = searchParams.get('role') || '';

    // Data States
    const [users, setUsers] = useState([]);
    const [seksis, setSeksis] = useState([]);
    const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, total: 0 });
    const [isLoading, setIsLoading] = useState(true);
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Modal / Drawer state
    const [isDrawerOpen, setIsDrawerOpen] = useState(false);
    const [editingUser, setEditingUser] = useState(null); // null means "Create" mode
    const [errorMsg, setErrorMsg] = useState('');
    const [successMsg, setSuccessMsg] = useState('');

    // Search state
    const [searchInput, setSearchInput] = useState(searchQuery);

    // react-hook-form Setup
    const { register, handleSubmit, reset, watch, setValue, formState: { errors } } = useForm({
        defaultValues: {
            name: '',
            nip: '',
            email: '',
            no_wa: '',
            role: 'petugas_lapangan',
            seksi_id: '',
            password: '',
            is_active: true
        }
    });

    // Watch role for conditional seksi validation
    const watchedRole = watch('role');

    // Fetch data whenever page, search, or role filters change
    const fetchUsers = async () => {
        setIsLoading(true);
        try {
            const res = await api.get('/v1/users', {
                params: {
                    page,
                    search: searchQuery,
                    role: roleFilter
                }
            });
            setUsers(res.users.data || []);
            setSeksis(res.seksis || []);
            setPagination({
                current_page: res.users.current_page || 1,
                last_page: res.users.last_page || 1,
                total: res.users.total || 0
            });
        } catch (err) {
            console.error('[UserManagement] Gagal memuat user:', err);
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        fetchUsers();
    }, [page, searchQuery, roleFilter]);

    // Sync search input with URL params
    const handleSearchSubmit = (e) => {
        e.preventDefault();
        setSearchParams({
            page: '1',
            search: searchInput,
            role: roleFilter
        });
    };

    const handleRoleFilterChange = (role) => {
        setSearchParams({
            page: '1',
            search: searchQuery,
            role
        });
    };

    // Open drawer to add a new user
    const handleAddClick = () => {
        setEditingUser(null);
        setErrorMsg('');
        setSuccessMsg('');
        reset({
            name: '',
            nip: '',
            email: '',
            no_wa: '',
            role: 'petugas_lapangan',
            seksi_id: '',
            password: '',
            is_active: true
        });
        setIsDrawerOpen(true);
    };

    // Open drawer to edit an existing user
    const handleEditClick = (user) => {
        setEditingUser(user);
        setErrorMsg('');
        setSuccessMsg('');
        reset({
            name: user.name,
            nip: user.nip,
            email: user.email,
            no_wa: user.no_wa,
            role: user.role,
            seksi_id: user.seksi_id || '',
            password: '',
            is_active: user.is_active
        });
        setIsDrawerOpen(true);
    };

    // Handle delete user
    const handleDeleteClick = async (userToDelete) => {
        if (currentUser && currentUser.id === userToDelete.id) {
            alert('Gagal! Anda tidak bisa menghapus akun Anda sendiri.');
            return;
        }

        if (confirm(`Apakah Anda yakin ingin menghapus akun user: ${userToDelete.name}?`)) {
            try {
                await api.delete(`/v1/users/${userToDelete.id}`);
                setSuccessMsg('User berhasil dihapus.');
                fetchUsers();
            } catch (err) {
                setErrorMsg(err.message || 'Gagal menghapus user.');
            }
        }
    };

    // Handle form submit
    const onSubmit = async (data) => {
        setIsSubmitting(true);
        setErrorMsg('');
        setSuccessMsg('');
        try {
            // Clean payload
            const payload = { ...data };
            if (payload.role !== 'seksi') {
                payload.seksi_id = null;
            }

            if (editingUser) {
                // Update mode
                if (!payload.password) {
                    delete payload.password; // Don't submit blank password
                }
                const res = await api.put(`/v1/users/${editingUser.id}`, payload);
                setSuccessMsg(res.message || 'User berhasil diperbarui.');
                setIsDrawerOpen(false);
                fetchUsers();
            } else {
                // Create mode
                const res = await api.post('/v1/users', payload);
                setSuccessMsg(res.message || 'User berhasil dibuat.');
                setIsDrawerOpen(false);
                fetchUsers();
            }
        } catch (err) {
            setErrorMsg(err.message || 'Terjadi kesalahan sistem.');
        } finally {
            setIsSubmitting(false);
        }
    };

    const getRoleBadgeColor = (role) => {
        switch (role) {
            case 'admin': return 'border-red-600 text-red-700 bg-red-50';
            case 'seksi': return 'border-blue-600 text-blue-700 bg-blue-50';
            case 'kadis': return 'border-purple-600 text-purple-700 bg-purple-50';
            case 'petugas_lapangan': return 'border-orange-600 text-orange-700 bg-orange-50';
            default: return 'border-slate-300 text-slate-700 bg-slate-50';
        }
    };

    return (
        <div className="font-sans text-slate-800 flex flex-col gap-6 min-h-screen relative">
            {/* Header section */}
            <div className="border-b border-slate-300 pb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 className="text-xl font-bold tracking-tight uppercase text-slate-900">
                        Manajemen User & Akun Petugas
                    </h1>
                    <p className="text-xs text-slate-500 mt-1">
                        Pusat otorisasi hak akses, pembuatan akun operator Seksi, Kadis, serta Regu Petugas Lapangan.
                    </p>
                </div>

                <button
                    onClick={handleAddClick}
                    className="inline-flex items-center gap-2 border border-slate-800 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-bold px-4 py-2 uppercase text-xs tracking-wider transition-colors rounded-none shadow-none"
                >
                    <UserPlus size={14} />
                    <span>Tambah User</span>
                </button>
            </div>

            {/* Notifications */}
            {successMsg && (
                <div className="bg-emerald-50 border border-emerald-400 p-4 rounded-none text-xs text-emerald-800 font-medium">
                    {successMsg}
                </div>
            )}
            {errorMsg && (
                <div className="bg-red-50 border border-red-400 p-4 rounded-none text-xs text-red-800 font-medium flex items-center gap-2">
                    <AlertCircle size={14} />
                    <span>{errorMsg}</span>
                </div>
            )}

            {/* Filters Row */}
            <div className="border border-slate-300 bg-white p-4 flex flex-col md:flex-row gap-4 items-center justify-between rounded-none shadow-none">
                <form onSubmit={handleSearchSubmit} className="flex w-full md:w-auto gap-2">
                    <div className="relative flex-1 md:w-80">
                        <input
                            type="text"
                            placeholder="Cari berdasarkan nama, email, NIP, no WA..."
                            value={searchInput}
                            onChange={(e) => setSearchInput(e.target.value)}
                            className="w-full appearance-none rounded-none border border-slate-300 focus:border-slate-800 bg-white px-3 py-2 text-xs text-slate-800 focus:outline-none placeholder:text-slate-400"
                        />
                        <button type="submit" className="absolute right-3 top-2.5 text-slate-400 hover:text-slate-800">
                            <Search size={14} />
                        </button>
                    </div>
                </form>

                <div className="flex gap-2 w-full md:w-auto overflow-x-auto self-stretch md:self-auto items-center">
                    <span className="text-[10px] uppercase font-bold text-slate-400 mr-2 shrink-0">Filter Role:</span>
                    {[
                        { value: '', label: 'Semua' },
                        { value: 'admin', label: 'Admin' },
                        { value: 'seksi', label: 'Seksi/Bidang' },
                        { value: 'kadis', label: 'Kadis' },
                        { value: 'petugas_lapangan', label: 'Petugas Lapangan' }
                    ].map((btn) => (
                        <button
                            key={btn.value}
                            onClick={() => handleRoleFilterChange(btn.value)}
                            className={`px-3 py-1.5 border text-[10px] font-bold uppercase transition-all rounded-none ${
                                roleFilter === btn.value
                                    ? 'bg-slate-900 border-slate-900 text-white'
                                    : 'bg-white border-slate-300 text-slate-600 hover:border-slate-400'
                            }`}
                        >
                            {btn.label}
                        </button>
                    ))}
                </div>
            </div>

            {/* Data Table */}
            <div className="border border-slate-300 bg-white rounded-none shadow-none overflow-hidden">
                {isLoading ? (
                    <div className="p-12 text-center text-slate-500 text-xs font-mono uppercase tracking-wider">
                        Memuat data user...
                    </div>
                ) : users.length === 0 ? (
                    <div className="p-12 text-center text-slate-400 text-xs italic">
                        Tidak ada data user ditemukan.
                    </div>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr className="bg-slate-100 border-b border-slate-300 font-bold text-slate-500 uppercase tracking-wider text-[10px]">
                                    <th className="p-3">Nama Lengkap / NIP</th>
                                    <th className="p-3">Kontak Email & WA</th>
                                    <th className="p-3">Otorisasi Role</th>
                                    <th className="p-3">Seksi / Bidang Kerja</th>
                                    <th className="p-3">Status</th>
                                    <th className="p-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-200">
                                {users.map((item) => (
                                    <tr key={item.id} className="hover:bg-slate-50 transition-colors">
                                        <td className="p-3">
                                            <div className="font-bold text-slate-950">{item.name}</div>
                                            <div className="text-[10px] text-slate-400 font-mono mt-0.5">NIP: {item.nip}</div>
                                        </td>
                                        <td className="p-3">
                                            <div>{item.email}</div>
                                            <div className="text-[10px] text-slate-500 font-mono mt-0.5">WA: +{item.no_wa}</div>
                                        </td>
                                        <td className="p-3">
                                            <span className={`inline-block border px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-none ${getRoleBadgeColor(item.role)}`}>
                                                {item.role === 'petugas_lapangan' ? 'Petugas Lapangan' : item.role}
                                            </span>
                                        </td>
                                        <td className="p-3 text-slate-600 font-medium">
                                            {item.seksi ? item.seksi.nama_seksi : <span className="text-slate-400 font-normal italic">-</span>}
                                        </td>
                                        <td className="p-3">
                                            <span className={`inline-flex items-center gap-1.5 px-2 py-0.5 border text-[9px] font-bold uppercase tracking-wide rounded-none ${
                                                item.is_active 
                                                    ? 'border-emerald-600 text-emerald-800 bg-emerald-50' 
                                                    : 'border-rose-400 text-rose-800 bg-rose-50'
                                            }`}>
                                                {item.is_active ? 'Aktif' : 'Nonaktif'}
                                            </span>
                                        </td>
                                        <td className="p-3 text-right">
                                            <div className="flex justify-end gap-1.5">
                                                <button
                                                    onClick={() => handleEditClick(item)}
                                                    className="border border-slate-300 bg-white hover:bg-slate-50 active:bg-slate-100 text-slate-700 px-2.5 py-1.5 rounded-none shadow-none font-bold uppercase text-[9px] tracking-tight flex items-center gap-1 transition-colors"
                                                >
                                                    <Edit size={10} />
                                                    <span>Ubah</span>
                                                </button>
                                                <button
                                                    onClick={() => handleDeleteClick(item)}
                                                    disabled={currentUser && currentUser.id === item.id}
                                                    className={`border px-2.5 py-1.5 rounded-none shadow-none font-bold uppercase text-[9px] tracking-tight flex items-center gap-1 transition-colors ${
                                                        currentUser && currentUser.id === item.id
                                                            ? 'border-slate-200 text-slate-300 cursor-not-allowed bg-slate-50'
                                                            : 'border-red-300 text-red-600 hover:bg-red-50 hover:border-red-400 active:bg-red-100'
                                                    }`}
                                                >
                                                    <Trash2 size={10} />
                                                    <span>Hapus</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                {/* Pagination Controls */}
                {pagination.last_page > 1 && (
                    <div className="border-t border-slate-300 bg-slate-50 p-4 flex items-center justify-between">
                        <span className="text-[10px] text-slate-400 font-mono uppercase">
                            Halaman {pagination.current_page} dari {pagination.last_page} ({pagination.total} total akun)
                        </span>

                        <div className="flex gap-1">
                            <button
                                disabled={pagination.current_page === 1}
                                onClick={() => setSearchParams({ page: (pagination.current_page - 1).toString(), search: searchQuery, role: roleFilter })}
                                className="border border-slate-300 bg-white hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 disabled:hover:bg-white disabled:hover:border-slate-300 active:bg-slate-100 px-3 py-1.5 font-bold uppercase text-[10px] tracking-tight rounded-none transition-colors"
                            >
                                Sebelumnya
                            </button>
                            <button
                                disabled={pagination.current_page === pagination.last_page}
                                onClick={() => setSearchParams({ page: (pagination.current_page + 1).toString(), search: searchQuery, role: roleFilter })}
                                className="border border-slate-300 bg-white hover:bg-slate-50 hover:border-slate-400 disabled:opacity-50 disabled:hover:bg-white disabled:hover:border-slate-300 active:bg-slate-100 px-3 py-1.5 font-bold uppercase text-[10px] tracking-tight rounded-none transition-colors"
                            >
                                Selanjutnya
                            </button>
                        </div>
                    </div>
                )}
            </div>

            {/* Brutalist Slide-out Drawer */}
            <div className={`fixed inset-y-0 right-0 w-full sm:w-[450px] bg-white border-l border-slate-800 z-50 shadow-2xl transform transition-transform duration-300 ease-in-out ${isDrawerOpen ? 'translate-x-0' : 'translate-x-full'}`}>
                <div className="h-full flex flex-col justify-between">
                    {/* Header */}
                    <div className="border-b border-slate-800 p-5 bg-slate-900 text-white flex justify-between items-center">
                        <div>
                            <h3 className="font-bold text-xs uppercase tracking-wider text-slate-300">
                                {editingUser ? 'Perbarui Akun User' : 'Registrasi User Baru'}
                            </h3>
                            <h2 className="font-black text-sm tracking-tight uppercase mt-0.5">
                                {editingUser ? editingUser.name : 'Formulir Pendaftaran'}
                            </h2>
                        </div>
                        <button
                            onClick={() => setIsDrawerOpen(false)}
                            className="text-slate-400 hover:text-white p-1 hover:bg-slate-800 transition-colors"
                        >
                            <X size={18} />
                        </button>
                    </div>

                    {/* Scrollable Form Body */}
                    <form onSubmit={handleSubmit(onSubmit)} className="flex-1 overflow-y-auto p-5 flex flex-col gap-4">
                        {/* Name */}
                        <div className="flex flex-col gap-1.5">
                            <label className="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Nama Lengkap</label>
                            <input
                                type="text"
                                {...register('name', { required: 'Nama lengkap wajib diisi.' })}
                                className="w-full appearance-none rounded-none border border-slate-300 focus:border-slate-800 bg-white px-3 py-2 text-xs text-slate-800 focus:outline-none"
                            />
                            {errors.name && <span className="text-[10px] text-red-600 font-medium">{errors.name.message}</span>}
                        </div>

                        {/* NIP (Only editable on create) */}
                        <div className="flex flex-col gap-1.5">
                            <label className="text-[10px] font-bold uppercase text-slate-500 tracking-wider">NIP / Username</label>
                            <input
                                type="text"
                                disabled={!!editingUser}
                                {...register('nip', {
                                    required: 'NIP wajib diisi.',
                                    pattern: { value: /^[0-9]+$/, message: 'NIP harus berupa angka saja.' }
                                })}
                                className="w-full appearance-none rounded-none border border-slate-300 focus:border-slate-800 disabled:bg-slate-50 disabled:text-slate-400 bg-white px-3 py-2 text-xs text-slate-800 focus:outline-none"
                            />
                            {errors.nip && <span className="text-[10px] text-red-600 font-medium">{errors.nip.message}</span>}
                        </div>

                        {/* Email */}
                        <div className="flex flex-col gap-1.5">
                            <label className="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Alamat Email</label>
                            <input
                                type="email"
                                {...register('email', { required: 'Alamat email wajib diisi.' })}
                                className="w-full appearance-none rounded-none border border-slate-300 focus:border-slate-800 bg-white px-3 py-2 text-xs text-slate-800 focus:outline-none"
                            />
                            {errors.email && <span className="text-[10px] text-red-600 font-medium">{errors.email.message}</span>}
                        </div>

                        {/* WhatsApp */}
                        <div className="flex flex-col gap-1.5">
                            <label className="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Nomor WhatsApp</label>
                            <input
                                type="text"
                                placeholder="Contoh: 08123456789 atau 628123456789"
                                {...register('no_wa', { required: 'Nomor WhatsApp wajib diisi.' })}
                                className="w-full appearance-none rounded-none border border-slate-300 focus:border-slate-800 bg-white px-3 py-2 text-xs text-slate-800 focus:outline-none"
                            />
                            {errors.no_wa && <span className="text-[10px] text-red-600 font-medium">{errors.no_wa.message}</span>}
                        </div>

                        {/* Role selection */}
                        <div className="flex flex-col gap-1.5">
                            <label className="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Hak Akses / Role</label>
                            <select
                                {...register('role', { required: 'Role wajib diisi.' })}
                                className="w-full appearance-none rounded-none border border-slate-300 focus:border-slate-800 bg-white px-3 py-2 text-xs text-slate-800 focus:outline-none"
                            >
                                <option value="admin">Administrator (Full Access)</option>
                                <option value="seksi">Operator Seksi/Bidang</option>
                                <option value="kadis">Kepala Dinas (Executive)</option>
                                <option value="petugas_lapangan">Petugas Lapangan (Field Crew)</option>
                            </select>
                            {errors.role && <span className="text-[10px] text-red-600 font-medium">{errors.role.message}</span>}
                        </div>

                        {/* Seksi / Bidang (Conditional, only shown/required if role === 'seksi') */}
                        {watchedRole === 'seksi' && (
                            <div className="flex flex-col gap-1.5">
                                <label className="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Seksi / Bidang Penugasan</label>
                                <select
                                    {...register('seksi_id', { required: 'Bidang/Seksi penugasan wajib dipilih.' })}
                                    className="w-full appearance-none rounded-none border border-slate-300 focus:border-slate-800 bg-white px-3 py-2 text-xs text-slate-800 focus:outline-none"
                                >
                                    <option value="">-- Pilih Bidang --</option>
                                    {seksis.map((sk) => (
                                        <option key={sk.id} value={sk.id}>{sk.nama_seksi}</option>
                                    ))}
                                </select>
                                {errors.seksi_id && <span className="text-[10px] text-red-600 font-medium">{errors.seksi_id.message}</span>}
                            </div>
                        )}

                        {/* Password */}
                        <div className="flex flex-col gap-1.5">
                            <label className="text-[10px] font-bold uppercase text-slate-500 tracking-wider">
                                Password {editingUser ? '(Kosongkan jika tidak diubah)' : '(Minimal 6 karakter)'}
                            </label>
                            <input
                                type="password"
                                {...register('password', {
                                    required: editingUser ? false : 'Password wajib diisi.',
                                    minLength: { value: 6, message: 'Password minimal 6 karakter.' }
                                })}
                                className="w-full appearance-none rounded-none border border-slate-300 focus:border-slate-800 bg-white px-3 py-2 text-xs text-slate-800 focus:outline-none"
                            />
                            {errors.password && <span className="text-[10px] text-red-600 font-medium">{errors.password.message}</span>}
                        </div>

                        {/* Active Status (Only editable on Edit) */}
                        {editingUser && (
                            <div className="flex items-center gap-2 border border-slate-200 p-3 bg-slate-50 select-none">
                                <input
                                    type="checkbox"
                                    id="status-checkbox"
                                    {...register('is_active')}
                                    className="accent-slate-900 cursor-pointer w-4 h-4 rounded-none"
                                />
                                <label htmlFor="status-checkbox" className="text-xs font-bold uppercase text-slate-700 cursor-pointer">
                                    Aktifkan Akun (User dapat Login)
                                </label>
                            </div>
                        )}

                        {/* Submit Row */}
                        <div className="mt-4 border-t border-slate-200 pt-4 flex gap-2">
                            <button
                                type="button"
                                onClick={() => setIsDrawerOpen(false)}
                                className="flex-1 border border-slate-300 hover:border-slate-400 active:bg-slate-50 font-bold px-4 py-2.5 uppercase text-xs tracking-wider transition-colors rounded-none shadow-none text-slate-600 text-center"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                disabled={isSubmitting}
                                className="flex-1 border border-slate-800 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-bold px-4 py-2.5 uppercase text-xs tracking-wider transition-colors rounded-none shadow-none text-center flex items-center justify-center gap-1.5"
                            >
                                {isSubmitting && <RefreshCw size={12} className="animate-spin" />}
                                <span>{isSubmitting ? 'Menyimpan...' : 'Simpan Akun'}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
