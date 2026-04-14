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
        // PENTING: Nama tabel harus 'laporan_masyarakats' (pakai 's') agar sinkron dengan Laravel
        Schema::create('laporan_masyarakats', function (Blueprint $table) {
            $table->id();
            
            // Urutan otomatis setelah ID, tidak perlu pakai ->after()
            $table->string('ticket_number')->unique(); 
            
            $table->string('nama_pelapor');
            $table->string('kontak_pelapor'); 
            $table->string('judul_laporan'); 
            $table->text('deskripsi_keluhan');
            
            // PERBAIKAN: Ubah dari ENUM ke STRING agar bisa menerima "Pindah Tempat" dan pilihan lainnya
            $table->string('kondisi_aset')->default('Rusak');
            
            // Lokasi koordinat
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->string('lokasi_koordinat')->nullable(); 
            
            $table->string('foto')->nullable();
            
            // Status Laporan (Tetap String agar lebih fleksibel)
            $table->string('status')->default('masuk');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_masyarakats');
    }
};