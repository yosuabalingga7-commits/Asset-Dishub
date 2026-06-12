<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO: Dynamic title injected by each page via React Helmet or document.title --}}
    <title>LINTAS — Sistem Informasi Aset Dishub KBB</title>
    <meta name="description" content="Sistem Informasi Manajemen Aset Dinas Perhubungan Kabupaten Bandung Barat. Monitoring spasial, pelaporan, dan pengelolaan aset infrastruktur transportasi.">

    {{--
    ============================================================================
    LINTAS SPA SHELL — Laravel Blade Host
    ============================================================================
    This is the ONLY Blade file rendered for the entire SPA.
    React Router DOM takes full ownership of client-side routing from here.
    All subsequent navigation is handled in-browser — no further Blade views.

    Authentication: Sanctum SPA cookie (same-domain).
    The CSRF token above is read by the Axios interceptor in api.js.
    ============================================================================
    --}}

    {{-- Preconnect to Google Fonts (performance) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Vite: Injects compiled CSS + React bundle --}}
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/spa-app.jsx'])
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased" id="spa-body">

    {{--
        #spa-root: The single mount point for the entire React SPA.
        React DOM renders <SpaApp /> here via createRoot().
    --}}
    <div id="spa-root" class="h-full"></div>

    {{--
        Noscript fallback: Shown if JavaScript is disabled.
    --}}
    <noscript>
        <div style="padding: 2rem; font-family: 'Roboto', sans-serif; text-align: center; color: #334155;">
            <h2 style="margin-bottom: 0.5rem;">JavaScript Diperlukan</h2>
            <p>Aplikasi LINTAS membutuhkan JavaScript aktif untuk berfungsi. Aktifkan JavaScript pada browser Anda.</p>
        </div>
    </noscript>

</body>
</html>
