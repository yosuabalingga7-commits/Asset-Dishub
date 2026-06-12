import os
import re
from pathlib import Path

# --- KONFIGURASI ---
TARGET_DIRECTORY = r"C:\Users\PC\Documents\Dev\LINTAS\Asset-Dishub"
OUTPUT_FILE = r"C:\Users\PC\Documents\Dev\LINTAS\Asset-Dishub\lintas-pure-gis.txt"

# 1. FORBIDDEN DIRS (Blokir folder public dan database agar GeoJSON/Seeder raksasa tidak ikut)
FORBIDDEN_DIRS = {
    "vendor", "node_modules", ".git", "storage", "bootstrap", 
    ".vscode", ".idea", "__pycache__", "img", "uploads", 
    "fonts", "coverage", "dist", "build", "assets",
    "public", "database", "tests", "lang", "config"
}

# 2. EXTENSIONS (HANYA LOGIC BE & FE. Dilarang keras ambil .json)
INCLUDE_EXTENSIONS = {
    ".php", ".js", ".jsx"
}

# 3. STRICT GIS KEYWORDS (Hanya file yang mengandung sintaks ini yang akan diambil)
# Ini memastikan file seperti LoginController atau User.php tidak akan ikut.
STRICT_GIS_KEYWORDS = [
    "react-leaflet", "leaflet", "postgis", "st_distancesphere", 
    "st_geomfromtext", "st_contains", "mapcontainer", "tilelayer", 
    "markercluster", "AsetApiController", "HasSpatialCoordinates",
    "useGisUIStore", "useLitasStore", "spatialService", "LintasMap"
]

# Tambahan: Path yang secara eksplisit murni GIS
EXACT_GIS_PATHS = ["gis", "map"]

def is_pure_gis_domain(file_path: Path, content: str) -> bool:
    path_str = str(file_path).lower()
    
    # Syarat 1: Jika path foldernya memang jelas-jelas tentang peta/GIS
    if any(kw in path_str.split(os.sep) for kw in EXACT_GIS_PATHS):
        return True
        
    # Syarat 2: Jika isi kodenya memanggil library atau query database spasial
    content_lower = content.lower()
    if any(kw in content_lower for kw in STRICT_GIS_KEYWORDS):
        return True
        
    return False

def main():
    target_path = Path(TARGET_DIRECTORY)
    if not target_path.is_dir():
        print(f"❌ Error: Folder '{TARGET_DIRECTORY}' tidak ditemukan.")
        return

    files_to_process = []
    total_size = 0
    
    print("🎯 Memulai Ekstraksi MURNI Logic GIS (Backend & Frontend)...")
    for file_path in target_path.rglob("*"):
        if not file_path.is_file():
            continue

        # Cek Ekstensi (Cuma PHP dan JS/JSX)
        if not any(file_path.name.endswith(ext) for ext in INCLUDE_EXTENSIONS):
            continue

        # Cek Direktori Terlarang
        parts = file_path.relative_to(target_path).parts
        if any(part in FORBIDDEN_DIRS for part in parts):
            continue

        try:
            # Lewati file yang kelewat besar (Safety net)
            file_size = file_path.stat().st_size
            if file_size > 1024 * 1024: # Limit 1 MB
                continue
                
            content = file_path.read_text("utf-8", errors="ignore")
            
            # Evaluasi Domain Ketat
            if is_pure_gis_domain(file_path, content):
                files_to_process.append((file_path, content))
                total_size += file_size

        except Exception as e:
            pass

    # Eksekusi penulisan
    with open(OUTPUT_FILE, "w", encoding="utf-8") as f:
        f.write("=== PURE GIS DOMAIN LOGIC (BACKEND & FRONTEND) ===\n")
        f.write("=== Excludes all raw GeoJSON, Configs, and Non-Spatial CRUD ===\n\n")
        
        for file_path, content in sorted(files_to_process, key=lambda x: x[0]):
            relative_path = file_path.relative_to(target_path)
            f.write(f"\n--- FILE: {relative_path} ---\n")
            f.write(content)
            f.write("\n")
            print(f"✅ Terekstrak: {relative_path}")

    print(f"\n🚀 Selesai! File Output: 👉 {OUTPUT_FILE}")
    print(f"📊 Total Ukuran Logic: {total_size / 1024:.2f} KB (Sangat Ringan untuk AI).")

if __name__ == "__main__":
    main()