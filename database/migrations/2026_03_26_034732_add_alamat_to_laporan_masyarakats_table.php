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
        Schema::table('laporan_masyarakats', function (Blueprint $table) {
            // Menambah kolom 'alamat' dengan tipe text (karena alamat bisa panjang)
            // nullable() agar data lama yang sudah ada tidak error saat migrasi dijalankan
            // after('lng') agar posisi kolom berada setelah longitude
            $table->text('alamat')->nullable()->after('lng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_masyarakats', function (Blueprint $table) {
            // Menghapus kolom 'alamat' jika migrasi di-rollback
            $table->dropColumn('alamat');
        });
    }
};