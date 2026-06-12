import React, { useState, useEffect } from 'react';
import api from '../../services/api';
import { ResponsiveContainer, BarChart, Bar, XAxis, YAxis, Tooltip, Cell, PieChart, Pie } from 'recharts';
import { Database, ClipboardList, Users, ShieldAlert, Award, RefreshCw, Layers, Layout, Clock } from 'lucide-react';

function MetricCard({ title, value, subtitle, icon: Icon }) {
    return (
        <div className="bg-white border border-slate-300 p-5 rounded-none shadow-none flex flex-col gap-2">
            <div className="flex justify-between items-center text-slate-400">
                <span className="text-[10px] font-bold uppercase tracking-wider text-slate-500">{title}</span>
                {Icon && <Icon size={16} className="text-blue-700" />}
            </div>
            <div className="text-3xl tracking-tighter font-black text-slate-900 font-mono">
                {value}
            </div>
            {subtitle && (
                <span className="text-[9px] text-slate-400 font-mono uppercase tracking-wider">
                    {subtitle}
                </span>
            )}
        </div>
    );
}

export default function Dashboard() {
    const [summaryData, setSummaryData] = useState({
        total_assets: 0,
        total_reports: 0,
        active_officers: 0,
        ongoing_tasks: 0,
        completed_tasks: 0
    });
    
    const [chartData, setChartData] = useState({
        categories: [],
        status: []
    });

    const [isLoading, setIsLoading] = useState(true);
    const [isFetching, setIsFetching] = useState(false);
    const [autoRefresh, setAutoRefresh] = useState(false);
    const [lastRefreshed, setLastRefreshed] = useState('');

    const fetchData = async () => {
        setIsFetching(true);
        try {
            const [summaryRes, chartsRes] = await Promise.all([
                api.get('/v1/telemetry/summary'),
                api.get('/v1/telemetry/charts')
            ]);
            setSummaryData(summaryRes);
            setChartData(chartsRes);
            setLastRefreshed(new Date().toLocaleTimeString());
        } catch (error) {
            console.error('[TelemetryDashboard] Gagal memuat data telemetri:', error);
        } finally {
            setIsLoading(false);
            setIsFetching(false);
        }
    };

    // Initial load
    useEffect(() => {
        fetchData();
    }, []);

    // Auto-refresh handler
    useEffect(() => {
        if (!autoRefresh) return;

        const timer = setInterval(() => {
            fetchData();
        }, 15000); // Auto refresh every 15 seconds for dashboard precision

        return () => clearInterval(timer);
    }, [autoRefresh]);

    return (
        <div className="font-sans text-slate-800 flex flex-col gap-6 relative min-h-screen">
            {/* Title / Controls Header */}
            <div className="border-b border-slate-300 pb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 className="text-xl font-bold tracking-tight uppercase text-slate-900">
                        Dashboard Eksekutif
                    </h1>
                    <p className="text-xs text-slate-500 mt-1">
                        Informasi spasial, tingkat kesehatan aset, dan kinerja regu lapangan Dinas Perhubungan KBB.
                    </p>
                </div>

                <div className="flex flex-wrap items-center gap-4">
                    {/* Auto-Refresh Toggle */}
                    <label className="inline-flex items-center gap-2 cursor-pointer bg-white border border-slate-300 px-3 py-1.5 text-xs font-bold uppercase select-none rounded-none">
                        <input
                            type="checkbox"
                            checked={autoRefresh}
                            onChange={(e) => setAutoRefresh(e.target.checked)}
                            className="accent-blue-700 rounded-none cursor-pointer"
                        />
                        <span>Auto Refresh (15s)</span>
                    </label>

                    {/* Manual Refresh */}
                    <button
                        onClick={fetchData}
                        disabled={isFetching}
                        className="inline-flex items-center gap-1.5 border border-slate-300 bg-white hover:bg-slate-50 hover:border-slate-400 active:bg-slate-100 font-bold px-3 py-2 uppercase text-xs tracking-tight transition-colors rounded-none shadow-none"
                    >
                        <RefreshCw size={12} className={isFetching ? 'animate-spin' : ''} />
                        <span>Refresh</span>
                    </button>
                    
                    {lastRefreshed && (
                        <div className="text-[10px] text-slate-400 font-mono hidden md:flex items-center gap-1">
                            <Clock size={12} />
                            <span>Update terakhir: {lastRefreshed}</span>
                        </div>
                    )}
                </div>
            </div>

            {isLoading ? (
                <div className="p-12 text-center text-slate-500 font-medium border border-slate-300 bg-white rounded-none shadow-none">
                    Memuat data dashboard eksekutif...
                </div>
            ) : (
                <>
                    {/* KPI Metrics row */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                        <MetricCard
                            title="Total Aset Fisik"
                            value={summaryData.total_assets}
                            subtitle="Semua kategori di KBB"
                            icon={Layers}
                        />
                        <MetricCard
                            title="Aduan Warga"
                            value={summaryData.total_reports}
                            subtitle="Total laporan masuk"
                            icon={ClipboardList}
                        />
                        <MetricCard
                            title="Kinerja Regu"
                            value={summaryData.active_officers}
                            subtitle="Petugas lapangan aktif"
                            icon={Users}
                        />
                        <MetricCard
                            title="SPK Aktif"
                            value={summaryData.ongoing_tasks}
                            subtitle="Tiket dalam perbaikan"
                            icon={RefreshCw}
                        />
                        <MetricCard
                            title="SPK Rampung"
                            value={summaryData.completed_tasks}
                            subtitle="Tiket selesai diperbaiki"
                            icon={Award}
                        />
                    </div>

                    {/* Visual Charts Grid */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* Categories distribution bar chart */}
                        <div className="border border-slate-300 bg-white p-5 rounded-none shadow-none flex flex-col gap-4">
                            <div>
                                <h3 className="font-bold text-sm text-slate-900 uppercase tracking-tight">
                                    Distribusi Aset berdasarkan Kategori
                                </h3>
                                <p className="text-[10px] text-slate-400 mt-0.5">
                                    Visualisasi sebaran jenis infrastruktur Dishub di Kabupaten Bandung Barat.
                                </p>
                            </div>
                            
                            <div className="h-64 mt-2">
                                {chartData.categories.length === 0 ? (
                                    <div className="h-full flex items-center justify-center text-xs text-slate-400 italic">
                                        Data kategori kosong
                                    </div>
                                ) : (
                                    <ResponsiveContainer width="100%" height="100%">
                                        <BarChart data={chartData.categories} margin={{ top: 10, right: 10, left: -25, bottom: 0 }}>
                                            <XAxis
                                                dataKey="name"
                                                tickLine={false}
                                                axisLine={{ stroke: '#cbd5e1' }}
                                                tick={{ fontSize: 9, fill: '#64748b', fontFamily: 'Roboto', fontWeight: 'bold' }}
                                            />
                                            <YAxis
                                                tickLine={false}
                                                axisLine={{ stroke: '#cbd5e1' }}
                                                tick={{ fontSize: 9, fill: '#64748b', fontFamily: 'Roboto' }}
                                            />
                                            <Tooltip
                                                cursor={{ fill: 'rgba(241, 245, 249, 0.4)' }}
                                                contentStyle={{ borderRadius: 0, border: '1px solid #cbd5e1', fontSize: 10, fontFamily: 'Roboto' }}
                                            />
                                            <Bar dataKey="value" fill="#1d4ed8" radius={0}>
                                                {chartData.categories.map((entry, index) => (
                                                    <Cell key={`cell-${index}`} fill="#1d4ed8" />
                                                ))}
                                            </Bar>
                                        </BarChart>
                                    </ResponsiveContainer>
                                )}
                            </div>
                        </div>

                        {/* Status condition donut/pie chart */}
                        <div className="border border-slate-300 bg-white p-5 rounded-none shadow-none flex flex-col gap-4">
                            <div>
                                <h3 className="font-bold text-sm text-slate-900 uppercase tracking-tight">
                                    Kondisi Fisik / Tingkat Kesehatan Aset
                                </h3>
                                <p className="text-[10px] text-slate-400 mt-0.5">
                                    Persentase status kelayakan operasional infrastruktur di lapangan.
                                </p>
                            </div>

                            <div className="h-64 mt-2 flex flex-col sm:flex-row items-center justify-center gap-6">
                                <div className="flex-1 w-full h-full relative">
                                    {chartData.status.length === 0 ? (
                                        <div className="h-full flex items-center justify-center text-xs text-slate-400 italic">
                                            Data kondisi kosong
                                        </div>
                                    ) : (
                                        <ResponsiveContainer width="100%" height="100%">
                                            <PieChart>
                                                <Pie
                                                    data={chartData.status}
                                                    cx="50%"
                                                    cy="50%"
                                                    innerRadius={55}
                                                    outerRadius={75}
                                                    paddingAngle={3}
                                                    dataKey="value"
                                                    label={({ percent }) => `${(percent * 100).toFixed(0)}%`}
                                                    labelLine={true}
                                                >
                                                    {chartData.status.map((entry, index) => {
                                                        const name = (entry.name || '').toLowerCase();
                                                        let color = '#64748b'; // default slate-500
                                                        if (name === 'baik') {
                                                            color = '#15803d'; // green-700
                                                        } else if (name === 'rusak') {
                                                            color = '#b45309'; // amber-700
                                                        } else if (name === 'kritis') {
                                                            color = '#b91c1c'; // red-700
                                                        } else if (name === 'proses perbaikan' || name === 'proses') {
                                                            color = '#1d4ed8'; // blue-700
                                                        }
                                                        return <Cell key={`cell-${index}`} fill={color} />;
                                                    })}
                                                </Pie>
                                                <Tooltip contentStyle={{ borderRadius: 0, border: '1px solid #cbd5e1', fontSize: 10, fontFamily: 'Roboto' }} />
                                            </PieChart>
                                        </ResponsiveContainer>
                                    )}
                                </div>

                                {/* Custom Legend Panel */}
                                <div className="border border-slate-200 bg-slate-50 p-4 w-full sm:w-44 flex flex-col gap-2 rounded-none text-[10px]">
                                    <span className="font-bold uppercase tracking-wider text-slate-400 pb-1 border-b border-slate-200">
                                        Keterangan Status
                                    </span>
                                    {chartData.status.map((entry, idx) => {
                                        const name = (entry.name || '').toLowerCase();
                                        let colorClass = 'bg-slate-500';
                                        if (name === 'baik') colorClass = 'bg-[#15803d]';
                                        if (name === 'rusak') colorClass = 'bg-[#b45309]';
                                        if (name === 'kritis') colorClass = 'bg-[#b91c1c]';
                                        if (name === 'proses perbaikan' || name === 'proses') colorClass = 'bg-[#1d4ed8]';

                                        return (
                                            <div key={idx} className="flex items-center justify-between">
                                                <div className="flex items-center gap-1.5">
                                                    <span className={`w-2 h-2 rounded-none ${colorClass}`} />
                                                    <span className="capitalize">{entry.name}</span>
                                                </div>
                                                <span className="font-mono font-bold">{entry.value}</span>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}
