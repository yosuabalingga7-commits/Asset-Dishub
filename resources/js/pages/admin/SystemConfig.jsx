import React, { useState, useEffect } from 'react';
import { useForm } from 'react-hook-form';
import api from '../../services/api';
import useGisUIStore from '../../store/useGisUIStore';
import { Settings, Save, MapPin, RefreshCw, AlertCircle, CheckCircle2 } from 'lucide-react';

export default function SystemConfig() {
    // Zustand store actions to sync changes instantly
    const setMapCenter = useGisUIStore((state) => state.setMapCenter);
    const setMapZoom = useGisUIStore((state) => state.setMapZoom);

    // Form States
    const [isLoading, setIsLoading] = useState(true);
    const [isSaving, setIsSaving] = useState(false);
    const [successMsg, setSuccessMsg] = useState('');
    const [errorMsg, setErrorMsg] = useState('');

    // react-hook-form setup
    const { register, handleSubmit, reset, setValue, formState: { errors } } = useForm({
        defaultValues: {
            latitude: -6.8431,
            longitude: 107.4912,
            zoom: 11
        }
    });

    // Fetch current settings on mount
    const fetchSettings = async () => {
        setIsLoading(true);
        setErrorMsg('');
        try {
            const res = await api.get('/v1/system/map-settings');
            reset({
                latitude: Number(res.latitude),
                longitude: Number(res.longitude),
                zoom: Number(res.zoom)
            });
        } catch (err) {
            console.error('[SystemConfig] Gagal memuat konfigurasi peta:', err);
            setErrorMsg('Gagal memuat konfigurasi peta dari server.');
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        fetchSettings();
    }, []);

    // Set form to Bandung Barat default coordinates
    const handleSetDefaultKbb = () => {
        setValue('latitude', -6.8431);
        setValue('longitude', 107.4912);
        setValue('zoom', 11);
    };

    // Form submission
    const onSubmit = async (data) => {
        setIsSaving(true);
        setSuccessMsg('');
        setErrorMsg('');

        try {
            const payload = {
                latitude: parseFloat(data.latitude),
                longitude: parseFloat(data.longitude),
                zoom: parseInt(data.zoom)
            };

            const res = await api.put('/v1/system/map-settings', payload);
            
            // 1. Show success message
            setSuccessMsg(res.message || 'Konfigurasi peta berhasil disimpan.');
            
            // 2. Dispatch new settings directly to Zustand global UI store
            setMapCenter([payload.latitude, payload.longitude]);
            setMapZoom(payload.zoom);

        } catch (err) {
            console.error('[SystemConfig] Gagal menyimpan konfigurasi peta:', err);
            setErrorMsg(err.message || 'Terjadi kesalahan saat menyimpan data.');
        } finally {
            setIsSaving(false);
        }
    };

    return (
        <div className="font-sans text-slate-800 flex flex-col gap-6 min-h-screen relative">
            {/* Header section */}
            <div className="border-b border-slate-300 pb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 className="text-xl font-bold tracking-tight uppercase text-slate-900">
                        Pengaturan & Konfigurasi Sistem
                    </h1>
                    <p className="text-xs text-slate-500 mt-1">
                        Sesuaikan pusat koordinat geografis default, rasio zoom dasar, dan parameter spasial GIS Kabupaten Bandung Barat.
                    </p>
                </div>
            </div>

            {/* Notification messages */}
            {successMsg && (
                <div className="bg-emerald-50 border border-emerald-400 p-4 rounded-none text-xs text-emerald-800 font-medium flex items-center gap-2">
                    <CheckCircle2 size={14} className="text-emerald-600" />
                    <span>{successMsg}</span>
                </div>
            )}
            {errorMsg && (
                <div className="bg-red-50 border border-red-400 p-4 rounded-none text-xs text-red-800 font-medium flex items-center gap-2">
                    <AlertCircle size={14} className="text-red-600" />
                    <span>{errorMsg}</span>
                </div>
            )}

            {isLoading ? (
                <div className="border border-slate-300 bg-white p-12 text-center text-slate-500 text-xs font-mono uppercase tracking-wider">
                    Memuat konfigurasi sistem...
                </div>
            ) : (
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Map config form card */}
                    <div className="lg:col-span-2 border border-slate-300 bg-white p-5 rounded-none shadow-none flex flex-col gap-4">
                        <div>
                            <h3 className="font-bold text-sm text-slate-900 uppercase tracking-tight flex items-center gap-2">
                                <MapPin size={16} className="text-blue-700" />
                                <span>Konfigurasi Pusat Peta Spasial (Default Viewport)</span>
                            </h3>
                            <p className="text-[10px] text-slate-400 mt-0.5">
                                Mengatur koordinat awal dan skala kedekatan lensa Leaflet saat pertama kali memuat kanvas monitoring spasial.
                            </p>
                        </div>

                        <form onSubmit={handleSubmit(onSubmit)} className="flex flex-col gap-4 mt-2">
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {/* Latitude */}
                                <div className="flex flex-col gap-1.5">
                                    <label className="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Garis Lintang (Latitude)</label>
                                    <input
                                        type="text"
                                        placeholder="Contoh: -6.8431"
                                        {...register('latitude', {
                                            required: 'Garis Lintang wajib diisi.',
                                            pattern: { value: /^-?[0-9]+(?:\.[0-9]+)?$/, message: 'Format Latitude tidak valid.' }
                                        })}
                                        className="w-full appearance-none rounded-none border border-slate-300 focus:border-slate-800 bg-white px-3 py-2 text-xs text-slate-800 focus:outline-none"
                                    />
                                    {errors.latitude && <span className="text-[10px] text-red-600 font-medium">{errors.latitude.message}</span>}
                                </div>

                                {/* Longitude */}
                                <div className="flex flex-col gap-1.5">
                                    <label className="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Garis Bujur (Longitude)</label>
                                    <input
                                        type="text"
                                        placeholder="Contoh: 107.4912"
                                        {...register('longitude', {
                                            required: 'Garis Bujur wajib diisi.',
                                            pattern: { value: /^-?[0-9]+(?:\.[0-9]+)?$/, message: 'Format Longitude tidak valid.' }
                                        })}
                                        className="w-full appearance-none rounded-none border border-slate-300 focus:border-slate-800 bg-white px-3 py-2 text-xs text-slate-800 focus:outline-none"
                                    />
                                    {errors.longitude && <span className="text-[10px] text-red-600 font-medium">{errors.longitude.message}</span>}
                                </div>
                            </div>

                            {/* Zoom Level */}
                            <div className="flex flex-col gap-1.5">
                                <label className="text-[10px] font-bold uppercase text-slate-500 tracking-wider">Tingkat Zoom Default (8 - 18)</label>
                                <input
                                    type="number"
                                    placeholder="Contoh: 11"
                                    {...register('zoom', {
                                        required: 'Tingkat zoom wajib diisi.',
                                        min: { value: 8, message: 'Minimal tingkat zoom adalah 8.' },
                                        max: { value: 18, message: 'Maksimal tingkat zoom adalah 18.' }
                                    })}
                                    className="w-full appearance-none rounded-none border border-slate-300 focus:border-slate-800 bg-white px-3 py-2 text-xs text-slate-800 focus:outline-none"
                                />
                                {errors.zoom && <span className="text-[10px] text-red-600 font-medium">{errors.zoom.message}</span>}
                            </div>

                            {/* Options and submit row */}
                            <div className="border-t border-slate-200 pt-4 flex flex-wrap gap-2 justify-between items-center">
                                <button
                                    type="button"
                                    onClick={handleSetDefaultKbb}
                                    className="border border-slate-300 hover:border-slate-400 active:bg-slate-50 text-slate-600 font-bold px-3 py-2 uppercase text-[10px] tracking-tight transition-colors rounded-none shadow-none"
                                >
                                    Gunakan Default KBB
                                </button>
                                
                                <button
                                    type="submit"
                                    disabled={isSaving}
                                    className="border border-slate-800 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-bold px-4 py-2 uppercase text-xs tracking-wider transition-colors rounded-none shadow-none flex items-center gap-2 justify-center"
                                >
                                    {isSaving ? <RefreshCw size={12} className="animate-spin" /> : <Save size={14} />}
                                    <span>{isSaving ? 'Menyimpan...' : 'Simpan Konfigurasi'}</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Explanatory side panel */}
                    <div className="border border-slate-300 bg-slate-50 p-5 rounded-none shadow-none flex flex-col gap-4">
                        <div>
                            <h4 className="font-bold text-xs uppercase tracking-wider text-slate-400 pb-1 border-b border-slate-200">
                                Panduan Koordinat
                            </h4>
                            <p className="text-xs text-slate-600 mt-2 leading-relaxed">
                                Pusat default peta digunakan sebagai acuan kamera peta GIS monitoring ketika pertamakali dimuat oleh user umum maupun petugas. 
                            </p>
                            <p className="text-xs text-slate-600 mt-2 leading-relaxed">
                                <strong>Default Kabupaten Bandung Barat:</strong>
                            </p>
                            <ul className="text-xs text-slate-600 list-disc pl-4 mt-1 space-y-1 font-mono">
                                <li>Latitude: -6.84310</li>
                                <li>Longitude: 107.49120</li>
                                <li>Zoom: 11</li>
                            </ul>
                            <p className="text-xs text-slate-600 mt-2 leading-relaxed">
                                Setelah disimpan, peta interaktif GIS akan memposisikan ulang kamera secara real-time tanpa perlu me-reload browser.
                            </p>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
