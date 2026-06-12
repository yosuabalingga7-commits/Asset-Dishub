// resources/js/store/useLitasStore.js

import { create } from 'zustand';
import { spatialService } from '../services/spatialService';

/**
 * ============================================================================
 * useLitasStore (Information Expert - Domain & Spatial Caching)
 * ============================================================================
 * Mengelola aliran data mentah spasial dan bisnis dari Laravel Backend.
 * Memiliki mekanisme Caching Spasial cerdas di tingkat klien untuk menghemat 
 * pemanggilan API geofencing berulang saat peta digeser tipis oleh kursor.
 */

const useLitasStore = create((set, get) => ({
    // --- STATE DATA ---
    catalogAssets: [],             // Seluruh data master aset fisik teraktif (untuk sidebar)
    mapAssets: [],                 // Subset data aset terfilter viewport (untuk marker peta)
    recentReports: [],             // Laporan masyarakat & petugas terbaru
    priorityAssets: [],            // Daftar aset rusak kritis (prioritas tindak lanjut)
    reportStats: null,             // Statistik dashboard (Masyarakat vs Petugas)
    reportTrend: null,             // Data grafik tren pengaduan 7 hari

    // --- LOADING STATES ---
    isAssetsLoading: false,
    isReportsLoading: false,
    isStatsLoading: false,

    // --- SPATIAL CACHE STORAGE (Pure Fabrication) ---
    nearestAssets: [],             // Hasil deteksi aset terdekat (untuk form laporan)
    isNearestLoading: false,
    nearestCache: {},              // Cache spasial lokal sensor geofencing ("lat_lng_radius")
    viewportCache: {},             // Cache spasial lokal untuk simpan hasil filter BBOX ("minLat_minLng_maxLat_maxLng_zoom")

    // --- ACTIONS: DATA FETCHERS & CACHE INTEGRATION ---

    /**
     * Memuat aset spasial KBB dengan optimasi parameter viewport (BBOX).
     * Jika bounds dikirim, pengecekan cache lokal dijalankan sebelum memanggil API.
     */
    fetchAssets: async (bounds = null, zoom = null) => {
        // Kasus 1: Panggilan global tanpa pembatas viewport (Standard Hydration)
        if (!bounds) {
            set({ isAssetsLoading: true });
            try {
                const data = await spatialService.fetchAllAssets();
                set({ catalogAssets: data || [], isAssetsLoading: false });
            } catch (error) {
                set({ isAssetsLoading: false });
                console.error('[LitasStore] Gagal meng-hydrate data aset:', error);
            }
            return;
        }

        // Kasus 2: Viewport-Based Rendering dengan Geotag-Caching
        // Presisi 3 desimal (~111 meter toleransi geser) untuk efisiensi buffering kursor
        const roundedMinLat = bounds.minLat.toFixed(3);
        const roundedMinLng = bounds.minLng.toFixed(3);
        const roundedMaxLat = bounds.maxLat.toFixed(3);
        const roundedMaxLng = bounds.maxLng.toFixed(3);
        const cacheKey = `${roundedMinLat}_${roundedMinLng}_${roundedMaxLat}_${roundedMaxLng}_${zoom}`;

        const state = get();

        // HIT CACHE: Muat data dari memori browser lokal
        if (state.viewportCache[cacheKey]) {
            set({ mapAssets: state.viewportCache[cacheKey] });
            return;
        }

        // MISS CACHE: Tarik data spasial terkompresi dari database PostGIS
        set({ isAssetsLoading: true });
        try {
            const data = await spatialService.fetchAllAssets(bounds, zoom);
            set((state) => ({
                mapAssets: data || [],
                isAssetsLoading: false,
                viewportCache: {
                    ...state.viewportCache,
                    [cacheKey]: data || []
                }
            }));
        } catch (error) {
            set({ isAssetsLoading: false });
            console.error('[LitasStore] Gagal menyaring data aset spasial:', error);
        }
    },

    /**
     * Memuat data aset kritis untuk widget prioritas tindakan.
     */
    fetchPriorityAssets: async () => {
        try {
            const data = await spatialService.fetchPriorityAssets();
            set({ priorityAssets: data?.assets || [] });
        } catch (error) {
            console.error('[LitasStore] Gagal memuat aset prioritas:', error);
        }
    },

    /**
     * Memuat laporan pengaduan gabungan terbaru.
     */
    fetchRecentReports: async () => {
        set({ isReportsLoading: true });
        try {
            const data = await spatialService.fetchRecentReports();
            set({ recentReports: data?.reports || [], isReportsLoading: false });
        } catch (error) {
            set({ isReportsLoading: false });
            console.error('[LitasStore] Gagal memuat laporan terbaru:', error);
        }
    },

    /**
     * Memuat statistik perbandingan kuantitatif laporan.
     */
    fetchReportStats: async () => {
        set({ isStatsLoading: true });
        try {
            const data = await spatialService.fetchReportDetails();
            set({ reportStats: data || null, isStatsLoading: false });
        } catch (error) {
            set({ isStatsLoading: false });
            console.error('[LitasStore] Gagal memuat statistik laporan:', error);
        }
    },

    /**
     * Memuat tren pengaduan 7 hari untuk chart dashboard.
     */
    fetchReportTrend: async () => {
        try {
            const data = await spatialService.fetchReportTrend();
            set({ reportTrend: data || null });
        } catch (error) {
            console.error('[LitasStore] Gagal memuat tren laporan:', error);
        }
    },

    /**
     * LOGIKA GEOFENCING PINTAR (DENGAN CACHE LOKAL KLIEN)
     * Mengambil aset terdekat dalam radius meter.
     */
    getNearestAssets: async (lat, lng, radius = 100) => {
        if (!lat || !lng) return;

        const roundedLat = lat.toFixed(4);
        const roundedLng = lng.toFixed(4);
        const cacheKey = `${roundedLat}_${roundedLng}_${radius}`;

        const state = get();

        // Hit Cache Geofencing
        if (state.nearestCache[cacheKey]) {
            set({ nearestAssets: state.nearestCache[cacheKey] });
            return;
        }

        // Miss Cache Geofencing
        set({ isNearestLoading: true });
        try {
            const response = await spatialService.fetchNearestAssets(lat, lng, radius);
            const nearestList = response?.aset || [];

            set((state) => ({
                nearestAssets: nearestList,
                isNearestLoading: false,
                nearestCache: {
                    ...state.nearestCache,
                    [cacheKey]: nearestList
                }
            }));
        } catch (error) {
            set({ isNearestLoading: false });
            console.error('[LitasStore] Gagal melacak aset terdekat:', error);
        }
    },

    /**
     * Membersihkan seluruh cache spasial lokal di client-side.
     */
    clearLitasCache: () => set({
        nearestCache: {},
        viewportCache: {},
        nearestAssets: [],
        mapAssets: []
    })
}));

export default useLitasStore;