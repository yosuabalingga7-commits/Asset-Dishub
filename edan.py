import os
import argparse
import logging
from pathlib import Path
from typing import Set, List

# Konfigurasi Logging yang bersih
logging.basicConfig(level=logging.INFO, format='%(message)s')
logger = logging.getLogger(__name__)

class ExtractorConfig:
    """Konfigurasi sentral (Information Expert) untuk ekstraksi codebase."""
    
    # Tambahan: 'public', 'vendor', dll.
    FORBIDDEN_DIRS: Set[str] = {
        "vendor", "node_modules", ".git", "storage", "bootstrap", 
        ".vscode", ".idea", "__pycache__", "img", "shp", "uploads", 
        "fonts", "coverage", "dist", "build", "public", "config", 
        "tests", "lang", "seeders", "factories", "css", "sass", "assets"
    }

    ALLOWED_DIRS: Set[str] = {
        "app", "database", "resources", "routes"
    }

    # Menambahkan vite.config.js karena penting untuk konteks SPA
    ALLOWED_ROOT_FILES: Set[str] = {
        "composer.json", "package.json", ".env.example", "vite.config.js"
    }

    INCLUDE_EXTENSIONS: Set[str] = {
        ".php", ".js", ".jsx", ".ts", ".tsx", ".vue"
    }

    # Ditingkatkan ke 80KB karena file React/Blade seringkali padat logika
    MAX_FILE_SIZE_BYTES: int = 80 * 1024  


class LLMContextOptimizer:
    """Utility untuk memampatkan teks agar menghemat token LLM (Pure Fabrication)."""
    
    @staticmethod
    def compress_code(content: str) -> str:
        """Menghapus spasi trailing dan baris kosong ganda untuk hemat token LLM."""
        lines = content.splitlines()
        optimized_lines = []
        previous_empty = False
        
        for line in lines:
            stripped = line.rstrip()
            is_empty = len(stripped) == 0
            
            if is_empty and previous_empty:
                continue # Skip consecutive empty lines
                
            optimized_lines.append(stripped)
            previous_empty = is_empty
            
        return "\n".join(optimized_lines)

    @staticmethod
    def is_binary(file_path: Path) -> bool:
        try:
            with open(file_path, 'rb') as f:
                return b'\x00' in f.read(512)
        except Exception:
            return True


class CodebaseExtractor:
    """Controller utama untuk proses ekstraksi (GRASP Controller)."""
    
    def __init__(self, target_dir: str, output_file: str):
        self.target_path = Path(target_dir).resolve()
        self.output_file = Path(output_file).resolve()
        self.config = ExtractorConfig()
        self.optimizer = LLMContextOptimizer()

    def _should_process(self, file_path: Path) -> bool:
        parts = file_path.relative_to(self.target_path).parts
        is_root_file = len(parts) == 1

        # 1. Validasi Root Files
        if is_root_file:
            return file_path.name in self.config.ALLOWED_ROOT_FILES

        # 2. Validasi Blacklist
        if any(part in self.config.FORBIDDEN_DIRS for part in parts):
            return False
            
        # 3. Validasi Whitelist
        if not any(part in self.config.ALLOWED_DIRS for part in parts):
            return False

        # 4. Validasi Ekstensi
        return file_path.suffix in self.config.INCLUDE_EXTENSIONS

    def execute(self):
        if not self.target_path.is_dir():
            logger.error(f"❌ Error: Folder '{self.target_path}' tidak ditemukan.")
            return

        files_to_process: List[Path] = []
        original_size = 0
        
        logger.info("🔍 Menganalisa struktur arsitektur project Laravel (Strict Domain Mode)...")
        
        for file_path in self.target_path.rglob("*"):
            if not file_path.is_file():
                continue

            if self._should_process(file_path):
                try:
                    file_size = file_path.stat().st_size
                    
                    # Size Guard
                    if file_size > self.config.MAX_FILE_SIZE_BYTES:
                        logger.warning(f"[SKIPPED] {file_path.name} terlalu besar (>{self.config.MAX_FILE_SIZE_BYTES/1024:.0f}KB).")
                        continue
                    
                    # Binary Guard
                    if not self.optimizer.is_binary(file_path):
                        files_to_process.append(file_path)
                        original_size += file_size
                except Exception as e:
                    logger.error(f"[ERROR] Gagal memvalidasi {file_path.name}: {e}")

        # Tulis ke file output
        self._write_output(sorted(files_to_process), original_size)

    def _write_output(self, files: List[Path], original_size: int):
        total_compressed_size = 0
        
        with open(self.output_file, "w", encoding="utf-8") as f:
            f.write("=== STRUKTUR & ISI KODE (OPTIMIZED FOR LLM CONTEXT) ===\n\n")
            
            for file_path in files:
                relative_path = file_path.relative_to(self.target_path)
                try:
                    content = file_path.read_text("utf-8", errors="ignore")
                    compressed_content = self.optimizer.compress_code(content)
                    
                    f.write(f"\n--- FILE: {relative_path} ---\n")
                    f.write(compressed_content)
                    f.write("\n")
                    
                    total_compressed_size += len(compressed_content.encode('utf-8'))
                    logger.info(f"-> Memuat logika: {relative_path}")
                except Exception as e:
                    logger.error(f"-> Gagal membaca {relative_path}: {e}")

        logger.info(f"\n✅ Selesai! Hasil ekstraksi dioptimasi pada:\n   {self.output_file}")
        logger.info(f"📊 Ukuran Asli: {original_size / 1024:.2f} KB")
        logger.info(f"🚀 Ukuran Terkompresi (LLM Ready): {total_compressed_size / 1024:.2f} KB")
        logger.info(f"📉 Penghematan Token: ~{((original_size - total_compressed_size) / original_size) * 100:.1f}%")

if __name__ == "__main__":
    # Menggunakan Argparse agar dinamis (Best Practice)
    parser = argparse.ArgumentParser(description="LLM Context Extractor for Laravel/React SPA")
    parser.add_argument("--dir", type=str, default=os.getcwd(), help="Target directory (default: current dir)")
    parser.add_argument("--out", type=str, default="llm-context-optimized.txt", help="Output file name")
    
    args = parser.parse_args()
    
    # Eksekusi
    extractor = CodebaseExtractor(target_dir=args.dir, output_file=args.out)
    extractor.execute()