// resources/js/pages/auth/LoginPage.jsx
// ============================================================================
// LoginPage — Public Authentication Entry (BC: Auth)
// ============================================================================
// Design: Industrial, data-dense, no rounded corners, no shadows.
// Form: React Hook Form (validation) + useAuthStore.login()
// On success: navigates to role-appropriate default route.
// ============================================================================

import React, { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { useNavigate } from 'react-router-dom';
import useAuthStore from '../../store/useAuthStore';
import api from '../../services/api';

export default function LoginPage() {
    const { login, isAuthenticated } = useAuthStore();
    const navigate = useNavigate();

    const {
        register,
        handleSubmit,
        setError,
        formState: { errors, isSubmitting },
    } = useForm();

    // If already authenticated (e.g. back-button), redirect immediately
    useEffect(() => {
        if (isAuthenticated) navigate('/dashboard', { replace: true });
    }, [isAuthenticated, navigate]);

    const onSubmit = async ({ nip, password }) => {
        // Step 1: Hydrate CSRF cookie (required before first POST with Sanctum)
        try {
            await api.get('/sanctum/csrf-cookie', { baseURL: '' });
        } catch {
            // non-fatal: CSRF cookie may already be set
        }

        // Step 2: Attempt login
        const result = await login(nip, password);

        if (result.success) {
            // AppRouter's GuestGuard will handle the redirect
            navigate('/', { replace: true });
        } else {
            setError('root', { message: result.message });
        }
    };

    return (
        <div className="h-screen w-screen flex bg-slate-50">

            {/* ── Left panel: Brand / Info ── */}
            <div
                className="hidden lg:flex flex-col justify-between w-96 shrink-0 bg-slate-900 p-8"
                style={{ borderRight: '1px solid #1e293b' }}
            >
                <div>
                    <div className="mb-8">
                        <span className="text-white font-medium text-lg tracking-tight">LINTAS</span>
                        <p className="text-slate-400 text-xs mt-0.5 uppercase tracking-widest">
                            Sistem Informasi Aset Dishub KBB
                        </p>
                    </div>
                    <div className="border-l-2 border-blue-700 pl-4">
                        <p className="text-slate-300 text-sm leading-relaxed">
                            Platform manajemen aset infrastruktur transportasi terintegrasi dengan monitoring spasial PostGIS dan alur kerja pemeliharaan terpadu.
                        </p>
                    </div>
                </div>

                <div className="text-slate-500 text-xs">
                    <div className="flex items-center gap-2 mb-1">
                        <span className="w-1.5 h-1.5 bg-green-500 inline-block"></span>
                        <span>Sistem Aktif</span>
                    </div>
                    <p>Dinas Perhubungan Kabupaten Bandung Barat</p>
                    <p className="mt-0.5">© {new Date().getFullYear()} LINTAS</p>
                </div>
            </div>

            {/* ── Right panel: Login form ── */}
            <div className="flex-1 flex items-center justify-center p-8">
                <div className="w-full max-w-sm">

                    {/* Header */}
                    <div className="mb-6">
                        <h1 className="text-xl font-medium text-slate-900 tracking-tight">
                            Masuk ke Sistem
                        </h1>
                        <p className="text-slate-500 text-sm mt-1">
                            Gunakan NIP dan kata sandi Anda.
                        </p>
                    </div>

                    {/* Form */}
                    <form
                        id="login-form"
                        onSubmit={handleSubmit(onSubmit)}
                        noValidate
                        className="flex flex-col gap-4"
                    >
                        {/* NIP field */}
                        <div>
                            <label
                                htmlFor="login-nip"
                                className="block text-xs font-medium text-slate-600 mb-1"
                            >
                                NIP
                            </label>
                            <input
                                id="login-nip"
                                type="text"
                                autoComplete="username"
                                placeholder="Masukkan NIP Anda"
                                className={`lintas-input ${errors.nip ? 'border-red-500' : ''}`}
                                {...register('nip', { required: 'NIP wajib diisi.' })}
                            />
                            {errors.nip && (
                                <p className="text-red-600 text-xs mt-1">{errors.nip.message}</p>
                            )}
                        </div>

                        {/* Password field */}
                        <div>
                            <label
                                htmlFor="login-password"
                                className="block text-xs font-medium text-slate-600 mb-1"
                            >
                                Kata Sandi
                            </label>
                            <input
                                id="login-password"
                                type="password"
                                autoComplete="current-password"
                                placeholder="••••••••"
                                className={`lintas-input ${errors.password ? 'border-red-500' : ''}`}
                                {...register('password', { required: 'Kata sandi wajib diisi.' })}
                            />
                            {errors.password && (
                                <p className="text-red-600 text-xs mt-1">{errors.password.message}</p>
                            )}
                        </div>

                        {/* Root-level error (wrong credentials) */}
                        {errors.root && (
                            <div
                                className="border border-red-200 bg-red-50 px-3 py-2"
                                role="alert"
                            >
                                <p className="text-red-700 text-xs">{errors.root.message}</p>
                            </div>
                        )}

                        {/* Submit */}
                        <button
                            id="login-submit-btn"
                            type="submit"
                            disabled={isSubmitting}
                            className="btn-primary w-full"
                        >
                            {isSubmitting ? 'Memproses...' : 'Masuk'}
                        </button>
                    </form>

                    {/* Footer note */}
                    <p className="text-slate-400 text-xs mt-6 border-t border-slate-200 pt-4">
                        Akses terbatas untuk pegawai Dinas Perhubungan KBB yang terdaftar.
                    </p>
                </div>
            </div>
        </div>
    );
}
