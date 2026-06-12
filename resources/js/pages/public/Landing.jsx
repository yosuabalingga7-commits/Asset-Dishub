import React from 'react';
import { Link, Navigate } from 'react-router-dom';
import { LogIn, Zap, Globe, CheckCircle } from 'lucide-react';
import useAuthStore from '../../store/useAuthStore';

function resolveDefaultRoute(role) {
    switch (role) {
        case 'kadis':             return '/telemetry/executive';
        case 'seksi':             return '/workflow/tasks';
        case 'petugas_lapangan':  return '/workflow/tasks';
        case 'admin':
        default:                  return '/dashboard';
    }
}

export default function Landing() {
    const { isAuthenticated, user } = useAuthStore();

    if (isAuthenticated) {
        return <Navigate to={resolveDefaultRoute(user?.role)} replace />;
    }

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900 font-sans flex flex-col">
            {/* Header / Navbar */}
            <header className="border-b border-slate-300 bg-white sticky top-0 z-50">
                <div className="max-w-7xl mx-auto px-8 py-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div className="flex items-center gap-3">
                        <div className="bg-blue-700 text-white px-3 py-2 text-xl font-bold tracking-tight">
                            LINTAS.
                        </div>
                    </div>
                    
                    <div className="flex items-center gap-6">
                        <a href="#tentang" className="text-sm font-bold uppercase hover:text-blue-700 hidden sm:block tracking-widest">Tentang</a>
                        <a href="#kegiatan" className="text-sm font-bold uppercase hover:text-blue-700 hidden sm:block tracking-widest">Galeri</a>
                        <Link
                            to="/login"
                            className="flex items-center gap-2 border border-slate-300 px-5 py-2.5 hover:bg-slate-50 active:bg-slate-100 transition-colors rounded-none shadow-none text-sm font-bold uppercase tracking-widest"
                        >
                            <LogIn size={16} />
                            <span>Portal Petugas</span>
                        </Link>
                    </div>
                </div>
            </header>

            {/* Hero Section */}
            <section className="bg-white border-b border-slate-300 py-20 lg:py-32">
                <div className="max-w-7xl mx-auto px-8 flex flex-col lg:flex-row items-center gap-16">
                    <div className="lg:w-3/5 space-y-8">
                        <div className="inline-block bg-slate-100 text-blue-700 px-4 py-1 text-xs font-bold tracking-[0.2em] uppercase border border-slate-300">
                            Dinas Perhubungan KBB
                        </div>
                        <h1 className="text-5xl lg:text-7xl font-black text-slate-900 tracking-tighter leading-none">
                            Sistem Layanan <br/> Integrasi Aset <br/>
                            <span className="text-blue-700">Jalan & Transportasi.</span>
                        </h1>
                        <p className="text-lg text-slate-600 leading-relaxed font-medium max-w-2xl">
                            Platform digital untuk monitoring, pendataan, dan pelaporan kondisi aset jalan di wilayah Kabupaten Bandung Barat secara real-time.
                        </p>
                        <div className="flex flex-col sm:flex-row gap-4 pt-4">
                            <Link
                                to="/lapor"
                                className="bg-blue-700 text-white px-8 py-4 font-bold tracking-widest uppercase hover:bg-blue-800 transition-colors rounded-none text-center border border-blue-700"
                            >
                                Buat Laporan
                            </Link>
                            <a
                                href="#tentang"
                                className="bg-white text-slate-900 border border-slate-300 px-8 py-4 font-bold tracking-widest uppercase hover:bg-slate-50 transition-colors rounded-none text-center"
                            >
                                Pelajari Alur
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            {/* Features */}
            <section className="border-b border-slate-300 bg-white">
                <div className="max-w-7xl mx-auto flex flex-col md:flex-row divide-y md:divide-y-0 md:divide-x divide-slate-300">
                    <div className="p-10 text-center hover:bg-slate-50 transition-colors flex-1">
                        <div className="text-3xl font-black text-blue-700 mb-2 uppercase tracking-tighter">Responsif</div>
                        <div className="text-xs font-bold text-slate-400 uppercase tracking-[0.2em]">Penanganan Cepat</div>
                    </div>
                    <div className="p-10 text-center hover:bg-slate-50 transition-colors flex-1">
                        <div className="text-3xl font-black text-emerald-600 mb-2 uppercase tracking-tighter">Terintegrasi</div>
                        <div className="text-xs font-bold text-slate-400 uppercase tracking-[0.2em]">Data Aset Real-time</div>
                    </div>
                    <div className="p-10 text-center hover:bg-slate-50 transition-colors flex-1">
                        <div className="text-3xl font-black text-orange-500 mb-2 uppercase tracking-tighter">Transparan</div>
                        <div className="text-xs font-bold text-slate-400 uppercase tracking-[0.2em]">Proses Terpantau</div>
                    </div>
                </div>
            </section>

            {/* Tentang Section */}
            <section id="tentang" className="py-24 bg-white border-b border-slate-300">
                <div className="max-w-7xl mx-auto px-8">
                    <div className="mb-20">
                        <h2 className="text-4xl lg:text-6xl font-black text-slate-900 tracking-tighter leading-none mb-8">
                            Sinergi <br/> Pendataan & Penataan.
                        </h2>
                        <p className="text-lg text-slate-600 font-medium leading-relaxed border-l-4 border-blue-700 pl-6 max-w-3xl">
                            LINTAS dirancang untuk mempermudah koordinasi pengelolaan aset dan pelaporan insiden infrastruktur demi kenyamanan masyarakat Kabupaten Bandung Barat.
                        </p>
                    </div>

                    <div className="grid md:grid-cols-3 gap-8">
                        <div className="p-8 border border-slate-300 hover:bg-slate-50 transition-colors">
                            <div className="w-16 h-16 bg-blue-700 text-white flex items-center justify-center mb-8 border border-blue-800">
                                <Zap size={32} />
                            </div>
                            <h3 className="text-xl font-bold text-slate-900 mb-4 uppercase tracking-tight">Lapor Cepat</h3>
                            <p className="text-slate-600 leading-relaxed text-sm">Lampirkan foto dan lokasi GPS untuk pelaporan kerusakan fasilitas jalan yang akurat.</p>
                        </div>

                        <div className="p-8 border border-slate-300 hover:bg-slate-50 transition-colors">
                            <div className="w-16 h-16 bg-emerald-600 text-white flex items-center justify-center mb-8 border border-emerald-700">
                                <CheckCircle size={32} />
                            </div>
                            <h3 className="text-xl font-bold text-slate-900 mb-4 uppercase tracking-tight">Verifikasi</h3>
                            <p className="text-slate-600 leading-relaxed text-sm">Setiap laporan akan diverifikasi oleh tim teknis Dishub untuk penanganan lebih lanjut.</p>
                        </div>

                        <div className="p-8 border border-slate-300 hover:bg-slate-50 transition-colors">
                            <div className="w-16 h-16 bg-orange-500 text-white flex items-center justify-center mb-8 border border-orange-600">
                                <Globe size={32} />
                            </div>
                            <h3 className="text-xl font-bold text-slate-900 mb-4 uppercase tracking-tight">Selesai</h3>
                            <p className="text-slate-600 leading-relaxed text-sm">Proses perbaikan aset dilakukan dengan standar yang jelas dan hasil yang terdokumentasi.</p>
                        </div>
                    </div>
                </div>
            </section>

            {/* Galeri Section */}
            <section id="kegiatan" className="py-24 bg-slate-50">
                <div className="max-w-7xl mx-auto px-8">
                    <div className="text-center mb-16">
                        <span className="text-blue-700 font-bold text-xs uppercase tracking-[0.2em] bg-white border border-slate-300 px-4 py-1.5 inline-block mb-6">Galeri Kegiatan</span>
                        <h2 className="text-3xl lg:text-5xl font-black text-slate-900 tracking-tighter mb-6 uppercase">
                            Dokumentasi <span className="text-blue-700">Dishub KBB</span>
                        </h2>
                        <p className="text-slate-600 text-base max-w-2xl mx-auto">
                            Wujud nyata sinergi dan penanganan aset infrastruktur untuk masyarakat Bandung Barat.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div className="border border-slate-300 bg-white">
                            <div className="h-48 bg-slate-200 overflow-hidden relative border-b border-slate-300">
                                <img src="/img/kegiatan_dishub_kbb1.png" alt="Inspeksi Jalan" className="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500" onError={(e) => e.target.style.display = 'none'} />
                            </div>
                            <div className="p-5">
                                <h4 className="font-bold text-slate-900 uppercase text-sm tracking-tight">Inspeksi Jalan & Jembatan</h4>
                                <p className="text-slate-500 text-xs mt-2 leading-relaxed">Tim teknis melakukan pengecekan rutin infrastruktur.</p>
                            </div>
                        </div>
                        <div className="border border-slate-300 bg-white">
                            <div className="h-48 bg-slate-200 overflow-hidden relative border-b border-slate-300">
                                <img src="/img/kegiatan_dishub_kbb2.png" alt="Koordinasi Lintas Sektor" className="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500" onError={(e) => e.target.style.display = 'none'} />
                            </div>
                            <div className="p-5">
                                <h4 className="font-bold text-slate-900 uppercase text-sm tracking-tight">Koordinasi Lintas Sektor</h4>
                                <p className="text-slate-500 text-xs mt-2 leading-relaxed">Sinergi percepatan penanganan aset daerah.</p>
                            </div>
                        </div>
                        <div className="border border-slate-300 bg-white">
                            <div className="h-48 bg-slate-200 overflow-hidden relative border-b border-slate-300">
                                <img src="/img/kegiatan_dishub_kbb3.png" alt="Sosialisasi LINTAS" className="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500" onError={(e) => e.target.style.display = 'none'} />
                            </div>
                            <div className="p-5">
                                <h4 className="font-bold text-slate-900 uppercase text-sm tracking-tight">Sosialisasi LINTAS</h4>
                                <p className="text-slate-500 text-xs mt-2 leading-relaxed">Edukasi publik tentang layanan pelaporan aset.</p>
                            </div>
                        </div>
                        <div className="border border-slate-300 bg-white">
                            <div className="h-48 bg-slate-200 overflow-hidden relative border-b border-slate-300">
                                <img src="/img/kegiatan_dishub_kbb4.png" alt="Monitoring & Evaluasi" className="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-500" onError={(e) => e.target.style.display = 'none'} />
                            </div>
                            <div className="p-5">
                                <h4 className="font-bold text-slate-900 uppercase text-sm tracking-tight">Monitoring & Evaluasi</h4>
                                <p className="text-slate-500 text-xs mt-2 leading-relaxed">Evaluasi berkala untuk peningkatan kualitas layanan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Footer */}
            <footer className="border-t border-slate-300 bg-white py-8">
                <div className="max-w-7xl mx-auto px-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-slate-500 uppercase font-bold tracking-wider">
                    <p>© {new Date().getFullYear()} Dinas Perhubungan KBB.</p>
                    <p>LINTAS v2.0-SPA</p>
                </div>
            </footer>
        </div>
    );
}
