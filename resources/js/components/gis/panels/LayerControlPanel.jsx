// resources/js/components/gis/panels/LayerControlPanel.jsx

import React, { useMemo } from 'react';
import { Layers, Sliders, Moon, Sun, Map, Check } from 'lucide-react';

// Store Zustand (UI Controller)
import useGisUIStore from '../../../store/useGisUIStore';

/**
 * ============================================================================
 * LayerControlPanel (Visual Controller Sub-Panel)
 * ============================================================================
 * VIBRANT LIGHT THEME: bg-white, border-slate-200/80, text-slate-700.
 */

export default function LayerControlPanel() {
    const activeBaseMap = useGisUIStore((state) => state.activeBaseMap);
    const mapOpacity    = useGisUIStore((state) => state.mapOpacity);
    const activeLayers  = useGisUIStore((state) => state.activeLayers);

    const setActiveBaseMap = useGisUIStore((state) => state.setActiveBaseMap);
    const setMapOpacity    = useGisUIStore((state) => state.setMapOpacity);
    const toggleLayer      = useGisUIStore((state) => state.toggleLayer);

    const baseMaps = useMemo(() => [
        { id: 'dark',      label: 'Carto Dark',     icon: Moon, desc: 'Paling kontras untuk visual emisi' },
        { id: 'satellite', label: 'Google Satelit',  icon: Sun,  desc: 'Citra foto udara asli' },
        { id: 'street',    label: 'OSM Standard',    icon: Map,  desc: 'Navigasi jalan & administrasi' }
    ], []);

    const featureLayers = useMemo(() => [
        { id: 'assets',  label: 'Aset Dishub KBB', desc: 'PJU, Rambu, & Traffic Light' },
        { id: 'reports', label: 'Pengaduan Aktif',  desc: 'Laporan warga & petugas' },
    ], []);

    return (
        <div className="flex flex-col h-full bg-white pb-6 font-sans text-slate-700 text-left">

            {/* SEKSI 1: PILIHAN BASEMAP */}
            <div className="flex flex-col border-b border-slate-200/80">
                <div className="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border-b border-slate-200/80 text-slate-500">
                    <Map size={13} className="text-[#2563eb]" />
                    <h4 className="text-[10px] font-black uppercase tracking-widest">Pilih Peta Dasar</h4>
                </div>

                <div className="flex flex-col">
                    {baseMaps.map((map) => {
                        const isActive = activeBaseMap === map.id;
                        return (
                            <button
                                key={map.id}
                                onClick={() => setActiveBaseMap(map.id)}
                                className={`flex items-center justify-between px-4 py-3 border-b border-slate-100 hover:bg-slate-50 transition-colors w-full text-left rounded-none outline-none
                                    ${isActive ? 'bg-[#2563eb]/5 border-l-2 border-l-[#2563eb]' : 'border-l-2 border-l-transparent'}`}
                            >
                                <div className="flex items-center gap-3">
                                    <div className={`p-1.5 rounded-none ${isActive ? 'text-[#2563eb]' : 'text-slate-400'}`}>
                                        <map.icon size={15} />
                                    </div>
                                    <div className="flex flex-col leading-none">
                                        <span className={`text-xs ${isActive ? 'font-bold text-slate-800' : 'font-semibold text-slate-600'}`}>
                                            {map.label}
                                        </span>
                                        <span className="text-[9px] text-slate-400 font-medium mt-0.5">{map.desc}</span>
                                    </div>
                                </div>
                                {isActive && <Check size={14} className="text-[#2563eb] stroke-[2.5px] mr-1" />}
                            </button>
                        );
                    })}
                </div>
            </div>

            {/* SEKSI 2: PENGATURAN BASEMAP & OPACITY (Section Header) */}
            <div className="flex flex-col border-b border-slate-200/80">
                <div className="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border-b border-slate-200/80 text-slate-500">
                    <span className="text-[9px] font-black uppercase tracking-widest">Pengaturan Basemap & Opacity</span>
                </div>
            </div>

            {/* SEKSI 3: TOGGLE LAPISAN SPASIAL */}
            <div className="flex flex-col border-b border-slate-200/80">
                <div className="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border-b border-slate-200/80 text-slate-500">
                    <Layers size={13} className="text-[#2563eb]" />
                    <h4 className="text-[10px] font-black uppercase tracking-widest">Lapisan Spasial</h4>
                </div>

                <div className="flex flex-col">
                    {featureLayers.map((layer) => {
                        const isActive = activeLayers.includes(layer.id);
                        return (
                            <button
                                key={layer.id}
                                onClick={() => toggleLayer(layer.id)}
                                className="flex items-center justify-between px-4 py-3.5 border-b border-slate-100 bg-white hover:bg-slate-50 transition-colors w-full text-left rounded-none outline-none"
                            >
                                <div className="flex flex-col leading-none">
                                    <span className={`text-xs ${isActive ? 'font-bold text-slate-800' : 'font-semibold text-slate-500'}`}>
                                        {layer.label}
                                    </span>
                                    <span className="text-[9px] text-slate-400 font-medium mt-0.5">{layer.desc}</span>
                                </div>

                                {/* Toggle Switch */}
                                <div className={`relative inline-flex h-4 w-8 shrink-0 items-center rounded-full transition-colors duration-200 ease-in-out cursor-pointer
                                    ${isActive ? 'bg-[#10b981]' : 'bg-slate-200'}`}
                                >
                                    <span className={`inline-block h-3 w-3 transform rounded-full bg-white shadow-md transition duration-200 ease-in-out
                                        ${isActive ? 'translate-x-4.5' : 'translate-x-0.5'}`}
                                    />
                                </div>
                            </button>
                        );
                    })}
                </div>
            </div>

            {/* SEKSI 4: OPACITY SLIDER */}
            <div className="flex flex-col bg-white">
                <div className="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-b border-slate-200/80 text-slate-500">
                    <div className="flex items-center gap-2">
                        <Sliders size={13} className="text-[#2563eb]" />
                        <h4 className="text-[10px] font-black uppercase tracking-widest">Transparansi Marker</h4>
                    </div>
                    <span className="text-[9px] font-black text-[#2563eb] font-mono bg-[#2563eb]/10 px-2 py-0.5 border border-[#2563eb]/20">
                        {mapOpacity}%
                    </span>
                </div>

                <div className="px-5 py-4 space-y-2">
                    <input
                        type="range"
                        min="10"
                        max="100"
                        value={mapOpacity}
                        onChange={(e) => setMapOpacity(parseInt(e.target.value))}
                        className="w-full h-1.5 bg-slate-200 rounded-none appearance-none cursor-pointer accent-[#2563eb] outline-none"
                    />
                    <div className="flex justify-between text-[8px] font-black text-slate-400 uppercase tracking-wider select-none">
                        <span>Pudar</span>
                        <span>Solid</span>
                    </div>
                </div>
            </div>

        </div>
    );
}