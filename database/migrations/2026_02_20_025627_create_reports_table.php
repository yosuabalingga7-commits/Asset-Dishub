<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan ekstensi PostGIS diaktifkan terlebih dahulu
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis;');

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            
            $table->string('ticket_number')->unique()->nullable();
            $table->enum('source', ['masyarakat', 'petugas']);

            // Data Pelapor (Masyarakat)
            $table->string('nama_pelapor')->nullable();
            $table->string('kontak_pelapor')->nullable();

            // Data Petugas
            $table->string('nama_petugas')->nullable();
            $table->string('nip')->nullable();
            $table->string('no_wa')->nullable();

            // Detail Laporan
            $table->string('judul_laporan'); 
            $table->text('deskripsi_keluhan')->nullable(); // Untuk masyarakat
            $table->text('deskripsi')->nullable();        // Untuk petugas / umum
            $table->string('kondisi_aset')->default('Rusak');
            $table->string('alamat')->nullable();
            $table->string('lokasi_koordinat')->nullable();
            $table->string('foto')->nullable();
            
            // Status & Validasi
            $table->string('status')->default('masuk'); // masuk, Proses Perbaikan, Selesai
            $table->boolean('is_validated')->default(false); 
            $table->string('kepemilikan')->nullable(); // Dishub atau Pihak Ke-3
            $table->text('catatan_admin')->nullable();
            
            // Relasi ke Aset (Tanpa foreign key numerik di sini karena tabel assets belum dibuat)
            $table->string('id_asset')->nullable(); // Kode Aset (misal: AST-PJU001)

            // Koordinat Geografis (PostGIS Geometry Point)
            $table->geometry('coordinates', subtype: 'point', srid: 4326)->nullable();

            // IP Address untuk logging aktivitas
            $table->string('ip_address', 45)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};