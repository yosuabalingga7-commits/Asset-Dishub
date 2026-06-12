// resources/js/components/gis/panels/DetailReportPanel.jsx

import React, { useMemo } from 'react';
import { X, MapPin, AlertTriangle, User, Phone, ShieldCheck, Eye, ExternalLink, Copy, FileText, Wrench } from 'lucide-react';

// Store Zustand (UI Controller)
import useGisUIStore from '../../../store/useGisUIStore';

/**
 * ============================================================================
 * DetailReportPanel (Citizen/Officer Report Stacking Drawer - Z-30 Floating)
 * ============================================================================
 * Menampilkan data forensik pengaduan kerusakan aset yang dilaporkan.
 * Menyediakan alat verifikasi visual foto bukti, informasi pelapor terenkripsi,
 * serta tombol aksi integrasi langsung ke sistem verifikasi admin portal.
 *
 * VIBRANT LIGHT THEME: bg-white, border-slate-200/80, text-slate-700.
 */

export default function DetailReportPanel({ reportData, panelId }) {
    const closePanel = useGisUIStore((state) => state.closePanel);

    if (!reportData) return null;

    // 1. RESOLVER PHOTO BUKTI FISIK LAPANGAN
    // Mengecek apakah ada foto bukti dari storage, jika tidak tampilkan default warning placeholder
    const imagePath = reportData.foto
        ? (reportData.foto.startsWith('http') ? reportData.foto : `/storage/${reportData.foto}`)
        : '/img/generic.png';

    // 2. WHATSAPP FORMATTER (Information Expert)
    const waLink = useMemo(() => {
        const rawContact = reportData.kontak_pelapor || reportData.no_wa;
        if (!rawContact) return null;

        // Bersihkan semua karakter selain angka
        let cleaned = rawContact.replace(/[^0-9]/g, '');
        // Ganti awalan 0 menjadi 62 (Kode Internasional Indonesia)
        if (cleaned.startsWith('0')) {
            cleaned = '62' + cleaned.substring(1);
        }

        const message = encodeURIComponent(
            `Halo, saya Verifikator LINTAS Dishub KBB.\n\nTerkait laporan Anda mengenai *"${reportData.judul_laporan}"* (Tiket: #${reportData.ticket_number}), kami memerlukan informasi tambahan untuk validasi lapangan.`
        );

        return `https://wa.me/${cleaned}?text=${message}`;
    }, [reportData]);

    const handleCopyCoords = () => {
        const latVal = parseFloat(reportData.lat);
        const lngVal = parseFloat(reportData.lng);
        if (!isNaN(latVal) && !isNaN(lngVal)) {
            navigator.clipboard.writeText(`${latVal.toFixed(6)}, ${lngVal.toFixed(6)}`);
            alert('Koordinat laporan disalin ke papan klip!');
        }
    };

    // Tentukan skema warna badge berdasarkan status pengaduan
    const statusLower = reportData.status ? reportData.status.toLowerCase() : 'masuk';
    const isNewReport = statusLower === 'masuk';

    return (
        <div className="flex flex-col h-full bg-white text-slate-700 font-sans text-left">

            {/* COMPACT STICKY HEADER */}
            <div className="px-4 h-12 border-b border-slate-200 flex justify-between items-center bg-slate-50 shrink-0 select-none">
                <div className="flex flex-col pr-2 min-w-0">
                    <span className="text-[8px] font-black text-rose-600 bg-rose-50 px-1.5 py-0.5 uppercase tracking-wider leading-none w-fit border border-rose-200">
                        TIKET: #{reportData.ticket_number}
                    </span>
                    <h3 className="text-xs font-bold text-slate-800 truncate max-w-[180px] mt-1 leading-none uppercase">
                        {reportData.judul_laporan}
                    </h3>
                </div>

                <button
                    onClick={() => closePanel(panelId)}
                    className="p-1 rounded-none hover:bg-slate-200 text-slate-500 hover:text-rose-500 transition-colors active:scale-95 outline-none"
                    title="Tutup Panel"
                >
                    <X size={14} strokeWidth={2.5} />
                </button>
            </div>

            {/* SCROLLABLE CONTENT BODY */}
            <div className="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-4">

                {/* 1. BUKTI FOTO KONDISI LAPANGAN (EDGE-TO-EDGE) */}
                <div className="relative w-full h-36 bg-slate-100 border border-slate-200 shadow-inner overflow-hidden shrink-0 select-none">
                    <img
                        src={imagePath}
                        alt={reportData.judul_laporan}
                        className="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                        draggable={false}
                        onError={(e) => {
                            e.target.onerror = null;
                            e.target.src = '/img/generic.png';
                        }}
                    />

                    {/* Status Badge Pengaduan Melayang */}
                    <div className="absolute top-2.5 right-2.5 z-10 flex">
                        <span className={`px-2.5 py-1 text-[8px] font-black uppercase tracking-widest text-white border border-white/20 shadow-md
                            ${isNewReport ? 'bg-rose-600 animate-pulse' : 'bg-blue-600'}`}
                        >
                            {reportData.status}
                        </span>
                    </div>
                </div>

                {/* 2. ADAPTIVE WHISTLEBLOWER IDENTITY PANEL */}
                <div className="p-3 bg-slate-50 border border-slate-200 rounded-none text-left space-y-2.5 select-none">
                    <div className="border-b border-slate-200 pb-1.5 flex items-center gap-1.5">
                        <User className="text-[#2563eb]" size={13} />
                        <span className="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Identitas Pelapor</span>
                    </div>

                    {reportData.nama_pelapor ? (
                        <div className="space-y-2 text-[11px] font-medium text-slate-600">
                            <div className="flex justify-between">
                                <span className="text-slate-400 font-bold uppercase text-[9px] tracking-wider">Nama:</span>
                                <span className="text-slate-800 font-bold">{reportData.nama_pelapor}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-slate-400 font-bold uppercase text-[9px] tracking-wider">Sumber:</span>
                                <span className="text-[#2563eb] font-bold uppercase text-[9px]">{reportData.source || 'Masyarakat'}</span>
                            </div>
                            {waLink && (
                                <div className="pt-1.5 border-t border-slate-200 mt-1 flex justify-end">
                                    <a
                                        href={waLink}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="inline-flex items-center gap-1 bg-[#25D366] hover:bg-[#128C7E] text-white font-black text-[8px] tracking-widest uppercase px-2.5 py-1 transition-all shadow-sm"
                                    >
                                        Hubungi Pelapor via WA
                                    </a>
                                </div>
                            )}
                        </div>
                    ) : (
                        <div className="flex items-start gap-2.5 text-emerald-600">
                            <ShieldCheck className="text-emerald-500 shrink-0 mt-0.5 animate-pulse" size={15} />
                            <div className="space-y-0.5">
                                <h5 className="text-[9px] font-black text-emerald-500 uppercase tracking-widest leading-none">Anonim Aman</h5>
                                <p className="text-[8px] text-emerald-600 leading-normal font-semibold">
                                    Identitas terlindungi penuh oleh enkripsi server.
                                </p>
                            </div>
                        </div>
                    )}
                </div>

                {/* 3. KRONOLOGI MASALAH / KELUHAN */}
                <div className="space-y-1.5 text-left">
                    <span className="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Keluhan & Kronologi Kejadian</span>
                    <div className="bg-slate-50 border border-slate-200 p-3 text-[11px] font-medium leading-relaxed italic text-justify text-slate-500 rounded-none">
                        "{reportData.deskripsi_keluhan || reportData.deskripsi || 'Tidak ada catatan kronologi keluhan tertulis dari pelapor.'}"
                    </div>
                </div>

                {/* 4. KOORDINAT GIS & ALAMAT */}
                <div className="space-y-1.5 text-left">
                    <span className="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Lokasi & Geotagging</span>
                    <div className="bg-slate-50 border border-slate-200 p-2.5 flex justify-between items-center rounded-none shadow-inner mb-2">
                        <div className="flex flex-col text-left">
                            <span className="text-[8px] font-bold text-slate-400 uppercase leading-none">Kordinat GPS Laporan</span>
                            <span className="text-[10px] font-mono font-black text-slate-700 mt-1 leading-none">
                                {parseFloat(reportData.lat).toFixed(6)}, {parseFloat(reportData.lng).toFixed(6)}
                            </span>
                        </div>
                        <button
                            onClick={handleCopyCoords}
                            className="p-1.5 bg-white border border-slate-200 text-slate-500 hover:text-[#2563eb] hover:border-[#2563eb] transition-all rounded-none outline-none shrink-0"
                            title="Salin koordinat"
                        >
                            <Copy size={11} />
                        </button>
                    </div>

                    <div className="border border-slate-200 bg-white p-2.5 rounded-none flex items-start gap-2">
                        <MapPin size={12} className="text-rose-600 shrink-0 mt-0.5" />
                        <p className="text-[10.5px] font-semibold text-slate-600 leading-snug wrap-break-word">
                            {reportData.alamat || 'Alamat tidak diinput secara spesifik.'}
                        </p>
                    </div>
                </div>

                {/* 5. REFERENSI ASSET TARGET (Jika Terikat) */}
                {reportData.id_asset && (
                    <div className="space-y-1.5 text-left">
                        <span className="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Referensi Aset Terdampak</span>
                        <div className="border border-slate-200 bg-slate-50 p-2.5 rounded-none flex items-center justify-between gap-4">
                            <div className="flex items-center gap-2.5 min-w-0">
                                <div className="w-6 h-6 bg-slate-200 border border-slate-300 flex items-center justify-center shrink-0">
                                    <FileText size={12} className="text-slate-500" />
                                </div>
                                <div className="min-w-0">
                                    <p className="text-[10px] font-black text-[#f59e0b] uppercase tracking-wider leading-none">ID Aset: {reportData.id_asset}</p>
                                    <p className="text-[9px] text-slate-400 truncate mt-1 leading-none">Kondisi: {reportData.kondisi_aset}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

            </div>

            {/* ACTION FOOTER BAR */}
            <div className="p-4 border-t border-slate-200 bg-slate-50 shrink-0">
                <a
                    href={`/admin/pengaduan/detail/${reportData.id}`}
                    className="w-full h-11 bg-[#2563eb] hover:bg-[#1d4ed8] text-white rounded-none font-black text-[10px] uppercase tracking-widest shadow-none transition-colors flex items-center justify-center gap-2 border-none outline-none"
                >
                    <Eye size={12} />
                    Buka Verifikasi Triage <ExternalLink size={11} />
                </a>
            </div>

        </div>
    );
}