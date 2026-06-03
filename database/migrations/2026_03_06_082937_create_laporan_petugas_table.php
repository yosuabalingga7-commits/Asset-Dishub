<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan perintah untuk membuat tabel di database.
     * Tabel ini dirancang untuk menampung laporan teknis dari petugas internal Dishub.
     */
    public function up(): void
    {
        Schema::create('laporan_petugas', function (Blueprint $table) {
            $table->id();
            
            // --- 01: Identitas Petugas ---
            $table->string('nama_petugas');
            $table->string('nip')->index(); // Index ditambahkan untuk relasi ke tabel users
            $table->string('no_wa');
            
            // --- 02: Detail Temuan Aset ---
            $table->string('judul_laporan'); // Contoh: Perbaikan PJU Ruas Ciburuy
            $table->string('kondisi_aset');  // Pilihan: Rusak, Hilang, Pindah Tempat, Lainnya
            
            /**
             * Kolom Deskripsi (TAMBAHAN BARU)
             * Menggunakan tipe text agar petugas bisa menjelaskan kronologi secara detail.
             */
            $table->text('deskripsi')->nullable(); 
            
            $table->string('foto'); // Menyimpan nama file/path foto dokumentasi
            
            // --- 03: Lokasi Geografis (GIS) ---
            // Menggunakan precision yang akurat untuk pemetaan Google Maps/Leaflet
            $table->decimal('lat', 10, 8); 
            $table->decimal('lng', 11, 8); 
            
            // --- 04: Status Alur Kerja ---
            // Secara default laporan yang baru masuk berstatus 'masuk'
            $table->string('status')->default('masuk'); // masuk, proses, selesai
            
            // ID Aset (bisa NULL jika stiker hilang)
            $table->string('id_asset')->nullable();
            
            // Sumber laporan
            $table->string('sumber_laporan')->default('petugas');
            
            // Kode Tiket
            $table->string('ticket_number')->unique()->nullable();
            
            // IP Address untuk logging aktivitas
            $table->string('ip_address', 45)->nullable();
            
            // --- 05: Audit Timestamps ---
            $table->timestamps(); // Mencatat created_at dan updated_at secara otomatis
        });
    }

    /**
     * Batalkan pembuatan tabel (Rollback).
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_petugas');
    }
};