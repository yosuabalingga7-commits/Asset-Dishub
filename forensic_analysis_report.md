# LINTAS KBB GIS Spasial - Forensic Audit & Optimization Report

Dokumen ini memuat temuan teknis mengenai penyebab kemacetan visual (*infinite loop* dan *main-thread blocking*) di rute `/admin/gis` beserta solusi optimasinya.

---

## 1. Temuan Utama & Lokasi Titik Kemacetan

Berdasarkan audit mendalam, terdapat tiga anomali utama yang berkolaborasi menyumbat *main thread* JavaScript:

| No | Jenis Masalah | Lokasi Berkas & Baris | Penyebab Utama |
|---|---|---|---|
| **1** | **Infinite Hydration Loop** | [AssetMarkers.jsx:L70-74](file:///c:/Users/PC/Documents/Dev/LINTAS/Asset-Dishub/resources/js/components/gis/AssetMarkers.jsx#L70-L74)<br>[ReportMarkers.jsx:L49-53](file:///c:/Users/PC/Documents/Dev/LINTAS/Asset-Dishub/resources/js/components/gis/ReportMarkers.jsx#L49-L53) | Dependensi `useEffect` memicu kueri API tanpa henti saat data kosong (`length === 0`). |
| **2** | **Main-Thread Blocking (GeoJSON)** | [LintasMap.jsx:L78-110](file:///c:/Users/PC/Documents/Dev/LINTAS/Asset-Dishub/resources/js/components/gis/LintasMap.jsx#L78-L110) | Memproses file GeoJSON `administrasi_desa.json` seberat **4.9 MB** menjadi ribuan cincin *hole masking* tanpa downsampling. |
| **3** | **Siklus Re-render Peta Agresif** | [LintasMap.jsx:L45-46](file:///c:/Users/PC/Documents/Dev/LINTAS/Asset-Dishub/resources/js/components/gis/LintasMap.jsx#L45-L46) | Melakukan subscribe secara reaktif ke `mapCenter` dan `mapZoom` yang memicu re-render seluruh kontainer peta setiap kali peta bergeser. |

---

## 2. Mekanisme Runtunan Logika (Runtime Diagnostics)

### A. Aliran Loop Hidrasi (Feedback Loop)
Saat pertama kali halaman dimuat dengan database kosong atau ketika respons kueri bernilai nihil (`[]`):
1. Komponen [AssetMarkers](file:///c:/Users/PC/Documents/Dev/LINTAS/Asset-Dishub/resources/js/components/gis/AssetMarkers.jsx) di-mount.
2. `useEffect` memeriksa kondisi `if (assets.length === 0 && !isAssetsLoading)`. Karena data kosong dan status loading tidak aktif, ia memanggil `fetchAssets()`.
3. `fetchAssets()` mengubah state store: `isAssetsLoading: true` (memicu re-render 1).
4. Pemanggilan API selesai dengan sukses tetapi mengembalikan data kosong. Store di-update: `assets: []` dan `isAssetsLoading: false` (memicu re-render 2).
5. Karena `assets.length` tetap `0` dan `isAssetsLoading` kembali `false`, kondisi `useEffect` terpenuhi kembali.
6. Langkah 3-5 berulang dalam siklus tidak terbatas (infinite loop) yang membanjiri tab jaringan dengan request API. Hal yang sama terjadi secara paralel di [ReportMarkers](file:///c:/Users/PC/Documents/Dev/LINTAS/Asset-Dishub/resources/js/components/gis/ReportMarkers.jsx).

### B. Penyumbatan Main Thread oleh Cincin Masking GeoJSON
Berkas `administrasi_desa.json` memiliki ukuran **4.86 MB** yang memuat puluhan ribu koordinat batas desa:
1. `LintasMap` mengambil data ini, lalu `kbbMaskingPolygon` melakukan iterasi mendalam pada semua *features* untuk menyusun array koordinat `innerHoles`.
2. Array raksasa ini dilempar ke komponen `<Polygon positions={kbbMaskingPolygon} />`.
3. Leaflet Engine harus menggambar SVG path tunggal dengan ratusan ribu koordinat point tersebut. Ini memakan waktu detik di satu *thread* CPU.
4. Setiap kali terjadi re-render (akibat loop hidrasi di atas atau pergeseran peta), React membandingkan (*diffing*) array koordinat raksasa ini, menyebabkan *freezing* browser.

### C. Re-render Peta saat Digeser
1. Pengguna menggeser peta. Event `moveend` Leaflet ditangkap oleh `MapControllers.jsx` dan memperbarui `mapCenter` di Zustand store.
2. `LintasMap.jsx` yang men-subscribe `mapCenter` secara reaktif terpaksa melakukan re-render.
3. `<MapContainer>` me-re-render seluruh anak komponennya (termasuk `<GeoJSON>` dan `<Polygon>` raksasa). CPU terbebani penuh secara konstan selama peta digerakkan.

---

## 3. Usulan Perbaikan Kode (Optimized Solution)

### A. Penyehatan Loop Hidrasi
Karena bootstrapping data awal sudah ditangani di tingkat root ([gis-app.jsx:L42-57](file:///c:/Users/PC/Documents/Dev/LINTAS/Asset-Dishub/resources/js/gis-app.jsx#L42-L57)), kita dapat menghapus `useEffect` pemanggil API di dalam `AssetMarkers.jsx` dan `ReportMarkers.jsx` secara aman.

### B. Mencegah Re-render Peta Dasar
Dekopel `mapCenter` dan `mapZoom` dari render reaktif `LintasMap.jsx` dengan mengambil nilai awal secara statis (non-reaktif). Peta selanjutnya akan dikendalikan murni secara imperatif lewat `MapControllers`.

### C. Downsampling Koordinat GeoJSON Masking
Gunakan teknik penyaringan indeks (*index filtering*) untuk mengurangi kerapatan koordinat sebesar 75% khusus untuk penggambaran poligon masking luar (tanpa merusak estetika garis batas utama).

---

## 4. Refactoring Implementasi

Berikut adalah kode rekomendasi baru untuk berkas-berkas terkait:

### 1. Modifikasi [LintasMap.jsx](file:///c:/Users/PC/Documents/Dev/LINTAS/Asset-Dishub/resources/js/components/gis/LintasMap.jsx)

```diff
-    const mapCenter = useGisUIStore((state) => state.mapCenter);
-    const mapZoom = useGisUIStore((state) => state.mapZoom);
+    // Ambil koordinat awal sekali saja (tidak reaktif) untuk menghindari re-render massal saat peta digeser/di-zoom
+    const initialCenter = useMemo(() => useGisUIStore.getState().mapCenter, []);
+    const initialZoom = useMemo(() => useGisUIStore.getState().mapZoom, []);

...

     const kbbMaskingPolygon = useMemo(() => {
         if (!geoJsonData || !geoJsonData.features) return null;
 
         const outerWorldBounds = [
             [90, -360], [90, 360], [-90, 360], [-90, -360], [90, -360]
         ];
 
         const innerHoles = [];
 
         geoJsonData.features.forEach((feature) => {
             const geom = feature.geometry;
             if (!geom) return;
 
             if (geom.type === 'Polygon') {
                 geom.coordinates.forEach((ring) => {
-                    const latLngRing = ring.map(coord => [coord[1], coord[0]]);
+                    // Downsample koordinat masking sebesar 75% (ambil setiap koordinat ke-4)
+                    const downsampled = ring.filter((_, idx) => idx % 4 === 0 || idx === ring.length - 1);
+                    const latLngRing = downsampled.map(coord => [coord[1], coord[0]]);
                     innerHoles.push(latLngRing);
                 });
             } else if (geom.type === 'MultiPolygon') {
                 geom.coordinates.forEach((polygon) => {
                     polygon.forEach((ring) => {
-                        const latLngRing = ring.map(coord => [coord[1], coord[0]]);
+                        // Downsample koordinat masking sebesar 75%
+                        const downsampled = ring.filter((_, idx) => idx % 4 === 0 || idx === ring.length - 1);
+                        const latLngRing = downsampled.map(coord => [coord[1], coord[0]]);
                         innerHoles.push(latLngRing);
                     });
                 });
             }
         });
 
         return [outerWorldBounds, ...innerHoles];
     }, [geoJsonData]);

...

             <MapContainer
-                center={mapCenter}
-                zoom={mapZoom}
+                center={initialCenter}
+                zoom={initialZoom}
```

### 2. Modifikasi [AssetMarkers.jsx](file:///c:/Users/PC/Documents/Dev/LINTAS/Asset-Dishub/resources/js/components/gis/AssetMarkers.jsx)

Hapus/komentari blok `useEffect` hidrasi otomatis:

```diff
-    // Auto-hydration jika data kosong saat komponen dipasang (Information Expert)
-    useEffect(() => {
-        if (assets.length === 0 && !isAssetsLoading) {
-            fetchAssets();
-        }
-    }, [assets.length, isAssetsLoading, fetchAssets]);
```

### 3. Modifikasi [ReportMarkers.jsx](file:///c:/Users/PC/Documents/Dev/LINTAS/Asset-Dishub/resources/js/components/gis/ReportMarkers.jsx)

Hapus/komentari blok `useEffect` hidrasi otomatis:

```diff
-    // Auto-hydration jika data kosong (Information Expert)
-    useEffect(() => {
-        if (recentReports.length === 0 && !isReportsLoading) {
-            fetchRecentReports();
-        }
-    }, [recentReports.length, isReportsLoading, fetchRecentReports]);
```
