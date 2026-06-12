// resources/js/pages/errors/ForbiddenPage.jsx
import React from 'react';
import { useNavigate } from 'react-router-dom';

export default function ForbiddenPage() {
    const navigate = useNavigate();
    return (
        <div className="h-screen w-screen flex items-center justify-center bg-slate-50">
            <div className="text-center">
                <p className="text-[64px] font-medium text-slate-200 leading-none tracking-tight">403</p>
                <h1 className="text-base font-medium text-slate-800 mt-2 tracking-tight">Akses Ditolak</h1>
                <p className="text-sm text-slate-500 mt-1">Anda tidak memiliki izin untuk mengakses halaman ini.</p>
                <button
                    id="forbidden-back-btn"
                    onClick={() => navigate(-1)}
                    className="btn-secondary mt-4 text-xs"
                >
                    Kembali
                </button>
            </div>
        </div>
    );
}
