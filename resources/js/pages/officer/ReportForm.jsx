import React, { useState, useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { Link, useNavigate } from 'react-router-dom';
import { MapContainer, TileLayer, Marker, useMapEvents } from 'react-leaflet';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { ArrowLeft, MapPin, Camera, Send, CheckCircle, ShieldAlert } from 'lucide-react';
import useAuthStore from '../../store/useAuthStore';
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
    const { user } = useAuthStore();
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
        reset,
        formState: { errors }
    } = useForm({
        defaultValues: {
            nama_pelapor: user?.name || '',
            nip: user?.nip || '',
            kontak_pelapor: user?.no_wa || '',
            judul_laporan: '',
            kondisi_aset: 'Rusak',
            deskripsi: '',
            asset_id: '',
            lat: '',
            lng: ''
        }
    });

    // Reset default values when user is loaded / changes
    useEffect(() => {
        if (user) {
            reset({
                nama_pelapor: user.name || '',
                nip: user.nip || '',
                kontak_pelapor: user.no_wa || '',
                judul_laporan: '',
                kondisi_aset: 'Rusak',
                deskripsi: '',
                asset_id: '',
                lat: '',
                lng: ''
            });
        }
    }, [user, reset]);

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
            setSubmitError('Silakan pilih lokasi koordinat pada peta terlebih dahulu.');
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
            formData.append('nip', data.nip);
            formData.append('kontak_pelapor', data.kontak_pelapor);
            formData.append('judul_laporan', data.judul_laporan);
            formData.append('kondisi_aset', data.kondisi_aset);
            formData.append('deskripsi', data.deskripsi);
            formData.append('asset_id', data.asset_id);
            formData.append('lat', data.lat);
            formData.append('lng', data.lng);

            if (data.foto && data.foto[0]) {
                formData.append('foto', data.foto[0]);
            }

            const response = await spatialService.submitOfficerReport(formData);

            setTicketNumber(response.data?.ticket_number || 'LP-P-SUCCESS');
            setSubmitSuccess(true);
        } catch (error) {
            console.error('Gagal mengirim laporan petugas:', error);
            setSubmitError(error.message || 'Terjadi kesalahan sistem saat menyimpan laporan teknis.');
        } finally {
            setIsSubmitting(false);
        }
    };

    if (submitSuccess) {
        return (
            <div className="p-6 font-sans flex items-center justify-center min-h-[80vh]">
                <div className="border border-slate-300 bg-white p-8 max-w-md w-full rounded-none shadow-none text-center flex flex-col gap-6">
                    <div className="text-blue-700 mx-auto">
                        <CheckCircle size={64} />
                    </div>
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight uppercase">Laporan Teknis Disimpan</h2>
                        <p className="text-slate-600 text-sm mt-2 leading-relaxed">
                            Laporan teknis petugas lapangan berhasil dicatat ke dalam database utama. Tiket maintenance otomatis telah dipicu.
                        </p>
                        {ticketNumber && (
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
                                reset({
                                    nama_pelapor: user?.name || '',
                                    nip: user?.nip || '',
                                    kontak_pelapor: user?.no_wa || '',
                                    judul_laporan: '',
                                    kondisi_aset: 'Rusak',
                                    deskripsi: '',
                                    asset_id: '',
                                    lat: '',
                                    lng: ''
                                });
                            }}
                            className="bg-blue-700 hover:bg-blue-800 active:bg-blue-900 text-white font-bold py-3 uppercase tracking-tight transition-colors text-sm rounded-none shadow-none"
                        >
                            Input Laporan Baru
                        </button>
                        <Link
                            to="/dashboard"
                            className="border border-slate-300 text-slate-800 hover:bg-slate-50 py-3 uppercase font-bold text-sm tracking-tight text-center rounded-none shadow-none"
                        >
                            Kembali ke Dashboard
                        </Link>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className="p-6 font-sans text-slate-800 flex flex-col gap-6">
            {/* Header Title */}
            <div className="border-b border-slate-300 pb-4 flex items-center justify-between">
                <div>
                    <h1 className="text-xl font-bold tracking-tight uppercase text-slate-900">
                        Input Laporan Teknis Petugas
                    </h1>
                    <p className="text-xs text-slate-500 mt-1">
                        Formulir perekaman kondisi lapangan khusus untuk petugas lapangan dan regu perbaikan Dinas Perhubungan KBB.
                    </p>
                </div>
            </div>

            {/* Layout Grid */}
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {/* Left Column: Map Selector */}
                <div className="flex flex-col gap-4">
                    <div className="border border-slate-300 bg-white p-4 rounded-none shadow-none flex flex-col gap-2">
                        <h2 className="font-bold uppercase tracking-tight flex items-center gap-2 text-sm text-slate-900">
                            <MapPin size={16} className="text-blue-700" />
                            <span>Tentukan Lokasi Aset</span>
                        </h2>
                        <p className="text-xs text-slate-500 leading-normal">
                            Klik pada peta untuk menetapkan titik koordinat persis dari peninjauan aset. Sistem akan mencari semua aset terdekat dalam jarak 100 meter.
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
                        Detail Peninjauan Teknis
                    </h2>

                    {submitError && (
                        <div className="border border-red-300 bg-red-50 text-red-700 p-3 text-xs leading-normal flex items-start gap-2 rounded-none">
                            <ShieldAlert size={16} className="flex-shrink-0 mt-0.5" />
                            <span>{submitError}</span>
                        </div>
                    )}

                    <form onSubmit={handleSubmit(onSubmit)} className="flex flex-col gap-4">
                        {/* Name & NIP row */}
                        <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div className="flex flex-col gap-1">
                                <label className="text-xs font-bold uppercase text-slate-700">Nama Petugas *</label>
                                <input
                                    type="text"
                                    {...register('nama_pelapor', { required: 'Nama petugas wajib diisi' })}
                                    className={`border ${errors.nama_pelapor ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-sm focus:outline-none focus:border-blue-700 rounded-none bg-slate-50`}
                                    placeholder="Nama lengkap petugas"
                                />
                                {errors.nama_pelapor && (
                                    <span className="text-[11px] text-red-600">{errors.nama_pelapor.message}</span>
                                )}
                            </div>

                            <div className="flex flex-col gap-1">
                                <label className="text-xs font-bold uppercase text-slate-700">NIP / No. Identitas *</label>
                                <input
                                    type="text"
                                    {...register('nip', { required: 'NIP wajib diisi' })}
                                    className={`border ${errors.nip ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-sm focus:outline-none focus:border-blue-700 rounded-none bg-slate-50`}
                                    placeholder="NIP Petugas"
                                />
                                {errors.nip && (
                                    <span className="text-[11px] text-red-600">{errors.nip.message}</span>
                                )}
                            </div>
                        </div>

                        {/* Contact */}
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-bold uppercase text-slate-700">No. WhatsApp Petugas *</label>
                            <input
                                type="text"
                                {...register('kontak_pelapor', { required: 'No. WhatsApp wajib diisi' })}
                                className={`border ${errors.kontak_pelapor ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-sm focus:outline-none focus:border-blue-700 rounded-none bg-slate-50`}
                                placeholder="Contoh: 08123456789"
                            />
                            {errors.kontak_pelapor && (
                                <span className="text-[11px] text-red-600">{errors.kontak_pelapor.message}</span>
                            )}
                        </div>

                        {/* Title */}
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-bold uppercase text-slate-700">Judul Temuan / Laporan *</label>
                            <input
                                type="text"
                                {...register('judul_laporan', { required: 'Judul temuan wajib diisi' })}
                                className={`border ${errors.judul_laporan ? 'border-red-500' : 'border-slate-300'} px-3 py-2 text-sm focus:outline-none focus:border-blue-700 rounded-none bg-slate-50`}
                                placeholder="Contoh: Kerusakan Lampu Merkuri PJU"
                            />
                            {errors.judul_laporan && (
                                <span className="text-[11px] text-red-600">{errors.judul_laporan.message}</span>
                            )}
                        </div>

                        {/* Condition */}
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-bold uppercase text-slate-700">Kondisi Fisik Aset *</label>
                            <select
                                {...register('kondisi_aset')}
                                className="border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:border-blue-700 rounded-none bg-slate-50"
                            >
                                <option value="Rusak">Rusak Ringan / Malfungsi</option>
                                <option value="Hilang/Dicuri">Hilang / Dicuri</option>
                                <option value="Pindah/Roboh">Roboh / Bergeser</option>
                                <option value="Lainnya">Kondisi Kritis Lainnya</option>
                            </select>
                        </div>

                        {/* Description */}
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-bold uppercase text-slate-700">Deskripsi Detail Temuan</label>
                            <textarea
                                {...register('deskripsi')}
                                rows={3}
                                className="border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:border-blue-700 rounded-none bg-slate-50 resize-none"
                                placeholder="Jelaskan secara terperinci kondisi kerusakan di lapangan (catatan teknis, dsb)"
                            />
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
                                    Tidak ada aset terdaftar Dishub dalam radius 100m dari titik ini. Silakan geser titik pencarian.
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
                                <span>Foto Bukti Fisik Aset *</span>
                            </label>
                            <input
                                type="file"
                                accept="image/*"
                                {...register('foto', { required: 'Foto bukti fisik wajib diunggah' })}
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
                            <span>{isSubmitting ? 'Menyimpan Laporan...' : 'Simpan Laporan Teknis'}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    );
}
