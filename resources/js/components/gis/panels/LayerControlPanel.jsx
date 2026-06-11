// resources/js/components/gis/panels/LayerControlPanel.jsx

import React, { useMemo } from 'react';
import { Layers, Sliders, Moon, Sun, Map, Check } from 'lucide-react';

// Store Zustand (UI Controller)
import useGisUIStore from '../../../store/useGisUIStore';

/**
 * ============================================================================
 * LayerControlPanel (Visual Controller Sub-Panel)
 * ============================================================================
 * Menyediakan antarmuka interaktif untuk mengubah peta dasar (Basemaps),
 * menyaring visibilitas layer spasial, dan mengontrol tingkat transparansi 
 * (*Opacity*) poligon/klaster peta.
 */

export default function LayerControlPanel() {
    const activeBaseMap = useGisUIStore((state) => state.activeBaseMap);
    const mapOpacity = useGisUIStore((state) => state.mapOpacity);
    const activeLayers = useGisUIStore((state) => state.activeLayers);

    const setActiveBaseMap = useGisUIStore((state) => state.setActiveBaseMap);
    const setMapOpacity = useGisUIStore((state) => state.setMapOpacity);
    const toggleLayer = useGisUIStore((state) => state.toggleLayer);

    // Katalog Peta Dasar (Basemaps)
    const baseMaps = useMemo(() => [
        { id: 'dark', label: 'Carto Dark', icon: Moon, desc: 'Paling kontras untuk visual emisi' },
        { id: 'satellite', label: 'Google Satelit', icon: Sun, desc: 'Citra foto udara asli' },
        { id: 'street', label: 'OSM Standard', icon: Map, desc: 'Navigasi jalan & administrasi' }
    ], []);

    // Katalog Lapisan Spasial (Feature Layers)
    const featureLayers = useMemo(() => [
        { id: 'assets', label: 'Aset Dishub KBB', desc: 'PJU, Rambu, & Traffic Light' },
        { id: 'reports', label: 'Pengaduan Aktif', desc: 'Laporan warga & petugas' },
        { id: 'boundaries', label: 'Batas Wilayah Desa', desc: 'Batas administratif KBB' }
    ], []);

    return (
        <div className="flex flex-col h-full bg-white pb-6 font-sans text-slate-800 text-left">

            {/* SEKSI 1: PILIHAN BASEMAP */}
            <div className="flex flex-col border-b border-slate-100">
                <div className="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border-b border-slate-200 text-slate-500">
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
                                    ${isActive ? 'bg-[#2563eb]/5' : 'bg-white'}`}
                            >
                                <div className="flex items-center gap-3">
                                    <div className={`p-1.5 rounded-none ${isActive ? 'text-[#2563eb]' : 'text-slate-400'}`}>
                                        <map.icon size={15} />
                                    </div>
                                    <div className="flex flex-col leading-none">
                                        <span className={`text-xs ${isActive ? 'font-bold text-slate-900' : 'font-semibold text-slate-700'}`}>
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

            {/* SEKSI 2: TOOGLE LAYERS */}
            <div className="flex flex-col border-b border-slate-100">
                <div className="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border-b border-slate-200 text-slate-500">
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
                                className="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-white hover:bg-slate-50 transition-colors w-full text-left rounded-none outline-none"
                            >
                                <div className="flex flex-col leading-none">
                                    <span className={`text-xs ${isActive ? 'font-bold text-slate-900' : 'font-semibold text-slate-700'}`}>
                                        {layer.label}
                                    </span>
                                    <span className="text-[9px] text-slate-400 font-medium mt-0.5">{layer.desc}</span>
                                </div>

                                {/* Custom UI Switch Toggle menggunakan utilitas Tailwind v4 */}
                                <div className={`relative inline-flex h-4 w-8 shrink-0 items-center rounded-full transition-colors duration-200 ease-in-out cursor-pointer
                                    ${isActive ? 'bg-[#00e5ff]' : 'bg-slate-200'}`}
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

            {/* SEKSI 3: OPACITY SLIDER */}
            <div className="flex flex-col bg-white">
                <div className="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-b border-slate-200 text-slate-500">
                    <div className="flex items-center gap-2">
                        <Sliders size={13} className="text-[#2563eb]" />
                        <h4 className="text-[10px] font-black uppercase tracking-widest">Transparansi</h4>
                    </div>
                    <span className="text-[9px] font-black text-[#2563eb] font-mono bg-[#2563eb]/5 px-2 py-0.5 border border-[#2563eb]/20">
                        {mapOpacity}%
                    </span>
                </div>

                <div className="px-5 py-4 space-y-2">
                    <input
                        type="range"
                        min="10" // Jangan biarkan 0 agar data tidak hilang total dari pandangan
                        max="100"
                        value={mapOpacity}
                        onChange={(e) => setMapOpacity(parseInt(e.target.value))}
                        className="w-full h-1.5 bg-slate-150 rounded-none appearance-none cursor-pointer accent-[#2563eb] outline-none"
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