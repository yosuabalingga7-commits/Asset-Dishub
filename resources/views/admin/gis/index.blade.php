<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Meta Token Keamanan CSRF untuk Jembatan Komunikasi Axios (api.js) -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Command Center GIS | LINTAS KBB</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Leaflet CSS (Tingkat Global) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        html,
        body,
        #gis-root {
            margin: 0;
            padding: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
        }
    </style>

    <!-- Penyuntikan Sesi Autentikasi Pengguna Laravel ke Level Window (Telemetry Data) -->
    <script>
        window.LaravelUser = {
            name: "{{ Auth::user()->name ?? 'Administrator' }}",
            role: "{{ Auth::user()->role ?? 'Admin Sistem' }}",
            nip: "{{ Auth::user()->nip ?? '' }}"
        };
    </script>

    <!-- Vite Reload & React GIS Compilation Loading -->
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/gis-app.jsx'])
</head>

<body class="bg-slate-950">
    <!-- 
        GIS ROOT MOUNT POINT
        Dikunci secara eksklusif hanya untuk diisi oleh React Virtual DOM.
        Tidak boleh dicemari oleh pemrosesan DOM sepihak dari skrip vanilla Leaflet eksternal.
    -->
    <div id="gis-root"></div>
</body>

</html>