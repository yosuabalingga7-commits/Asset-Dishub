<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dishub KBB - Pemetaan Aset</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    {{-- Tambahan library untuk Marker Cluster --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    
    <style>
        /* Mengunci body agar tidak bisa scroll secara keseluruhan */
        html, body { 
            height: 100%; 
            margin: 0; 
            padding: 0; 
            overflow: hidden; 
        }
        
        #map { height: 100%; width: 100%; }
        
        body { font-family: 'Inter', sans-serif; }
        
        /* Pencegahan elemen Alpine muncul sebelum load */
        [x-cloak] { display: none !important; }

        /* Custom scrollbar khusus untuk area konten */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-slate-100" x-data="{ sidebarOpen: false }">

{{-- Container utama --}}
<div class="flex h-screen overflow-hidden bg-slate-100">
    
    {{-- 
        Sidebar
        Sekarang sidebarOpen diatur dari body, 
        pastikan file sidebar.blade.php menggunakan x-show atau class binding berdasarkan 'sidebarOpen'
    --}}
    @include('partials.sidebar')

    {{-- Pembungkus area kanan --}}
    <div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden relative">
        
        {{-- Navbar --}}
        @include('partials.navbar')

        {{-- Main Content --}}
        <main class="flex-1 relative overflow-y-auto bg-slate-100 p-0 custom-scrollbar">
            @yield('content')
        </main>

        {{-- Overlay untuk Mobile saat Sidebar terbuka --}}
        <div 
            x-show="sidebarOpen" 
            @click="sidebarOpen = false" 
            class="fixed inset-0 bg-black/50 z-[40] lg:hidden"
            x-transition:enter="transition opacity-0 duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition opacity-100 duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
{{-- Tambahan script untuk Marker Cluster --}}
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

@stack('scripts')

</body>
</html>