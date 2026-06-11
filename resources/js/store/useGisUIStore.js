// resources/js/store/useGisUIStore.js

import { create } from 'zustand';

/**
 * ============================================================================
 * useGisUIStore (Information Expert - UI & Map Context)
 * ============================================================================
 * Mengelola keadaan antarmuka visual GIS LINTAS KBB.
 * Menerapkan pola "Stacking Drawers" (GFW Paradigm) yang memungkinkan menu 
 * bertumpuk dan bergeser secara mulus di atas kanvas peta.
 */

const DEFAULT_CENTER = [-6.8431, 107.4912]; // Titik pusat default Kabupaten Bandung Barat
const DEFAULT_ZOOM = 11;                    // Skala zoom awal KBB

const useGisUIStore = create((set) => ({
    // --- STATE DATA ---
    activePanels: [],              // Tumpukan laci aktif [{ id, type, title, data }]
    activeBaseMap: 'dark',         // Default 'dark' (Carto Dark) atau 'satellite'
    mapOpacity: 80,                // Opacity layer spasial (0 - 100)
    activeLayers: ['assets', 'reports'], // Layer peta yang sedang di-render
    selectedAssetId: null,         // ID aset fisik yang sedang dipilih/fokus
    selectedReportId: null,        // ID laporan pengaduan yang sedang dipilih/fokus
    mapCenter: DEFAULT_CENTER,     // Titik koordinat pusat peta saat ini
    mapZoom: DEFAULT_ZOOM,         // Tingkat zoom peta saat ini

    // --- ACTIONS: ORCHESTRATOR PANELS (STAKING ENGINE) ---
    /**
     * Membuka laci panel baru secara cerdas (Mutually Exclusive).
     * @param {string} type - Jenis panel (contoh: 'katalog-aset', 'detil-aset')
     * @param {string} title - Judul laci panel
     * @param {any} data - Payload data opsional untuk dikirim ke laci
     */
    openPanel: (type, title, data = null) => set((state) => {
        const isDetailPanel = type === 'detil-aset' || type === 'detil-laporan';
        let nextPanels = [...state.activePanels];

        if (isDetailPanel) {
            // Jika membuka laci detail mikro, tutup laci detail lain agar tidak tumpang tindih
            nextPanels = nextPanels.filter(p => p.type !== 'detil-aset' && p.type !== 'detil-laporan');
        } else {
            // Jika membuka laci menu utama sidebar, tutup laci menu utama lainnya
            const menuTypes = ['katalog-aset', 'katalog-laporan', 'konfigurasi', 'tentang'];
            if (menuTypes.includes(type)) {
                nextPanels = nextPanels.filter(p => !menuTypes.includes(p.type));
            }
            // Serta hilangkan instance dengan tipe yang sama
            nextPanels = nextPanels.filter(p => p.type !== type);
        }

        const newPanel = {
            id: `${type}-${Date.now()}`, // ID unik menghindari React key conflict
            type,
            title,
            data
        };

        nextPanels.push(newPanel);
        return { activePanels: nextPanels };
    }),

    /**
     * Menutup laci tertentu berdasarkan ID uniknya.
     */
    closePanel: (id) => set((state) => {
        const panelToClose = state.activePanels.find(p => p.id === id);
        if (!panelToClose) return {};

        let filtered = state.activePanels.filter(p => p.id !== id);

        // Jika laci detail ditutup, hilangkan juga highlight fokus di peta
        const isDetailClosed = panelToClose.type === 'detil-aset' || panelToClose.type === 'detil-laporan';

        return {
            activePanels: filtered,
            ...(isDetailClosed && { selectedAssetId: null, selectedReportId: null })
        };
    }),

    /**
     * Menutup seluruh laci di sebelah kanan indeks terpilih (Clean Stacking).
     */
    closePanelsToTheRight: (index) => set((state) => {
        const sliced = state.activePanels.slice(0, index + 1);
        const hasDetails = sliced.some(p => p.type === 'detil-aset' || p.type === 'detil-laporan');

        return {
            activePanels: sliced,
            ...(!hasDetails && { selectedAssetId: null, selectedReportId: null })
        };
    }),

    /**
     * Membersihkan seluruh tumpukan laci.
     */
    clearPanels: () => set({ activePanels: [], selectedAssetId: null, selectedReportId: null }),

    // --- ACTIONS: MAP CONTROLS ---
    setActiveBaseMap: (baseMapId) => set({ activeBaseMap: baseMapId }),
    setMapOpacity: (opacity) => set({ mapOpacity: opacity }),

    toggleLayer: (layerId) => set((state) => {
        const isExist = state.activeLayers.includes(layerId);
        return {
            activeLayers: isExist
                ? state.activeLayers.filter(id => id !== layerId)
                : [...state.activeLayers, layerId]
        };
    }),

    setSelectedAssetId: (id) => set({ selectedAssetId: id }),
    setSelectedReportId: (id) => set({ selectedReportId: id }),

    setMapCenter: (center) => set({ mapCenter: center }),
    setMapZoom: (zoom) => set({ mapZoom: zoom }),

    /**
     * Reset seluruh visual antarmuka kembali ke kondisi awal (KBB Default).
     */
    resetGisUI: () => set({
        activePanels: [],
        activeBaseMap: 'dark',
        mapOpacity: 80,
        activeLayers: ['assets', 'reports'],
        selectedAssetId: null,
        selectedReportId: null,
        mapCenter: DEFAULT_CENTER,
        mapZoom: DEFAULT_ZOOM
    })
}));

export default useGisUIStore;
