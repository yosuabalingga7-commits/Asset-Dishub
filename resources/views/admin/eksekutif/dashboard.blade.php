@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    * { font-family: 'Inter', sans-serif; }
    body { 
        font-family: 'Inter', sans-serif; 
        background-color: #0f172a;
    }
    .card-shadow { 
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
    .stat-card:hover { 
        transform: translateY(-4px); 
        transition: all 0.3s ease;
        box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.2);
    }
    .stat-card { 
        transition: all 0.3s ease;
    }
    .soft-shadow {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    /* Tambahan styling untuk chart spacing */
    .chart-kategori-wrapper {
        padding-bottom: 20px;
    }
    canvas#chartKategori {
        margin-bottom: 8px;
    }
    /* Styling untuk text label angka sumbu X agar tidak menabrak */
    .chartjs-render-monitor {
        font-weight: 600 !important;
    }
</style>

<div class="p-6 min-h-screen" style="background-color: #0f172a;">
    {{-- Header Section --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Pemantauan Eksekutif</h1>
            <p class="text-slate-400 font-medium text-sm">Dashboard Pemantauan Aset LINTAS - Dinas Perhubungan Kab. Bandung Barat</p>
        </div>
        <div class="flex items-center gap-3 bg-white/10 backdrop-blur-sm p-3 rounded-2xl card-shadow border border-slate-700">
            <div class="flex h-3 w-3 relative">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </div>
            <span class="text-sm font-bold text-white tracking-wider">Sistem Live Update</span>
        </div>
    </div>

    {{-- Main Statistics - 5 Kolom Sejajar --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        {{-- Card 1: Total Aset --}}
        <div class="bg-white p-6 rounded-3xl card-shadow stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 tracking-widest">Total Seluruh Aset</p>
                    <h3 class="text-4xl font-black text-slate-800 mt-2">{{ number_format($total_aset) }}</h3>
                </div>
                <span class="p-3 bg-blue-50 text-blue-600 rounded-2xl"><i class="fas fa-box text-xl"></i></span>
            </div>
            <p class="text-[10px] text-slate-400 mt-4 font-bold tracking-wide">Tersebar di seluruh wilayah KBB</p>
        </div>

        {{-- Card 2: Pengaduan Masuk --}}
        <div class="bg-white p-6 rounded-3xl card-shadow stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 tracking-widest">Pengaduan Masuk</p>
                    <h3 class="text-4xl font-black text-red-600 mt-2">{{ number_format($total_pengaduan) }}</h3>
                </div>
                <span class="p-3 bg-red-50 text-red-600 rounded-2xl"><i class="fas fa-exclamation-triangle text-xl"></i></span>
            </div>
            <div class="mt-4 flex gap-2">
                <span class="text-[10px] font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded-md tracking-wide">Masyarakat: {{ $laporan_masyarakat }}</span>
                <span class="text-[10px] font-bold bg-slate-100 text-slate-600 px-2 py-1 rounded-md tracking-wide">Petugas: {{ $laporan_petugas }}</span>
            </div>
        </div>

        {{-- Card 3: Proses Perbaikan --}}
        <div class="bg-white p-6 rounded-3xl card-shadow stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 tracking-widest">Proses Perbaikan</p>
                    <h3 class="text-4xl font-black text-orange-500 mt-2">{{ number_format($perbaikan_proses) }}</h3>
                </div>
                <span class="p-3 bg-orange-50 text-orange-600 rounded-2xl"><i class="fas fa-tools text-xl"></i></span>
            </div>
        </div>

        {{-- Card 4: Selesai Ditangani --}}
        <div class="bg-white p-6 rounded-3xl card-shadow stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 tracking-widest">Selesai Ditangani</p>
                    <h3 class="text-4xl font-black text-green-600 mt-2">{{ number_format($total_selesai) }}</h3>
                </div>
                <span class="p-3 bg-green-50 text-green-600 rounded-2xl"><i class="fas fa-check-circle text-xl"></i></span>
            </div>
        </div>

        {{-- Card 5: Petugas Lapangan --}}
        <div class="bg-white p-6 rounded-3xl card-shadow stat-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 tracking-widest">Petugas Lapangan</p>
                    <h3 class="text-4xl font-black text-blue-600 mt-2">{{ number_format($total_petugas) }}</h3>
                </div>
                <span class="p-3 bg-blue-50 text-blue-600 rounded-2xl"><i class="fas fa-user-shield text-xl"></i></span>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kondisi Aset (Donut) --}}
        <div class="bg-white p-8 rounded-3xl card-shadow">
            <h4 class="font-black text-slate-800 text-lg mb-6 flex items-center gap-2">
                <span class="w-1.5 h-6 bg-blue-600 rounded-full"></span> Kondisi Aset Saat Ini
            </h4>
            <div class="relative h-[250px] mb-8">
                <canvas id="chartKondisi"></canvas>
            </div>
            <div class="grid grid-cols-1 gap-3">
                @php
                    $all_statuses = [
                        'Baik' => ['color' => 'bg-green-500', 'hex' => '#28a745'],
                        'Rusak' => ['color' => 'bg-yellow-500', 'hex' => '#ffc107'],
                        'Kritis' => ['color' => 'bg-red-500', 'hex' => '#dc3545'],
                        'Proses Perbaikan' => ['color' => 'bg-cyan-500', 'hex' => '#17a2b8']
                    ];
                @endphp
                
                @foreach($all_statuses as $statusName => $style)
                    @php 
                        $dataItem = $status_aset->firstWhere('status', $statusName);
                        $count = $dataItem ? $dataItem->total : 0;
                        $persen = ($total_aset > 0) ? round(($count / $total_aset) * 100, 1) : 0;
                    @endphp
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl border border-slate-100 transition-all hover:bg-white hover:shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-3 h-3 rounded-full {{ $style['color'] }}"></span>
                            <span class="text-sm font-bold text-slate-700">{{ $statusName }}</span>
                        </div>
                        <span class="text-xs font-black text-slate-500 tracking-wide">{{ $count }} Unit ({{ $persen }}%)</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Sebaran Kategori (Bar Chart) --}}
        <div class="bg-white p-8 rounded-3xl card-shadow lg:col-span-2">
            <div class="flex items-center justify-between mb-8">
                <h4 class="font-black text-slate-800 text-lg flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span> Sebaran Kategori Aset
                </h4>
            </div>

            {{-- Wrapper dengan padding-bottom untuk chart spacing fix --}}
            <div class="chart-kategori-wrapper h-[300px] mb-4">
                <canvas id="chartKategori"></canvas>
            </div>

            {{-- Grid dengan gap-4 dan padding-x konsisten --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-1">
                @php
                    // Daftar semua kategori yang ingin ditampilkan (urutan tetap)
                    $allKategori = [
                        'Penerangan Jalan Umum (PJU)' => ['icon' => 'fa-lightbulb', 'secondIcon' => null],
                        'Perlengkapan Jalan' => ['icon' => 'fa-triangle-exclamation', 'secondIcon' => null],
                        'Fasilitas Lalu Lintas' => ['icon' => 'fa-traffic-light', 'secondIcon' => null],
                        'Pengendalian dan Pengawasan' => ['icon' => 'fa-camera', 'secondIcon' => 'fa-chart-line'],
                        'Prasarana Transportasi' => ['icon' => 'fa-bus', 'secondIcon' => null],
                    ];
                    
                    // Mapping data dari database ke array asosiatif
                    $kategoriData = [];
                    foreach($kategori_stats as $kat) {
                        $kategoriData[$kat->kategori] = $kat->jumlah_jenis;
                    }
                @endphp
                
                @foreach($allKategori as $namaKategori => $icons)
                    @php
                        $jumlahJenis = $kategoriData[$namaKategori] ?? 0;
                    @endphp
                    <div class="p-4 bg-slate-800 rounded-2xl flex items-center justify-between group transition-all border border-slate-700 hover:border-slate-600">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-slate-700 rounded-xl flex items-center justify-center gap-1.5 text-slate-300 group-hover:scale-110 transition-transform">
                                @if($namaKategori == 'Pengendalian dan Pengawasan')
                                    <i class="fas {{ $icons['icon'] }}"></i>
                                    <i class="fas {{ $icons['secondIcon'] }}"></i>
                                @else
                                    <i class="fas {{ $icons['icon'] }}"></i>
                                @endif
                            </div>
                            <div>
                                <p class="font-bold text-white text-sm leading-tight tracking-wide">{{ $namaKategori }}</p>
                            </div>
                        </div>
                        <div class="text-xl font-black text-indigo-400">
                            {{ $jumlahJenis }} <span class="text-[10px] text-slate-500 tracking-wide">jenis</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Footer Info --}}
    <div class="mt-8 p-6 rounded-3xl text-white flex flex-col md:flex-row items-center justify-between gap-6 card-shadow" style="background-color: #1e293b;">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center text-3xl">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div>
                <h4 class="font-bold text-xl tracking-wide">Arsip Digital & Audit</h4>
                <p class="text-slate-300 text-xs mt-1 tracking-wide">Seluruh data yang ditampilkan telah tervalidasi oleh sistem LINTAS KBB secara real-time.</p>
            </div>
        </div>
        <div class="flex gap-3 w-full md:w-auto">
            <a href="{{ route('eksekutif.export.pdf') }}" class="bg-red-600 text-white px-8 py-3 rounded-2xl text-xs font-black hover:bg-red-700 transition-all shadow-lg flex-1 md:flex-none text-center tracking-wider">
                <i class="fas fa-file-pdf mr-2"></i> DOWNLOAD PDF
            </a>
            <a href="{{ route('eksekutif.export.excel') }}" class="bg-emerald-600 text-white px-8 py-3 rounded-2xl text-xs font-black hover:bg-emerald-700 transition-all shadow-lg flex-1 md:flex-none text-center tracking-wider">
                <i class="fas fa-file-excel mr-2"></i> DOWNLOAD EXCEL
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data untuk Chart Kondisi
    const ctxKondisi = document.getElementById('chartKondisi').getContext('2d');
    
    const statusRaw = {!! json_encode($status_aset) !!};
    const labels = ['Baik', 'Rusak', 'Kritis', 'Proses Perbaikan'];
    const colors = ['#28a745', '#ffc107', '#dc3545', '#17a2b8'];
    
    const chartData = labels.map(label => {
        const item = statusRaw.find(s => s.status === label);
        return item ? item.total : 0;
    });

    new Chart(ctxKondisi, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: chartData,
                backgroundColor: colors,
                borderWidth: 0,
                hoverOffset: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` ${context.label}: ${context.raw} Unit`;
                        }
                    }
                }
            },
            cutout: '80%',
            borderRadius: 5
        }
    });

    // Data untuk Chart Kategori (Horizontal Bar)
    const ctxKategori = document.getElementById('chartKategori').getContext('2d');
    
    // Gunakan daftar kategori lengkap untuk chart juga
    const chartLabels = {!! json_encode([
        'Penerangan Jalan Umum (PJU)',
        'Perlengkapan Jalan',
        'Fasilitas Lalu Lintas',
        'Pengendalian dan Pengawasan',
        'Prasarana Transportasi'
    ]) !!};
    
    // Mapping data dari database
    const kategoriDataMap = {};
    @foreach($kategori_stats as $kat)
        kategoriDataMap["{{ $kat->kategori }}"] = {{ $kat->jumlah_jenis }};
    @endforeach
    
    const chartDataKategori = chartLabels.map(label => kategoriDataMap[label] || 0);
    
    new Chart(ctxKategori, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Jumlah Jenis Aset',
                data: chartDataKategori,
                backgroundColor: '#3b82f6',
                borderRadius: 8,
                barThickness: 25
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    display: true,
                    position: 'top',
                    labels: { font: { weight: 'bold', size: 11, family: 'Inter' } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Jumlah Jenis Aset: ${context.raw}`;
                        }
                    }
                }
            },
            scales: {
                x: { 
                    grid: { display: false }, 
                    ticks: { 
                        font: { weight: '600', family: 'Inter', size: 11 },
                        stepSize: 0.2,
                        callback: function(val) {
                            return val;
                        }
                    },
                    title: { 
                        display: true, 
                        text: 'Jumlah Jenis Aset', 
                        font: { weight: '600', size: 11, family: 'Inter' },
                        color: '#475569'
                    }
                },
                y: { 
                    grid: { display: false }, 
                    ticks: { 
                        font: { weight: '600', family: 'Inter', size: 12 },
                        color: '#1e293b'
                    },
                    title: { display: true, text: 'Kategori Aset', font: { weight: '600', size: 11, family: 'Inter' } }
                }
            },
            layout: {
                padding: {
                    bottom: 8,
                    top: 8
                }
            }
        }
    });
</script>
@endsection