<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Membuat tabel maintenance_tickets sesuai logika di MaintenanceController
        Schema::create('maintenance_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique(); // Contoh: MNT-001
            $table->string('category')->default('dishub'); // dishub atau umum
            
            // --- KOLOM BARU UNTUK RELASI SEKSI (PENERIMA TUGAS) ---
            $table->foreignId('seksi_id')->nullable()->constrained('users')->onDelete('cascade');

            // Kolom tambahan agar sinkron dengan Model & Controller (SANGAT PENTING)
            $table->string('kepemilikan')->nullable(); // Menentukan sumber aset
            $table->string('subject')->nullable(); // Judul perbaikan
            $table->string('jenis_aset')->nullable(); // Jenis aset (CCTV, PJU, dll)
            $table->text('edit_reason')->nullable(); // Alasan perubahan data
            $table->date('deadline')->nullable(); // Batas waktu pengerjaan

            // Relasi (Ditingkatkan dengan foreignId agar relasi database aktif)
            // asset_id merujuk ke tabel assets (untuk ASET DISHUB)
            $table->foreignId('asset_id')->nullable()->constrained('assets')->onDelete('cascade');
            
            // --- PERBAIKAN DI SINI ---
            // Kita gunakan kolom biasa tanpa constrained ke tabel 'categories' agar tidak error errno: 150
            $table->unsignedBigInteger('category_id')->nullable(); 
            
            // PERBAIKAN: report_id merujuk ke tabel laporan_masyarakats (sesuai file migrasi Ketua)
            $table->foreignId('report_id')->nullable()->constrained('laporan_masyarakats')->onDelete('cascade');
            
            // Data Maintenance
            $table->string('priority')->default('normal'); // rendah, normal, tinggi, kritis
            $table->string('status')->default('pending');  // pending, process, finished, rejected
            
            // Lokasi & Deskripsi
            $table->string('location_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('description')->nullable();
            
            // Kolom Hasil Pengerjaan (PENTING UNTUK FITUR SELESAI)
            $table->string('foto_perbaikan')->nullable(); // Menyimpan path foto bukti selesai
            $table->text('completion_notes')->nullable(); // Menyimpan catatan teknis penyelesaian
            
            // Petugas
            $table->string('technician_name')->nullable();
            // Relasi ke tabel users untuk petugas/admin yang menangani
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Timeline
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_tickets');
    }
};