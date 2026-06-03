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
            // Menambahkan kolom relasi ke tabel assets
            // Kita taruh setelah kolom 'id' agar rapi
            $table->foreignId('asset_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('assets')
                  ->onDelete('set null'); // Jika aset dihapus, kolom ini jadi NULL, laporan tetap ada
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_masyarakats', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu sebelum menghapus kolom
            $table->dropForeign(['asset_id']);
            $table->dropColumn('asset_id');
        });
    }
};