// resources/js/services/api.js

import axios from 'axios';

/**
 * ============================================================================
 * CORE NETWORK LAYER (AXIOS INSTANCE)
 * ============================================================================
 * Menangani semua lalu lintas komunikasi HTTP antara React dan Laravel.
 * Disuntikkan CSRF token secara otomatis untuk keamanan sistem.
 */

const api = axios.create({
    baseURL: '/api',
    timeout: 15000, // Timeout 15 detik (Best practice untuk cegah hanging request)
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest', // Standard Laravel AJAX Recognition
    },
});

// Request Interceptor: Injeksi CSRF Token secara dinamis
api.interceptors.request.use((config) => {
    // Membaca CSRF Token dari meta tag Blade Laravel (jika ada)
    const token = document.head.querySelector('meta[name="csrf-token"]');
    if (token) {
        config.headers['X-CSRF-TOKEN'] = token.content;
    }
    return config;
}, (error) => {
    return Promise.reject(error);
});

// Response Interceptor: Filter payload & Standardisasi Error handling
api.interceptors.response.use(
    (response) => {
        // Bypass bungkus axios, kembalikan data murninya saja
        return response.data;
    },
    (error) => {
        // Jika server mati atau timeout
        if (!error.response) {
            console.error('[Network Error] Tidak dapat terhubung ke server Laravel.', error);
            return Promise.reject({ message: 'Sistem tidak dapat menghubungi server.' });
        }

        // Ambil payload error dari Laravel
        const serverError = error.response.data;
        return Promise.reject({
            status: error.response.status,
            message: serverError.message || 'Terjadi kesalahan pada server.',
            errors: serverError.errors || null // Biasanya isi validasi
        });
    }
);

export default api;