// resources/js/components/layout/GisNavbar.jsx

import React, { useState } from 'react';
import { Search, Share2, RefreshCw, Check, Loader2, User } from 'lucide-react';

// Store Zustand (UI dan Domain Data)
import useGisUIStore from '../../store/useGisStore';
import useLitasStore from '../../store/useLitasStore';

/**
 * ============================================================================
 * GisNavbar (Global Header Container - Z-50)
 * ============================================================================
 * Berada di puncak tertinggi tumpukan visual (Z-50) setinggi 64px (h-16).
 * Menyediakan antarmuka pencarian koordinat spasial global, pemicu sinkronisasi
 * data manual, tombol berbagi koordinat, dan identitas sesi pimpinan/petugas.
 */

export default function GisNavbar() {
    const fetchAssets = useLitasStore((state) => state.fetchAssets);
    const fetchRecentReports = useLitasStore((state) => state.fetchRecentReports);
    const fetchReportStats = useLitasStore((state) => state.fetchReportStats);

    // 2. State Lokal UI
    const [searchQuery, setSearchQuery] = useState('');
    const [isSearching, setIsSearching] = useState(false);
    const [isSyncing, setIsSyncing] = useState(false);
    const [isCopied, setIsCopied] = useState(false);

    // Membaca identitas pengguna Laravel Auth yang diekspos secara global (Fallback: Guest)
    const currentUser = window.LaravelUser || {
        name: 'Administrator',
        role: 'Admin Sistem',
        nip: '199208032026111001'
    };

    // 3. LOGIKA SINKRONISASI DATA MANUAL (Sync Button)
    const handleSyncData = async () => {
        setIsSyncing(true);
        try {
            await Promise.all([
                fetchAssets(),
                fetchRecentReports(),
                fetchReportStats()
            ]);
            // Dispatch event untuk membersihkan cache spasial lokal di LitasStore
            useLitasStore.getState().clearLitasCache();
            alert('Sinkronisasi spasial berhasil! Seluruh data aset dan laporan diperbarui.');
        } catch (error) {
            console.error('[GisNavbar] Gagal sinkronisasi data:', error);
            alert('Sinkronisasi gagal. Hubungi tim teknis.');
        } finally {
            setIsSyncing(false);
        }
    };

    // 4. LOGIKA PENCARIAN & GEOCODING (Nominatim API Integration)
    const handleSearchSubmit = async (e) => {
        e.preventDefault();
        if (!searchQuery.trim()) return;

        setIsSearching(true);
        const queryText = searchQuery.trim();

        // Jika user menginput kordinat manual (Format: lat, lng)
        if (queryText.includes(',')) {
            const parts = queryText.split(',');
            if (parts.length === 2) {
                const lat = parseFloat(parts[0].trim());
                const lng = parseFloat(parts[1].trim());

                if (!isNaN(lat) && !isNaN(lng)) {
                    // Terbangkan kamera ke kordinat manual
                    window.dispatchEvent(new CustomEvent('map-fly-to-coords', {
                        detail: { lat, lng, zoom: 16 }
                    }));
                    setIsSearching(false);
                    return;
                }
            }
        }

        // Jika menginput nama wilayah (Kunci pencarian di dalam wilayah Bandung Barat)
        const encodedQuery = encodeURIComponent(`${queryText}, Bandung Barat, Jawa Barat`);
        const searchUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodedQuery}&limit=1`;

        try {
            const response = await fetch(searchUrl);
            const data = await response.json();

            if (data && data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lon = parseFloat(data[0].lon);

                // Terbangkan kamera peta ke lokasi pencarian Nominatim
                window.dispatchEvent(new CustomEvent('map-fly-to-coords', {
                    detail: { lat, lng: lon, zoom: 15 }
                }));
            } else {
                alert(`Wilayah "${queryText}" tidak terdeteksi di area Bandung Barat.`);
            }
        } catch (error) {
            console.error('[GisNavbar] Gagal melakukan geocoding:', error);
            alert('Terjadi gangguan jaringan pencarian peta.');
        } finally {
            setIsSearching(false);
        }
    };

    // 5. LOGIKA BAGIKAN KOORDINAT (Share Button)
    const handleShareCoordinates = async () => {
        const center = useGisUIStore.getState().mapCenter;
        const zoom = useGisUIStore.getState().mapZoom;
        const shareText = `LINTAS KBB Monitoring:\nFokus kordinat saat ini: ${center[0].toFixed(5)}, ${center[1].toFixed(5)} (Zoom: ${zoom})`;

        try {
            await navigator.clipboard.writeText(shareText);
            setIsCopied(true);
            setTimeout(() => setIsCopied(false), 2000);
        } catch (err) {
            console.error('[GisNavbar] Gagal menyalin kordinat:', err);
        }
    };

    return (
        <nav className="w-full h-16 px-6 flex items-center justify-between bg-white border-b border-slate-200/80 text-slate-800 select-none shadow-sm">

            {/* KIRI: Branding LINTAS KBB */}
            <div className="flex items-center gap-4">
                <div className="flex items-center gap-3">
                    <img src="/img/logo_dishub_kbb.png" alt="Logo Dishub" className="w-9 h-9 object-contain" />
                    <div className="flex flex-col leading-none">
                        <span className="text-sm font-black tracking-tight text-slate-800 uppercase">
                            LINTAS <span className="text-[#2563eb]">KBB</span>
                        </span>
                        <span className="text-[8px] font-black text-slate-400 uppercase tracking-[0.2em] mt-0.5">
                            Command Center GIS
                        </span>
                    </div>
                </div>
            </div>

            {/* TENGAH: Input Pencarian Geocoding */}
            <div className="hidden md:flex flex-1 max-w-lg mx-8 relative">
                <form onSubmit={handleSearchSubmit} className="w-full relative group">
                    <button type="submit" disabled={isSearching} className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#2563eb]">
                        {isSearching ? <Loader2 size={16} className="animate-spin text-[#2563eb]" /> : <Search size={16} />}
                    </button>
                    <input
                        type="text"
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        placeholder="Cari wilayah (misal: Padalarang) atau koordinat (lat, lng)..."
                        className="w-full bg-slate-50 border border-slate-200/80 py-2.5 pl-12 pr-6 text-xs text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#2563eb] focus:bg-white transition-all rounded-none"
                    />
                </form>
            </div>

            {/* KANAN: Sync, Share, Profile */}
            <div className="flex items-center gap-4">

                {/* Button A: Sync Spasial */}
                <button
                    onClick={handleSyncData}
                    disabled={isSyncing}
                    className="flex items-center gap-2 px-3 py-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-all rounded-none outline-none"
                    title="Sinkronisasi seluruh data spasial langsung dari PostgreSQL"
                >
                    {isSyncing ? <Loader2 size={14} className="animate-spin text-[#2563eb]" /> : <RefreshCw size={14} />}
                    <span className="text-[10px] font-black uppercase tracking-widest hidden lg:block">Sync</span>
                </button>

                {/* Button B: Share GPS */}
                <button
                    onClick={handleShareCoordinates}
                    className={`flex items-center gap-2 px-3 py-2 transition-all rounded-none outline-none ${isCopied ? 'text-[#2563eb]' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100'}`}
                    title="Salin koordinat peta saat ini ke papan klip"
                >
                    {isCopied ? <Check size={14} className="stroke-[2.5px]" /> : <Share2 size={14} />}
                    <span className="text-[10px] font-black uppercase tracking-widest hidden lg:block">
                        {isCopied ? 'Tersalin' : 'Bagikan'}
                    </span>
                </button>

                {/* Separator vertikal */}
                <div className="h-8 w-px bg-slate-200 hidden sm:block mx-1"></div>

                {/* Profil Sesi */}
                <div className="flex items-center gap-3 pl-2">
                    <div className="hidden sm:flex flex-col items-end leading-none">
                        <span className="text-[10px] font-black text-slate-800 uppercase tracking-tight truncate max-w-28">
                            {currentUser.name}
                        </span>
                        <span className="text-[8px] text-[#2563eb] font-black uppercase tracking-widest mt-0.5">
                            {currentUser.role}
                        </span>
                    </div>

                    <div className="w-9 h-9 bg-[#2563eb]/10 text-[#2563eb] flex items-center justify-center text-xs font-black border-2 border-[#2563eb]/30 rounded-none shrink-0 shadow-inner">
                        <User size={16} />
                    </div>
                </div>

            </div>
        </nav>
    );
}