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
<body class="bg-slate-100">

{{-- Container utama menggunakan h-screen dan overflow-hidden agar sidebar tidak ikut scroll --}}
<div class="flex h-screen overflow-hidden">
    
    {{-- Sidebar - Tetap di kiri, tidak ikut scroll --}}
    @include('partials.sidebar')

    {{-- Pembungkus area kanan --}}
    <div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden">
        
        {{-- Navbar - Tetap di atas, tidak ikut scroll --}}
        @include('partials.navbar')

        {{-- Main Content - HANYA bagian ini yang bisa di-scroll --}}
        {{-- class flex-1 dan overflow-y-auto adalah kunci agar form bisa scroll tanpa menarik sidebar --}}
        <main class="flex-1 relative overflow-y-auto bg-slate-100 p-0 custom-scrollbar">
            @yield('content')
        </main>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

{{-- Tambahan script untuk Marker Cluster --}}
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

@stack('scripts')

</body>
</html>