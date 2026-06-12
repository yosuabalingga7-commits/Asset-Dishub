import React, { useState } from 'react';
import { useForm } from 'react-hook-form';
import { Link, useNavigate } from 'react-router-dom';
import { MapContainer, TileLayer, Marker, useMapEvents } from 'react-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { ArrowLeft, MapPin, Camera, Send, CheckCircle, ShieldAlert } from 'lucide-react';
import { spatialService } from '../../services/spatialService';

// Configure Leaflet Default Marker Icon
import icon from 'leaflet/dist/images/marker-icon.png';
import iconShadow from 'leaflet/dist/images/marker-shadow.png';

let DefaultIcon = L.icon({
    iconUrl: icon,
    shadowUrl: iconShadow,
    iconSize: [25, 41],
    iconAnchor: [12, 41],
});
L.Marker.prototype.options.icon = DefaultIcon;

// Map click listener component
function MapClickEvents({ onMapClick }) {
    useMapEvents({
        click(e) {
            onMapClick(e.latlng.lat, e.latlng.lng);
        }
    });
    return null;
}

export default function ReportForm() {
    const navigate = useNavigate();
    const [mapCenter] = useState([-6.8431, 107.4912]); // Default center Bandung Barat
    const [selectedCoords, setSelectedCoords] = useState(null);
    const [nearestAssets, setNearestAssets] = useState([]);
    const [isLoadingAssets, setIsLoadingAssets] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [submitSuccess, setSubmitSuccess] = useState(false);
    const [ticketNumber, setTicketNumber] = useState('');
    const [submitError, setSubmitError] = useState('');

    const {
        register,
        handleSubmit,
        setValue,
        watch,
        formState: { errors }
    } = useForm({
        defaultValues: {
            nama_pelapor: '',
            kontak_pelapor: '',
            judul_laporan: '',
            kondisi_aset: 'rusak',
            alamat: '',
            asset_id: '',
            lat: '',
            lng: ''
        }
    });

    const watchAssetId = watch('asset_id');

    // Handle map click, query backend for nearest assets
    const handleMapClick = async (lat, lng) => {
        setSelectedCoords({ lat, lng });
        setValue('lat', lat);
        setValue('lng', lng);
        setNearestAssets([]);
        setValue('asset_id', '');
        setIsLoadingAssets(true);
        setSubmitError('');

        try {
            const data = await spatialService.fetchNearestAssets(lat, lng, 100);
            if (data && data.aset) {
                setNearestAssets(data.aset);
                if (data.aset.length > 0) {
                    // Pre-select the first nearest asset
                    setValue('asset_id', data.aset[0].id);
                }
            }
        } catch (error) {
            console.error('Gagal mengambil aset terdekat:', error);
            setSubmitError('Gagal mendeteksi aset terdekat di lokasi ini.');
        } finally {
            setIsLoadingAssets(false);
        }
    };

    // Handle form submit
    const onSubmit = async (data) => {
        if (!selectedCoords) {
            setSubmitError('Silakan pilih lokasi koordinat kerusakan pada peta terlebih dahulu.');
            return;
        }
        if (!data.asset_id) {
            setSubmitError('Silakan pilih salah satu aset terdekat yang terdeteksi.');
            return;
        }

        setIsSubmitting(true);
        setSubmitError('');

        try {
            const formData = new FormData();
            formData.append('nama_pelapor', data.nama_pelapor);
            formData.append('kontak_pelapor', data.kontak_pelapor);
            formData.append('judul_laporan', data.judul_laporan);
            formData.append('kondisi_aset', data.kondisi_aset);
            formData.append('alamat', data.alamat);
            formData.append('asset_id', data.asset_id);
            formData.append('lat', data.lat);
            formData.append('lng', data.lng);

            if (data.foto && data.foto[0]) {
                formData.append('foto', data.foto[0]);
            }

            const response = await spatialService.submitPublicReport(formData);

            if (response.duplicate) {
                // Asset is already reported, show success message with custom duplicate warning
                setTicketNumber('-');
                setSubmitSuccess(true);
            } else {
                setTicketNumber(response.data?.ticket_number || 'LP-SUCCESS');
                setSubmitSuccess(true);
            }
        } catch (error) {
            console.error('Gagal mengirim laporan:', error);
            setSubmitError(error.message || 'Terjadi kesalahan sistem saat menyimpan laporan.');
        } finally {
            setIsSubmitting(false);
        }
    };

    if (submitSuccess) {
        return (
            <div className="min-h-screen bg-slate-50 flex items-center justify-center p-4 font-sans">
                <div className="border border-slate-300 bg-white p-8 max-w-md w-full rounded-none shadow-none text-center flex flex-col gap-6">
                    <div className="text-blue-700 mx-auto">
                        <CheckCircle size={64} />
                    </div>
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight uppercase">Laporan Diterima</h2>
                        <p className="text-slate-600 text-sm mt-2 leading-relaxed">
                            Terima kasih atas kontribusi Anda! Laporan Anda telah kami terima dan akan segera ditindaklanjuti oleh tim teknis Dishub Kabupaten Bandung Barat.
                        </p>
                        {ticketNumber && ticketNumber !== '-' && (
                            <div className="mt-4 p-3 bg-slate-100 border border-slate-300 font-mono text-sm font-bold">
                                KODE TIKET: {ticketNumber}
                            </div>
                        )}
                    </div>
                    <div className="flex flex-col gap-3">
                        <button
                            onClick={() => {
                                setSubmitSuccess(false);
                                setTicketNumber('');
                                setSelectedCoords(null);
                                setNearestAssets([]);
                                setValue('nama_pelapor', '');
                                setValue('kontak_pelapor', '');
                                setValue('judul_laporan', '');
                                setValue('alamat', '');
                                setValue('asset_id', '');
                                setValue('lat', '');
                                setValue('lng', '');
                            }}
                            className="bg-blue-700 hover:bg-blue-800 active:bg-blue-900 text-white font-bold py-3 uppercase tracking-tight transition-colors text-sm rounded-none shadow-none"
                        >
                            Buat Laporan Baru
                        </button>
                        <Link
                            to="/app"
                            className="border border-slate-300 text-slate-800 hover:bg-slate-50 py-3 uppercase font-bold text-sm tracking-tight text-center rounded-none shadow-none"
                        >
                            Kembali ke Beranda
                        </Link>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-slate-50 text-slate-800 font-sans flex flex-col">
            {/* Header */}
            <header className="border-b border-slate-300 bg-white sticky top-0 z-50">
                <div className="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Link to="/app" className="hover:text-blue-700 transition-colors">
                            <ArrowLeft size={20} />
                        </Link>
                        <h1 className="text-lg font-bold tracking-tight uppercase">
                            Formulir Pengaduan Masyarakat
                        </h1>
                    </div>
                </div>
            </header>

            {/* Content Body */}
            <main className="flex-1 max-w-6xl mx-auto px-4 py-8 w-full grid grid-cols-1 lg:grid-cols-2 gap-8">
                {/* Left Column: Map Selector */}
                <div className="flex flex-col gap-4">
                    <div className="border border-slate-300 bg-white p-4 rounded-none shadow-none flex flex-col gap-2">
                        <h2 className="font-bold uppercase tracking-tight flex items-center gap-2 text-sm text-slate-900">
                            <MapPin size={16} className="text-blue-700" />
                            <span>Tentukan Titik Kerusakan</span>
                        </h2>
                        <p className="text-xs text-slate-500 leading-normal">
                            Klik pada peta di bawah ini persis di titik kerusakan aset Dishub berada. Sistem akan secara otomatis menyaring aset dalam radius 100m.
                        </p>
                    </div>

                    <div className="border border-slate-300 h-[380px] lg:h-[450px] relative rounded-none z-10">
                        <MapContainer
                            center={mapCenter}
                            zoom={13}
                            className="w-full h-full"
                        >
                            <TileLayer
                                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                attribution='&copy; OpenStreetMap'
                            />
                            <MapClickEvents onMapClick={handleMapClick} />
                            {selectedCoords && (
                                <Marker position={[selectedCoords.lat, selectedCoords.lng]} />
                            )}
                        </MapContainer>
                    </div>

                    {selectedCoords && (
                        <div className="border border-slate-300 bg-white p-3 font-mono text-xs text-slate-600 flex justify-between rounded-none">
                            <span>LAT: {selectedCoords.lat.toFixed(6)}</span>
                            <span>LNG: {selectedCoords.lng.toFixed(6)}</span>
                        </div>
                    )}
                </div>

                {/* Right Column: Form Inputs */}
                <div className="border border-slate-300 bg-white p-6 rounded-none shadow-none flex flex-col gap-6">
                    <h2 className="font-bold uppercase tracking-tight text-sm text-slate-900 pb-2 border-b border-slate-200">
                        Detail Pengaduan
                    </h2>

                    {submitError && (
                        <div className="border border-red-300 bg-red-50 text-red-700 p-3 text-xs leading-normal flex items-start gap-2 rounded-none">
                            <ShieldAlert size={16} className="flex-shrink-0 mt-0.5" />
                            <span>{submitError}</span>
                        </div>
                    )}

                    <form onSubmit={handleSubmit(onSubmit)} className="flex flex-col gap-4">
                        {/* Name */}
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-bold uppercase text-slate-700">Nama Pelapor *</label>
                            <input
                                type="text"
                                {...register('nama_pelapor', { required: 'Nama pelapor wajib diisi' })}
                                className={`border ${errors.nama_pelapor ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-sm focus:outline-none focus:border-blue-700 rounded-none bg-slate-50`}
                                placeholder="Masukkan nama lengkap Anda"
                            />
                            {errors.nama_pelapor && (
                                <span className="text-[11px] text-red-600">{errors.nama_pelapor.message}</span>
                            )}
                        </div>

                        {/* Contact */}
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-bold uppercase text-slate-700">No. WhatsApp *</label>
                            <input
                                type="text"
                                {...register('kontak_pelapor', {
                                    required: 'No. WhatsApp wajib diisi',
                                    pattern: {
                                        value: /^[0-9]+$/,
                                        message: 'No. WhatsApp harus berupa angka saja'
                                    }
                                })}
                                className={`border ${errors.kontak_pelapor ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-sm focus:outline-none focus:border-blue-700 rounded-none bg-slate-50`}
                                placeholder="Contoh: 08123456789"
                            />
                            {errors.kontak_pelapor && (
                                <span className="text-[11px] text-red-600">{errors.kontak_pelapor.message}</span>
                            )}
                        </div>

                        {/* Title */}
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-bold uppercase text-slate-700">Judul Pengaduan *</label>
                            <input
                                type="text"
                                {...register('judul_laporan', { required: 'Judul laporan wajib diisi' })}
                                className={`border ${errors.judul_laporan ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-sm focus:outline-none focus:border-blue-700 rounded-none bg-slate-50`}
                                placeholder="Contoh: Lampu PJU Padam di Jalan Raya"
                            />
                            {errors.judul_laporan && (
                                <span className="text-[11px] text-red-600">{errors.judul_laporan.message}</span>
                            )}
                        </div>

                        {/* Condition */}
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-bold uppercase text-slate-700">Kondisi Aset *</label>
                            <select
                                {...register('kondisi_aset')}
                                className="border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:border-blue-700 rounded-none bg-slate-50"
                            >
                                <option value="rusak">Rusak Fisik / Malfungsi</option>
                                <option value="hilang">Hilang / Dicuri</option>
                                <option value="pindah">Bergeser / Pindah Tempat</option>
                                <option value="fast">Lainnya / Fast Triage</option>
                                <option value="others">Kategori Lainnya</option>
                            </select>
                        </div>

                        {/* Alamat */}
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-bold uppercase text-slate-700">Alamat Lokasi Kerusakan *</label>
                            <textarea
                                {...register('alamat', { required: 'Alamat kerusakan wajib diisi' })}
                                rows={3}
                                className={`border ${errors.alamat ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-sm focus:outline-none focus:border-blue-700 rounded-none bg-slate-50 resize-none`}
                                placeholder="Jelaskan detail alamat patokan lokasi kerusakan"
                            />
                            {errors.alamat && (
                                <span className="text-[11px] text-red-600">{errors.alamat.message}</span>
                            )}
                        </div>

                        {/* Detect assets */}
                        <div className="flex flex-col gap-2 p-4 bg-slate-50 border border-slate-300 rounded-none">
                            <div className="flex justify-between items-center">
                                <label className="text-xs font-bold uppercase text-slate-700">Aset Dishub Terdeteksi (Radius 100m)</label>
                                {isLoadingAssets && (
                                    <span className="text-xs text-blue-700 animate-pulse">Scanning...</span>
                                )}
                            </div>
                            
                            {!selectedCoords ? (
                                <p className="text-xs text-slate-500 italic">Tentukan titik di peta terlebih dahulu.</p>
                            ) : nearestAssets.length === 0 && !isLoadingAssets ? (
                                <div className="border border-amber-300 bg-amber-50 text-amber-800 p-2.5 text-xs leading-normal rounded-none">
                                    Tidak ada aset terdaftar Dishub dalam radius 100m dari titik yang Anda tunjuk. Silakan geser titik pencarian.
                                </div>
                            ) : (
                                <select
                                    {...register('asset_id', { required: 'Aset wajib dipilih' })}
                                    className={`border ${errors.asset_id ? 'border-red-500' : 'border-slate-300'} w-full px-3 py-2 text-sm focus:outline-none focus:border-blue-700 rounded-none bg-white`}
                                >
                                    <option value="">-- Pilih Aset Terdeteksi --</option>
                                    {nearestAssets.map((asset) => (
                                        <option key={asset.id} value={asset.id}>
                                            [{asset.id_asset}] {asset.nama} - {asset.kategori} ({asset.status})
                                        </option>
                                    ))}
                                </select>
                            )}
                            {errors.asset_id && (
                                <span className="text-[11px] text-red-600">{errors.asset_id.message}</span>
                            )}
                        </div>

                        {/* Image Upload */}
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-bold uppercase text-slate-700 flex items-center gap-1.5">
                                <Camera size={14} />
                                <span>Foto Kondisi Aset *</span>
                            </label>
                            <input
                                type="file"
                                accept="image/*"
                                {...register('foto', { required: 'Foto kondisi wajib diunggah' })}
                                className={`border ${errors.foto ? 'border-red-500' : 'border-slate-300'} text-xs bg-slate-50 file:border-0 file:bg-slate-300 file:px-3 file:py-2 file:text-xs file:font-bold file:uppercase file:hover:bg-slate-400 file:cursor-pointer rounded-none`}
                            />
                            {errors.foto && (
                                <span className="text-[11px] text-red-600">{errors.foto.message}</span>
                            )}
                        </div>

                        {/* Submit button */}
                        <button
                            type="submit"
                            disabled={isSubmitting || isLoadingAssets}
                            className="bg-blue-700 hover:bg-blue-800 active:bg-blue-900 text-white font-bold py-3.5 uppercase tracking-tight transition-colors text-sm flex items-center justify-center gap-2 rounded-none shadow-none disabled:opacity-50 disabled:cursor-not-allowed mt-4"
                        >
                            <Send size={16} />
                            <span>{isSubmitting ? 'Mengirim Laporan...' : 'Kirim Laporan Pengaduan'}</span>
                        </button>
                    </form>
                </div>
            </main>
        </div>
    );
}
