// resources/js/components/gis/panels/DetailAssetPanel.jsx

import React from 'react';
import { MapPin, Info, Sliders, ShieldAlert, Wrench, X, Copy } from 'lucide-react';

// Store Zustand (UI Controller)
import useGisUIStore from '../../../store/useGisUIStore';

/**
 * ============================================================================
 * DetailAssetPanel (Micro-Metadata Stacking Drawer - Z-30 Floating)
 * ============================================================================
 * Menampilkan rincian rekam medis aset yang terpilih.
 * Menyediakan visualisasi foto, detail kordinat, dan banner mitigasi darurat
 * jika aset terdeteksi rusak/kritis untuk pemicuan tiket maintenance di Laravel [1, 2].
 */

export default function DetailAssetPanel({ assetData, panelId }) {
    const { closePanel } = useGisUIStore();

    if (!assetData) return null;

    // Membaca jalur gambar secara dinamis dari API Resource
    const imagePath = assetData.foto || '/img/default-asset.png';

    const handleCopyCoords = () => {
        const coordsText = `${assetData.lat.toFixed(6)}, ${assetData.lng.toFixed(6)}`;
        navigator.clipboard.writeText(coordsText);
        alert('📋 Koordinat spasial disalin ke papan klip!');
    };

    const isTroubled = assetData.status === 'Rusak' || assetData.status === 'Kritis';

    return (
        <div className="flex flex-col h-full bg-white text-slate-800 font-sans text-left">

            {/* COMPACT STICKY HEADER */}
            <div className="px-4 h-12 border-b border-slate-200 flex justify-between items-center bg-slate-50 shrink-0 select-none">
                <div className="flex flex-col text-left pr-2">
                    <span className="text-[8px] font-black text-rose-600 bg-rose-50 px-1.5 py-0.5 uppercase tracking-wider leading-none w-fit border border-rose-100">
                        {assetData.kategori}
                    </span>
                    <h3 className="text-xs font-bold text-slate-800 truncate max-w-45 mt-1 leading-none uppercase">
                        {assetData.nama}
                    </h3>
                </div>

                <button
                    onClick={() => closePanel(panelId)}
                    className="p-1 rounded-none hover:bg-slate-200 text-slate-400 hover:text-rose-500 transition-colors active:scale-95 outline-none"
                >
                    <X size={14} strokeWidth={2.5} />
                </button>
            </div>

            {/* SCROLLABLE METADATA BODY */}
            <div className="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-4">

                {/* 1. HERO IMAGE (EDGE-TO-EDGE IN PANEL) */}
                <div className="relative w-full h-36 bg-slate-100 border border-slate-200 shadow-inner overflow-hidden shrink-0 select-none">
                    <img
                        src={imagePath}
                        alt={assetData.nama}
                        className="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                        draggable={false}
                        onError={(e) => {
                            e.target.src = '/img/default-asset.png';
                        }}
                    />

                    {/* Status Badge Melayang */}
                    <div className="absolute top-2.5 right-2.5 z-10 flex">
                        <span className={`px-2.5 py-1 text-[8px] font-black uppercase tracking-widest text-white border border-white/20 shadow-md
                            ${assetData.status === 'Baik' ? 'bg-emerald-600' : assetData.status === 'Rusak' ? 'bg-amber-500' : 'bg-rose-600 animate-pulse'}`}
                        >
                            KONDISI: {assetData.status}
                        </span>
                    </div>
                </div>

                {/* 2. WARNING FLAG BANNER FOR INTEGRATED LOGISTICS (Larman Mitigation) */}
                {isTroubled && (
                    <div className="p-3 bg-rose-50 border border-rose-200 rounded-none flex items-start gap-2.5 select-none animate-in fade-in duration-200">
                        <ShieldAlert className="text-rose-600 shrink-0 mt-0.5 animate-pulse" size={15} />
                        <div className="space-y-1">
                            <h4 className="text-[9px] font-black text-rose-900 uppercase tracking-widest leading-none">Butuh Atensi Segera</h4>
                            <p className="text-[9px] font-bold text-rose-700 leading-normal text-justify">
                                Aset terdeteksi mengalami degradasi fungsional ({assetData.status}). Buat surat perintah tugas pemeliharaan (*Maintenance Ticket*) [1, 2].
                            </p>

                            {/* Tautan langsung ke pembuatan tiket di backend Laravel */}
                            <a
                                href={`/admin/maintenance/create?asset_id=${assetData.id}`}
                                className="inline-flex items-center gap-1.5 mt-2 bg-rose-600 hover:bg-rose-700 text-white font-black text-[8px] tracking-widest uppercase px-3 py-1.5 border border-rose-700 hover:border-rose-800 transition-colors shadow-sm"
                            >
                                <Wrench size={10} /> Buat Perintah Kerja (SLA)
                            </a>
                        </div>
                    </div>
                )}

                {/* 3. KOORDINAT SPASIAL & QUICK ACTIONS */}
                <div className="space-y-1.5">
                    <span className="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Geotagging Satelit</span>
                    <div className="bg-slate-50 border border-slate-200 p-2.5 flex justify-between items-center select-all">
                        <div className="flex flex-col text-left">
                            <span className="text-[8px] font-bold text-slate-400 uppercase leading-none">GPS Coordinates</span>
                            <span className="text-[11px] font-mono font-black text-slate-700 mt-1 leading-none">
                                {assetData.lat.toFixed(6)}, {assetData.lng.toFixed(6)}
                            </span>
                        </div>
                        <button
                            onClick={handleCopyCoords}
                            className="p-1.5 bg-white border border-slate-200 text-slate-500 hover:text-[#2563eb] hover:border-[#2563eb] transition-all rounded-none outline-none shrink-0"
                            title="Salin koordinat"
                        >
                            <Copy size={12} />
                        </button>
                    </div>
                </div>

                {/* 4. TABEL DATA METADATA TEKNIS (High-Density Specs Table) */}
                <div className="space-y-1.5 text-left">
                    <span className="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Spesifikasi Teknis</span>
                    <div className="border border-slate-200 bg-white divide-y divide-slate-100 font-sans text-xs">
                        <div className="p-2.5 flex justify-between">
                            <span className="text-slate-400 font-bold uppercase text-[9px] tracking-wider shrink-0 w-24">ID Aset:</span>
                            <span className="font-mono text-slate-800 font-black text-right truncate pl-2">{assetData.id_asset}</span>
                        </div>
                        <div className="p-2.5 flex justify-between">
                            <span className="text-slate-400 font-bold uppercase text-[9px] tracking-wider shrink-0 w-24">Jenis:</span>
                            <span className="text-slate-800 font-bold text-right truncate pl-2">{assetData.jenis}</span>
                        </div>
                        <div className="p-2.5 flex justify-between">
                            <span className="text-slate-400 font-bold uppercase text-[9px] tracking-wider shrink-0 w-24">Alamat Lokasi:</span>
                            <span className="text-slate-800 font-medium text-right text-[11px] leading-tight pl-2 break-all max-w-37.5">{assetData.alamat || '-'}</span>
                        </div>
                        <div className="p-2.5 flex justify-between">
                            <span className="text-slate-400 font-bold uppercase text-[9px] tracking-wider shrink-0 w-24">Merk/Model:</span>
                            <span className="text-slate-800 font-bold text-right truncate pl-2">{assetData.merk || '-'}</span>
                        </div>
                        <div className="p-2.5 flex justify-between">
                            <span className="text-slate-400 font-bold uppercase text-[9px] tracking-wider shrink-0 w-24">Pemasangan:</span>
                            <span className="text-slate-800 font-bold text-right truncate pl-2">
                                {assetData.tgl_pemasangan ? new Date(assetData.tgl_pemasangan).toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: '2-digit' }) : '-'}
                            </span>
                        </div>
                    </div>
                </div>

                {/* 5. ARSIP CATATAN PEMELIHARAAN (Medical History) */}
                <div className="space-y-1.5 text-left">
                    <span className="text-[8px] font-black text-slate-400 uppercase tracking-widest block">Catatan Histori Aset</span>
                    <div className="bg-slate-50 border border-slate-200 p-3 text-[11px] font-medium leading-relaxed italic text-justify text-slate-500 rounded-none">
                        "{assetData.catatan || 'Belum ada catatan historis khusus untuk inventaris fisik ini.'}"
                    </div>
                </div>

            </div>
        </div>
    );
}