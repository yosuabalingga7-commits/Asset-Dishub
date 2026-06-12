// resources/js/store/useLitasStore.js

import { create } from 'zustand';
import { spatialService } from '../services/spatialService';

/**
 * ============================================================================
 * useLitasStore (Information Expert - Domain & Spatial Caching)
 * ============================================================================
 * Mengelola aliran data mentah spasial dan bisnis dari Laravel Backend.
 *
 * STATE ARCHITECTURE — DECOUPLED (FIX: State Coupling Bug)
 * ─────────────────────────────────────────────────────────
 * catalogAssets : Seluruh aset global untuk katalog pencarian panel kiri.
 *                 Hanya diisi oleh panggilan fetchAssets() TANPA bounds.
 *                 Tidak pernah ditimpa oleh pergeseran kamera peta.
 *
 * mapAssets     : Aset terfilter viewport aktif untuk rendering marker peta.
 *                 Diperbarui dinamis oleh panggilan fetchAssets(bounds, zoom).
 *
 * Dengan pemisahan ini, menggeser kamera peta (yang memicu fetchAssets(bounds))
 * tidak akan lagi mengosongkan daftar aset di panel katalog (AssetCatalogPanel).
 */

const useLitasStore = create((set, get) => ({
    // --- STATE DATA ---
    catalogAssets: [],          // Seluruh data aset global (untuk panel Katalog)
    mapAssets: [],              // Data aset terfilter viewport (untuk AssetMarkers)
    recentReports: [],          // Laporan masyarakat & petugas terbaru
    priorityAssets: [],         // Daftar aset rusak kritis (prioritas tindak lanjut)
    reportStats: null,          // Statistik dashboard (Masyarakat vs Petugas)
    reportTrend: null,          // Data grafik tren pengaduan 7 hari

    // --- LOADING STATES ---
    isAssetsLoading: false,
    isMapAssetsLoading: false,
    isReportsLoading: false,
    isStatsLoading: false,

    // --- SPATIAL CACHE STORAGE (Pure Fabrication) ---
    nearestAssets: [],          // Hasil deteksi aset terdekat (untuk form laporan)
    isNearestLoading: false,
    nearestCache: {},           // Cache spasial lokal sensor geofencing ("lat_lng_radius")
    viewportCache: {},          // Cache spasial lokal untuk simpan hasil filter BBOX

    // --- ACTIONS: DATA FETCHERS & CACHE INTEGRATION ---

    /**
     * Memuat data aset dengan dua mode berdasarkan ada/tidaknya bounds:
     *
     * Mode A — Global Hydration (bounds = null):
     *   Memuat SELURUH aset tanpa filter viewport.
     *   Hasilnya disimpan ke `catalogAssets` (tidak menyentuh `mapAssets`).
     *   Digunakan saat: bootstrap aplikasi, buka panel katalog.
     *
     * Mode B — Viewport-Based Rendering (bounds diberikan):
     *   Memuat aset terfilter oleh BBOX viewport peta aktif.
     *   Hasilnya disimpan ke `mapAssets` (tidak menyentuh `catalogAssets`).
     *   Digunakan saat: peta digeser atau di-zoom.
     */
    fetchAssets: async (bounds = null, zoom = null) => {
        // ── MODE A: Global Hydration (untuk Katalog Panel) ──
        if (!bounds) {
            // Jangan re-fetch jika katalog sudah terisi dan tidak ada flag loading
            if (get().catalogAssets.length > 0 && !get().isAssetsLoading) {
                return;
            }
            set({ isAssetsLoading: true });
            try {
                const data = await spatialService.fetchAllAssets();
                set({ catalogAssets: data || [], isAssetsLoading: false });
            } catch (error) {
                set({ isAssetsLoading: false });
                console.error('[LitasStore] Gagal meng-hydrate data katalog aset:', error);
            }
            return;
        }

        // ── MODE B: Viewport-Based Rendering (untuk AssetMarkers) ──
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
        set({ isMapAssetsLoading: true });
        try {
            const data = await spatialService.fetchAllAssets(bounds, zoom);
            set((state) => ({
                mapAssets: data || [],
                isMapAssetsLoading: false,
                viewportCache: {
                    ...state.viewportCache,
                    [cacheKey]: data || []
                }
            }));
        } catch (error) {
            set({ isMapAssetsLoading: false });
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