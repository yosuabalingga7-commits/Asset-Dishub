// resources/js/components/gis/panels/AssetCatalogPanel.jsx

import React, { useState, useMemo, useEffect } from 'react';
import { Search, ChevronDown, FolderGit, Lightbulb, TrafficCone, AlertTriangle, Video, Bus, MapPin } from 'lucide-react';

// Store Zustand (UI dan Domain Data)
import useGisUIStore from '../../../store/useGisUIStore';
import useLitasStore from '../../../store/useLitasStore';

/**
 * ============================================================================
 * AssetCatalogPanel (Kategori -> Aset Explorer Sub-Panel)
 * ============================================================================
 * Menyediakan antarmuka pencarian dan penelusuran aset terstruktur.
 * Mengelompokkan aset secara dinamis ke dalam rumpun kategori Dishub KBB.
 * 
 * VIBRANT LIGHT THEME: bg-white, border-slate-200/80, text-slate-700.
 */

// Konfigurasi visual statis untuk Rumpun Kategori Utama Dishub KBB (Information Expert)
const CATEGORY_META = {
    'Penerangan Jalan Umum (PJU)': { icon: Lightbulb, color: '#fbbf24' },
    'Penerangan Jalan Umum (PJU) (5)': { icon: Lightbulb, color: '#fbbf24' },
    'Perlengkapan Jalan': { icon: TrafficCone, color: '#f59e0b' },
    'Perlengkapan Jalan (4)': { icon: TrafficCone, color: '#f59e0b' },
    'Fasilitas Lalu Lintas': { icon: AlertTriangle, color: '#3b82f6' },
    'Fasilitas Lalu Lintas (5)': { icon: AlertTriangle, color: '#3b82f6' },
    'Pengendalian & Pengawasan': { icon: Video, color: '#00e5ff' },
    'Pengendalian & Pengawasan (4)': { icon: Video, color: '#00e5ff' },
    'Prasarana Transportasi': { icon: Bus, color: '#16a34a' },
    'Prasarana Transportasi (2)': { icon: Bus, color: '#16a34a' }
};

export default function AssetCatalogPanel() {
    const catalogAssets = useLitasStore((state) => state.catalogAssets);
    const fetchAssets = useLitasStore((state) => state.fetchAssets);
    const isAssetsLoading = useLitasStore((state) => state.isAssetsLoading);

    const openPanel = useGisUIStore((state) => state.openPanel);
    const closePanelsToTheRight = useGisUIStore((state) => state.closePanelsToTheRight);
    const setSelectedAssetId = useGisUIStore((state) => state.setSelectedAssetId);
    const selectedAssetId = useGisUIStore((state) => state.selectedAssetId);

    const [searchQuery, setSearchQuery] = useState('');
    const [expandedCategory, setExpandedCategory] = useState(null);
    const [visibleCounts, setVisibleCounts] = useState({});

    // Auto-hydrate data secara global jika katalog masih kosong saat laci dibuka
    useEffect(() => {
        if (catalogAssets.length === 0 && !isAssetsLoading) {
            // Panggilan tanpa bounds → mengisi catalogAssets global (tidak merusak mapAssets)
            fetchAssets();
        }
    }, [catalogAssets.length, isAssetsLoading, fetchAssets]);

    // 1. FILTER & GROUPING DATA REAL-TIME O(N)
    const groupedAndFilteredAssets = useMemo(() => {
        const groups = {};
        const query = searchQuery.toLowerCase().trim();

        // Saring elemen klaster spasial tingkat DB agar tidak tampil di katalog pencarian teks
        const individualAssets = catalogAssets.filter(item => !item.is_cluster);

        individualAssets.forEach(asset => {
            const matchesQuery = !query ||
                asset.nama.toLowerCase().includes(query) ||
                asset.id_asset.toLowerCase().includes(query) ||
                (asset.alamat && asset.alamat.toLowerCase().includes(query)) ||
                (asset.jenis && asset.jenis.toLowerCase().includes(query));

            if (matchesQuery) {
                const catName = asset.kategori || 'Lainnya';
                if (!groups[catName]) groups[catName] = [];
                groups[catName].push(asset);
            }
        });

        return groups;
    }, [catalogAssets, searchQuery]);

    // Set akordion pertama terbuka otomatis jika hasil pencarian diketik oleh pengguna
    useEffect(() => {
        if (searchQuery.trim() !== '') {
            const firstGroupKey = Object.keys(groupedAndFilteredAssets)[0];
            if (firstGroupKey) setExpandedCategory(firstGroupKey);
        }
    }, [searchQuery, groupedAndFilteredAssets]);

    const handleAssetClick = (asset) => {
        // 1. Tandai ID aset terpilih di dalam penyimpanan keadaan visual
        setSelectedAssetId(asset.id);

        // 2. Bersihkan tumpukan laci melayang sebelah kanan
        closePanelsToTheRight(-1);

        // 3. Tampilkan panel rincian spesifik aset
        openPanel('detil-aset', `Detail Aset: ${asset.id_asset}`, asset);
    };

    return (
        <div className="flex flex-col h-full bg-white pb-6 font-sans text-slate-700 text-left">

            {/* SEARCH BAR (Sticky Header) */}
            <div className="px-4 py-2.5 border-b border-slate-200 bg-slate-50/80 sticky top-0 z-10 backdrop-blur-sm">
                <div className="relative group">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#2563eb]" size={14} />
                    <input
                        type="text"
                        placeholder="Cari nama, ID, atau jalan..."
                        value={searchQuery}
                        onChange={(e) => setSearchQuery(e.target.value)}
                        className="w-full bg-white border border-slate-200 rounded-none py-1.5 pl-8 pr-3 text-[11px] font-semibold text-slate-700 placeholder:text-slate-400 focus:outline-none focus:border-[#2563eb] transition-all"
                    />
                </div>
            </div>

            {/* CATALOG LIST CONTAINER */}
            <div className="flex-1 overflow-y-auto custom-scrollbar flex flex-col">
                {isAssetsLoading ? (
                    <div className="p-8 text-center space-y-3">
                        <div className="w-8 h-8 border-4 border-[#2563eb] border-t-transparent rounded-full animate-spin mx-auto"></div>
                        <p className="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Memuat Database PostgreSQL...</p>
                    </div>
                ) : Object.keys(groupedAndFilteredAssets).length > 0 ? (
                    Object.keys(groupedAndFilteredAssets).map((catName) => {
                        const isExpanded = expandedCategory === catName;
                        const catAssets = groupedAndFilteredAssets[catName] || [];
                        const meta = CATEGORY_META[catName] || { icon: MapPin, color: '#64748b' };

                        return (
                            <div key={catName} className="flex flex-col border-b border-slate-200/80">

                                {/* Akordion Header */}
                                <button
                                    onClick={() => setExpandedCategory(isExpanded ? null : catName)}
                                    className="flex items-center justify-between px-4 py-3 bg-slate-50 hover:bg-slate-100/80 border-b border-slate-200/80 transition-colors w-full text-left rounded-none outline-none"
                                >
                                    <div className="flex items-center gap-2 min-w-0">
                                        <ChevronDown
                                            size={14}
                                            className={`text-slate-400 shrink-0 transition-transform duration-200 ${isExpanded ? 'rotate-180' : ''}`}
                                        />
                                        <span className="shrink-0 flex items-center justify-center w-5 h-5 rounded bg-slate-100 border border-slate-200/60">
                                            <meta.icon size={12} style={{ color: meta.color }} />
                                        </span>
                                        <span className="text-[11px] font-black uppercase text-slate-600 truncate tracking-wide">
                                            {catName}
                                        </span>
                                    </div>
                                    <span className="bg-slate-200/60 text-slate-600 font-mono text-[9px] font-black px-1.5 py-0.5 rounded-sm shadow-inner shrink-0 border border-slate-300/50">
                                        {catAssets.length}
                                    </span>
                                </button>

                                {/* List Aset di dalam Rumpun Kategori */}
                                {isExpanded && (
                                    <div className="flex flex-col bg-white animate-in slide-in-from-top-1 duration-150">
                                        {catAssets.slice(0, visibleCounts[catName] || 30).map((asset) => {
                                            const isSelected = selectedAssetId === asset.id;
                                            return (
                                                <button
                                                    key={asset.id}
                                                    onClick={() => handleAssetClick(asset)}
                                                    className={`flex items-center gap-3 px-6 py-2.5 border-b border-slate-100 hover:bg-slate-50 transition-colors w-full text-left rounded-none outline-none border-l-[3px]
                                                        ${isSelected ? 'bg-[#2563eb]/5 border-l-[#2563eb]' : 'border-l-transparent'}`}
                                                >
                                                    {/* Penanda warna kondisi dinamis */}
                                                    <span className="w-2.5 h-2.5 rounded-full shrink-0 shadow-sm border border-white" style={{
                                                        backgroundColor: asset.status === 'Baik' ? '#10b981' : asset.status === 'Rusak' ? '#f59e0b' : '#dc2626'
                                                    }}></span>

                                                    <div className="flex flex-col leading-none min-w-0">
                                                        <span className={`text-[11px] leading-tight truncate ${isSelected ? 'font-bold text-[#2563eb]' : 'font-semibold text-slate-700'}`}>
                                                            {asset.nama}
                                                        </span>
                                                        <span className="text-[9px] text-slate-400 font-bold uppercase tracking-wide mt-1">
                                                            {asset.id_asset} • {asset.jenis}
                                                        </span>
                                                    </div>
                                                </button>
                                            );
                                        })}
                                        {catAssets.length > (visibleCounts[catName] || 30) && (
                                            <button
                                                onClick={() => {
                                                    setVisibleCounts(prev => ({
                                                        ...prev,
                                                        [catName]: (prev[catName] || 30) + 50
                                                    }));
                                                }}
                                                className="w-full py-2 bg-slate-50 border-t border-slate-100 hover:bg-slate-100/80 text-[10px] font-black text-[#2563eb] tracking-widest uppercase transition-colors outline-none cursor-pointer text-center"
                                            >
                                                Muat Lebih Banyak ({catAssets.length - (visibleCounts[catName] || 30)} Tersisa)
                                            </button>
                                        )}
                                    </div>
                                )}
                            </div>
                        );
                    })
                ) : (
                    <div className="p-8 text-center text-slate-400 space-y-2">
                        <FolderGit className="mx-auto text-slate-300" size={24} />
                        <p className="text-[10px] font-black uppercase tracking-wider text-slate-400">Aset tidak ditemukan</p>
                    </div>
                )}
            </div>
        </div>
    );
}