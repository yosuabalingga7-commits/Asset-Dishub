import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/gis-app.jsx',   // Legacy GIS island (kept during transition)
                'resources/js/spa-app.jsx',   // New SPA entry point (Phase 0+)
            ],
            refresh: true,
        }),
        tailwindcss(),
        react(),
    ],
});

