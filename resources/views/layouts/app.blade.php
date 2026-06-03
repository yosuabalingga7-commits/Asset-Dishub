<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LINTAS | Layanan Inventaris & Sistem Tata Aset - Dishub KBB</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    {{-- CSS Leaflet & MarkerCluster --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
    
    {{-- HEROICONS CSS (Resmi dari Tailwind Labs) --}}
    <link rel="stylesheet" href="https://unpkg.com/heroicons@2.0.18/css/heroicons.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');

        html, body { 
            height: 100%; 
            margin: 0; 
            padding: 0; 
            scroll-behavior: smooth;
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            font-smoothing: antialiased;
        }
        
        [x-cloak] { display: none !important; }

        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #0f172a; }
        .custom-scrollbar::-webkit-scrollbar-thumb { 
            background: #334155; 
            border-radius: 10px; 
        }

        .map-mode { overflow: hidden !important; }

        /* Memastikan elemen Leaflet tidak menutupi UI Utama */
        .leaflet-container {
            z-index: 1 !important;
        }
    </style>

    @stack('styles')
</head>
<body class="bg-slate-950" x-data>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('sidebar', {
            open: localStorage.getItem('sidebarOpen') === 'true',
            toggle() {
                this.open = !this.open;
                localStorage.setItem('sidebarOpen', this.open);
            },
            openSidebar() {
                this.open = true;
                localStorage.setItem('sidebarOpen', true);
            },
            closeSidebar() {
                this.open = false;
                localStorage.setItem('sidebarOpen', false);
            }
        });
    });
</script>

<div class="flex h-screen overflow-hidden bg-slate-950">
    
    {{-- Sidebar Container --}}
    <div 
        :class="$store.sidebar.open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-[1060] w-64 transition-transform duration-300 transform lg:static lg:inset-0 bg-slate-900 shadow-2xl">
        @include('partials.sidebar')
    </div>

    {{-- Main Wrapper --}}
    <div class="flex-1 flex flex-col h-full min-w-0 overflow-hidden relative">
        
        {{-- Navbar --}}
        @include('partials.navbar')

        {{-- Content Area --}}
        <main class="flex-1 relative overflow-y-auto bg-slate-950 p-0 custom-scrollbar text-white">
            @yield('content')
        </main>

        {{-- Mobile Overlay --}}
        <div 
            x-show="$store.sidebar.open" 
            @click="$store.sidebar.closeSidebar()" 
            class="fixed inset-0 bg-black/70 z-[1055] lg:hidden backdrop-blur-sm"
            x-transition:enter="transition opacity-0 duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition opacity-100 duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
        </div>
    </div>
</div>

{{-- Script Leaflet diletakkan di akhir sebelum stack --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>

@stack('scripts')
</body>
</html>