// resources/js/services/spatialService.js

import api from './api';
// Kita menggunakan axios biasa untuk endpoint non-api jika tidak ada prefix /api
import axios from 'axios';

/**
 * ============================================================================
 * SPATIAL SERVICE (PURE FABRICATION PATTERN)
 * ============================================================================
 * Bertanggung jawab murni untuk mengambil dan memformat data spasial dari 
 * Laravel Backend. Komponen React dan Zustand Store TIDAK BOLEH memanggil 
 * Axios secara langsung, mereka harus lewat pintu gerbang (Service) ini.
 */

export const spatialService = {

    /**
     * MENGAMBIL DATA ASET (MASTER SPASIAL)
     * Menggunakan endpoint yang sudah disiapkan di AsetApiController.
     * Mengirimkan koordinat batas wilayah (BBOX) & zoom level jika tersedia 
     * untuk memicu penanganan spasial dan clustering tingkat server.
     * 
     * @param {Object|null} bounds - Batas pandang koordinat { minLat, minLng, maxLat, maxLng }
     * @param {number|null} zoom - Tingkat zoom peta saat ini
     */
    fetchAllAssets: async (bounds = null, zoom = null) => {
        try {
            const params = {};

            if (bounds) {
                params.minLat = bounds.minLat;
                params.minLng = bounds.minLng;
                params.maxLat = bounds.maxLat;
                params.maxLng = bounds.maxLng;
            }

            if (zoom !== null) {
                params.zoom = zoom;
            }

            return await api.get('/asets-map', { params });
        } catch (error) {
            console.error('[SpatialService] Gagal memuat data aset:', error);
            throw error;
        }
    },

    /**
     * MENGAMBIL ASET PRIORITAS (KRITIS)
     * Untuk ditonjolkan pada Dashboard Admin dan Laci Peta.
     */
    fetchPriorityAssets: async () => {
        try {
            return await api.get('/priority-assets');
        } catch (error) {
            console.error('[SpatialService] Gagal memuat aset prioritas:', error);
            throw error;
        }
    },

    /**
     * MENGAMBIL DATA PENGADUAN TERBARU
     * Gabungan laporan dari Masyarakat dan Petugas Lapangan.
     */
    fetchRecentReports: async () => {
        try {
            return await api.get('/recent-reports');
        } catch (error) {
            console.error('[SpatialService] Gagal memuat laporan terbaru:', error);
            throw error;
        }
    },

    /**
     * MENGAMBIL STATISTIK PENGADUAN (Masyarakat vs Petugas)
     */
    fetchReportDetails: async () => {
        try {
            return await api.get('/laporan-details');
        } catch (error) {
            console.error('[SpatialService] Gagal memuat detail pengaduan:', error);
            throw error;
        }
    },

    /**
     * MENGAMBIL TREN PENGADUAN 7 HARI TERAKHIR (Untuk Grafik)
     */
    fetchReportTrend: async () => {
        try {
            return await api.get('/report-trend');
        } catch (error) {
            console.error('[SpatialService] Gagal memuat tren pengaduan:', error);
            throw error;
        }
    },

    /**
     * MENCARI ASET TERDEKAT DARI TITIK KOORDINAT (GEOFENCING RADIUS)
     * Digunakan untuk fitur deteksi aset otomatis saat user menunjuk lokasi peta.
     * Perhatikan endpoint-nya bukan /api, tapi /laporan/aset-terdekat.
     * 
     * @param {number} lat - Latitude
     * @param {number} lng - Longitude
     * @param {number} radius - Jarak dalam meter (Default 100m)
     */
    fetchNearestAssets: async (lat, lng, radius = 100) => {
        try {
            // Karena endpoint dari LaporanController bukan ber-prefix /api,
            // kita pakai standard axios dengan interceptor manual jika perlu,
            // atau cukup axios get standar (disesuaikan dengan route web.php).
            const response = await axios.get(`/laporan/aset-terdekat`, {
                params: { lat, lng, radius },
                headers: { 'Accept': 'application/json' }
            });
            return response.data;
        } catch (error) {
            console.error('[SpatialService] Gagal mendeteksi aset terdekat:', error);
            throw error;
        }
    }
};