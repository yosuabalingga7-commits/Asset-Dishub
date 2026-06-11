import os
from pathlib import Path

# --- KONFIGURASI ---
TARGET_DIRECTORY = r"C:\Users\PC\Documents\Dev\LINTAS\Asset-Dishub"
OUTPUT_FILE = r"C:\Users\PC\Documents\Dev\LINTAS\Asset-Dishub\laravel-hybrid-output-optimized.txt"

# 1. FORBIDDEN DIRS (Blacklist Diperketat)
# Ditambahkan: public, config, tests, lang, seeders, factories, css, sass
FORBIDDEN_DIRS = {
    "vendor", "node_modules", ".git", "storage", "bootstrap", 
    ".vscode", ".idea", "__pycache__", "img", "shp", "uploads", 
    "fonts", "coverage", "dist", "build",
    "public", "config", "tests", "lang", "seeders", "factories", "css", "sass", "assets"
}

# 2. ALLOWED DIRS (Hanya Core Architecture Domain)
ALLOWED_DIRS = {
    "app", "database", "resources", "routes"
}

# 3. ROOT FILES (Hanya Dependency Definition)
ALLOWED_ROOT_FILES = {
    "composer.json", "package.json", ".env.example"
}

# 4. EXTENSIONS (Dihapus: .sql, .css, .md, .json)
# Menghapus .sql akan membuang ratusan ribu token tidak berguna.
INCLUDE_EXTENSIONS = {
    ".php", ".js", ".jsx", ".ts", ".tsx", ".vue"
}

# 5. GUARD PATTERN: Batas ukuran file (30 KB)
# Mencegah file raksasa / auto-generated code termuat.
MAX_FILE_SIZE_BYTES = 50 * 1024  

def is_binary(file_path: Path) -> bool:
    try:
        with open(file_path, 'rb') as f:
            return b'\x00' in f.read(512)
    except Exception:
        return True

def main():
    target_path = Path(TARGET_DIRECTORY)
    if not target_path.is_dir():
        print(f"Error: Folder '{TARGET_DIRECTORY}' tidak ditemukan.")
        return

    files_to_process = []
    total_size = 0
    
    print("Menganalisa struktur arsitektur project Laravel (Strict Domain Mode)...")
    for file_path in target_path.rglob("*"):
        if not file_path.is_file():
            continue

        parts = file_path.relative_to(target_path).parts
        
        # Validasi 1: File Root
        is_root_file = len(parts) == 1
        if is_root_file:
            if file_path.name not in ALLOWED_ROOT_FILES:
                continue
        else:
            # Validasi 2: Cek Blacklist (Jika ada di folder terlarang, skip)
            if any(part in FORBIDDEN_DIRS for part in parts):
                continue
            
            # Validasi 3: Cek Whitelist (Hanya izinkan folder spesifik)
            if not any(part in ALLOWED_DIRS for part in parts):
                continue

        # Validasi 4: Filter Ekstensi
        if file_path.suffix in INCLUDE_EXTENSIONS or file_path.name in ALLOWED_ROOT_FILES:
            
            # Validasi 5: Size Guard & Binary Check
            try:
                file_size = file_path.stat().st_size
                if file_size > MAX_FILE_SIZE_BYTES:
                    print(f"[SKIPPED] {file_path.name} ukurannya terlalu besar (> 30KB).")
                    continue
                
                if not is_binary(file_path):
                    files_to_process.append(file_path)
                    total_size += file_size
            except Exception:
                pass

    # Eksekusi penulisan
    with open(OUTPUT_FILE, "w", encoding="utf-8") as f:
        f.write("=== STRUKTUR & ISI KODE (STRICT DOMAIN LOGIC) ===\n\n")
        for file_path in sorted(files_to_process):
            relative_path = file_path.relative_to(target_path)
            try:
                content = file_path.read_text("utf-8", errors="ignore")
                f.write(f"\n--- FILE: {relative_path} ---\n")
                f.write(content)
                f.write("\n")
                print(f"-> Memuat logika: {relative_path}")
            except Exception as e:
                print(f"-> Gagal membaca {relative_path}: {e}")

    print(f"\nSelesai! Hasil ekstraksi dioptimasi pada: {OUTPUT_FILE}")
    print(f"Estimasi Total Ukuran Teks: {total_size / 1024:.2f} KB (Sangat aman untuk LLM Context).")

if __name__ == "__main__":
    main()