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
    assets: [],                    // Seluruh data master aset fisik
    recentReports: [],             // Laporan masyarakat & petugas terbaru
    priorityAssets: [],            // Daftar aset rusak kritis (prioritas tindak lanjut)
    reportStats: null,             // Statistik dashboard (Masyarakat vs Petugas)
    reportTrend: null,             // Data grafik tren pengaduan 7 hari

    // --- LOADING STATES ---
    isAssetsLoading: false,
    isReportsLoading: false,
    isStatsLoading: false,

    // --- NEAREST GEOFENCING STATE & CACHE ---
    nearestAssets: [],             // Hasil deteksi aset terdekat (untuk form laporan)
    isNearestLoading: false,
    nearestCache: {},              // Cache spasial lokal klien Key: "lat_lng_radius"

    // --- ACTIONS: DATA FETCHERS ---

    /**
     * Memuat seluruh aset spasial KBB untuk di-render di peta.
     */
    fetchAssets: async () => {
        set({ isAssetsLoading: true });
        try {
            const data = await spatialService.fetchAllAssets();
            set({ assets: data || [], isAssetsLoading: false });
        } catch (error) {
            set({ isAssetsLoading: false });
            console.error('[LitasStore] Gagal meng-hydrate data aset:', error);
        }
    },

    /**
     * Memuat data aset kritis untuk widget prioritas tindakan.
     */
    fetchPriorityAssets: async () => {
        try {
            const data = await spatialService.fetchPriorityAssets();
            // Data biasanya ber-format { assets: [...] } dari Laravel resource
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
     * Mengambil aset terdekat dalam radius meter. Jika kordinat mirip, gunakan cache.
     * Presisi 4 desimal setara akurasi ~11.1 meter di garis khatulistiwa.
     */
    getNearestAssets: async (lat, lng, radius = 100) => {
        if (!lat || !lng) return;

        // Bounding Key pembentuk koordinat aman
        const roundedLat = lat.toFixed(4);
        const roundedLng = lng.toFixed(4);
        const cacheKey = `${roundedLat}_${roundedLng}_${radius}`;

        const state = get();

        // Cek apakah data koordinat ini sudah pernah di-request sebelumnya (Hit Cache)
        if (state.nearestCache[cacheKey]) {
            set({ nearestAssets: state.nearestCache[cacheKey] });
            return;
        }

        // Cache Miss: Ambil data dari server PostGIS
        set({ isNearestLoading: true });
        try {
            const response = await spatialService.fetchNearestAssets(lat, lng, radius);
            const nearestList = response?.aset || [];

            // Simpan hasil respons ke memori cache lokal
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
     * Membersihkan cache spasial lokal di client-side.
     */
    clearLitasCache: () => set({ nearestCache: {}, nearestAssets: [] })
}));

export default useLitasStore;