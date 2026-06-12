// resources/js/components/layout/GisSidebar.jsx

import React, { useMemo } from 'react';
import { Layers, AlertTriangle, Settings, Info, FolderGit } from 'lucide-react';

// Store Zustand (UI Controller)
import useGisUIStore from '../../store/useGisUIStore';

/**
 * ============================================================================
 * GisSidebar (Slim Anchor Navigation Container - Z-40)
 * ============================================================================
 * Berdiri ramping setinggi penuh viewport minus navbar (top-16) selebar 64px (w-16).
 * Berfungsi murni sebagai jangkar tombol (*Slim Anchor*) untuk mengontrol daur hidup
 * penumpukan laci (*Panel Orchestrator*) di Z-30.
 */

export default function GisSidebar() {
    const { activePanels, openPanel, closePanelsToTheRight } = useGisUIStore();

    // Menu Navigasi Samping Ramping KBB
    const navItems = useMemo(() => [
        {
            type: 'katalog-aset',
            label: 'Aset',
            icon: FolderGit,
            title: 'Katalog Inventaris Aset Dishub'
        },
        {
            type: 'katalog-laporan',
            label: 'Aduan',
            icon: AlertTriangle,
            title: 'Katalog Pengaduan Masyarakat'
        },
        {
            type: 'konfigurasi',
            label: 'Peta',
            icon: Settings,
            title: 'Pengaturan Basemap & Opacity'
        }
    ], []);

    // Helper untuk mengecek apakah jenis panel ini sedang terbuka di layar
    const isPanelActive = (type) => {
        return activePanels.some(p => p.type === type);
    };

    // Handler klik: Menghapus laci melayang sebelumnya, lalu membuka menu utama terkait
    const handleNavClick = (item) => {
        closePanelsToTheRight(-1); // Tutup laci melayang detail lama
        openPanel(item.type, item.title);
    };

    return (
        <aside className="w-16 h-full flex flex-col items-center bg-white border-r border-slate-200/80 text-slate-800 select-none shrink-0 z-40">

            {/* Rumpun Menu Atas */}
            <div className="flex-1 flex flex-col items-center w-full">
                {navItems.map((item, index) => {
                    const isActive = isPanelActive(item.type);

                    return (
                        <div key={index} className="relative group w-full flex justify-center">
                            <button
                                onClick={() => handleNavClick(item)}
                                className={`w-full h-16 flex flex-col items-center justify-center gap-1 transition-all relative active:bg-white/5 rounded-none outline-none border-l-[3px]
                                    ${isActive
                                        ? 'bg-blue-50 text-[#2563eb] border-[#2563eb]'
                                        : 'text-slate-400 hover:text-slate-800 hover:bg-slate-50 border-transparent'
                                    }`}
                            >
                                <item.icon size={18} strokeWidth={isActive ? 2.5 : 2} />
                                <span className={`text-[8px] font-black uppercase tracking-widest ${isActive ? 'opacity-100' : 'opacity-60'}`}>
                                    {item.label}
                                </span>
                            </button>

                            {/* Tooltip Label (Desktop Hover) */}
                            <div className="hidden md:block absolute top-1/2 left-full -translate-y-1/2 ml-2 px-3 py-2 bg-white text-slate-800 text-[10px] font-black uppercase tracking-widest rounded-none opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 shadow-xl border border-slate-200/80">
                                {item.title}
                                <div className="absolute top-1/2 -left-1 -translate-y-1/2 w-2 h-2 bg-white rotate-45 border-b border-l border-slate-200/80 rounded-none" />
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Rumpun Menu Bawah (Info / Tentang) */}
            <div className="flex flex-col items-center w-full mt-auto">
                <div className="w-8 h-px bg-slate-200/80 mb-2"></div>

                <div className="relative group w-full flex justify-center">
                    <button
                        onClick={() => {
                            closePanelsToTheRight(-1);
                            openPanel('tentang', 'Tentang LINTAS KBB Spasial');
                        }}
                        className={`w-full h-16 flex items-center justify-center transition-all relative active:bg-white/5 rounded-none border-l-[3px]
                            ${isPanelActive('tentang')
                                ? 'bg-blue-50 text-[#2563eb] border-[#2563eb]'
                                : 'text-slate-400 hover:text-slate-800 hover:bg-slate-50 border-transparent'
                            }`}
                    >
                        <Info size={18} strokeWidth={isPanelActive('tentang') ? 2.5 : 2} />
                    </button>

                    {/* Tooltip Tentang */}
                    <div className="hidden md:block absolute top-1/2 left-full -translate-y-1/2 ml-2 px-3 py-2 bg-white text-slate-800 text-[10px] font-black uppercase tracking-widest rounded-none opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-50 shadow-xl border border-slate-200/80">
                        Tentang Aplikasi
                        <div className="absolute top-1/2 -left-1 -translate-y-1/2 w-2 h-2 bg-white rotate-45 border-b border-l border-slate-200/80 rounded-none" />
                    </div>
                </div>
            </div>

        </aside>
    );
}