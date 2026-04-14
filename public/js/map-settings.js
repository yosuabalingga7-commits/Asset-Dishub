const MAP_SETTINGS = {
    latitude: -6.8431, 
    longitude: 107.4912,
    defaultZoom: 11, // Zoom awal cakupan KBB
    detailZoom: 18,  // Zoom saat user klik titik tertentu
    maxZoom: 20,
    minZoom: 8,      // Diturunkan ke 8 agar bisa zoom-out lebih luas melihat area sekitar
    googleStreet: "https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}",
    googleSatellite: "https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}"
};