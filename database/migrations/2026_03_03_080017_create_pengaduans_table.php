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
        Schema::create('pengaduans', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            
            // DATA PELAPOR
            $table->string('nama_pelapor');
            $table->string('nik')->nullable();
            $table->string('kontak_pelapor')->nullable();
            $table->string('whatsapp')->nullable();
            
            // DATA LAPORAN
            $table->string('judul_laporan');
            $table->string('kategori_aset')->nullable(); // Contoh: PJU
            $table->string('jenis_aset');                // Contoh: CCTV, Tiang
            $table->string('kondisi_aset');              // Rusak, Hilang, dll
            
            // LOKASI
            $table->string('lokasi'); 
            $table->text('alamat_manual')->nullable();
            $table->decimal('lat', 10, 8)->nullable();
            $table->decimal('lng', 11, 8)->nullable();
            
            // STATUS SISTEM
            // PERBAIKAN: Ubah dari ENUM ke STRING agar tidak 404/Error saat ada perbedaan Huruf Besar/Kecil
            $table->string('status')->default('masuk');

            // --- TAMBAHAN LOGIKA VALIDASI & KATEGORI ---
            // is_validated: true jika sudah divalidasi oleh admin
            $table->boolean('is_validated')->default(false); 
            // kategori_laporan: 'dishub' (aset internal) atau 'umum' (masyarakat)
            $table->string('kategori_laporan')->nullable(); 
            
            // DETAIL & ADMINISTRASI
            $table->text('deskripsi')->nullable();
            $table->text('catatan_admin')->nullable();
            $table->string('foto')->nullable();
            
            // INFORMASI PETUGAS / VALIDATOR
            $table->string('petugas_nama')->nullable();
            $table->string('petugas_nip')->nullable();
            $table->string('petugas_jabatan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaduans');
    }
};