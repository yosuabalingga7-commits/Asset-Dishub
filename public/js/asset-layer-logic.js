/**
 * ASSET LAYER LOGIC - DISHUB KBB
 * Versi: 1.1 (Updated: Clean Border & Balanced Size)
 * Deskripsi: Mengatur standarisasi warna kategori dan visualisasi marker tanpa border putih.
 */

// 1. KONFIGURASI WARNA KATEGORI (Global)
const categoryColors = {
    'Fasilitas Lalu Lintas': '#0000FF',       // Biru
    'Penerangan Jalan Umum (PJU)': '#008000', // Hijau
    'Pengendalian & Pengawasan': '#FFFF00',    // Kuning
    'Perlengkapan Jalan': '#800080',          // Ungu
    'Prasarana Transportasi': '#000000'       // Hitam
};

/**
 * 2. FUNGSI PEMBUAT MARKER 3 LAPIS
 * @param {string} kategoriName - Nama kategori untuk menentukan warna lingkaran tengah.
 * @param {string} statusColor - Kode warna HEX dari status (Baik, Rusak, dll) untuk lingkaran luar.
 * @param {string} emojiIcon - Karakter emoji atau icon dari database.
 * @returns {L.divIcon} - Objek icon Leaflet.
 */
function createMarkerIcon(kategoriName, statusColor, emojiIcon) {
    // Ambil warna kategori, jika tidak terdaftar gunakan warna abu-abu (#808080)
    const catColor = categoryColors[kategoriName] || '#808080';
    
    // Default emoji jika data kosong
    const icon = emojiIcon || '📍';

    // Ukuran disesuaikan agar warna status tidak terlalu mencolok (lebih seimbang)
    return L.divIcon({
        html: `
            <div style="
                background: ${statusColor}; 
                width: 36px; 
                height: 36px; 
                border-radius: 50%; 
                display: flex; 
                align-items: center; 
                justify-content: center; 
                box-shadow: 0 3px 8px rgba(0,0,0,0.3);
                border: none; 
                transition: all 0.3s ease;">
                
                <div style="
                    background: ${catColor}; 
                    width: 28px; 
                    height: 28px; 
                    border-radius: 50%; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center;
                    border: none;">
                    
                    <span style="font-size: 16px; filter: drop-shadow(0 1px 1px rgba(0,0,0,0.2));">
                        ${icon}
                    </span>
                </div>
            </div>`,
        className: 'custom-div-icon', 
        iconSize: [36, 36], 
        iconAnchor: [18, 18] // Titik pusat marker (setengah dari iconSize 36)
    });
}

/**
 * 3. HELPER: DAPATKAN WARNA KATEGORI (Opsional)
 */
function getCategoryColor(catName) {
    return categoryColors[catName] || '#808080';
}