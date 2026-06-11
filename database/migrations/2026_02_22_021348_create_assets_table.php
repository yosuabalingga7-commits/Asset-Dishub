<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk membuat tabel assets.
     */
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id(); 
            $table->string('id_asset')->unique(); // ID unik inventaris (misal: PJU-001)
            $table->string('nama');               // Nama lengkap aset
            $table->string('kategori');           // 5 Kategori Utama Dishub
            $table->string('jenis');              // Jenis detail (Lampu Surya, Rambu, dll)
            
            // Kolom baru untuk Ikon Map Dashboard
            $table->string('icon_marker')->default('📍'); 
            
            $table->string('merk')->nullable();
            $table->string('status')->default('Baik'); // Baik, Rusak, Kritis, Proses
            $table->text('alamat')->nullable();
            
            // Koordinat Geografis (PostGIS Geometry Point)
            $table->geometry('coordinates', subtype: 'point', srid: 4326);
            
            $table->string('foto')->nullable();    // Path file foto aset
            $table->date('tgl_pemasangan')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};