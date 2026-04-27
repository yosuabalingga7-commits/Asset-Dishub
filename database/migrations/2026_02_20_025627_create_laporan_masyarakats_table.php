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
            
            $table->string('ticket_number')->unique(); 
            $table->string('nama_pelapor');
            $table->string('kontak_pelapor'); 
            $table->string('judul_laporan'); 
            $table->text('deskripsi_keluhan');
            
            // PERBAIKAN: Ubah dari ENUM ke STRING agar bisa menerima "Pindah Tempat" dan pilihan lainnya
            $table->string('kondisi_aset')->default('Rusak');
            
            // Lokasi koordinat & Alamat (DITAMBAHKAN UNTUK KONSISTENSI CONTROLLER)
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            $table->string('alamat')->nullable(); // Tambahan: Agar tidak error di Controller index
            $table->string('lokasi_koordinat')->nullable(); 
            
            $table->string('foto')->nullable();
            
            // Status & Validasi (DITAMBAHKAN UNTUK ALUR KERJA)
            $table->string('status')->default('masuk'); // masuk, Proses Perbaikan, Selesai
            $table->boolean('is_validated')->default(false); 
            $table->string('kepemilikan')->nullable(); // dishub atau umum
            $table->text('catatan_admin')->nullable();
            
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