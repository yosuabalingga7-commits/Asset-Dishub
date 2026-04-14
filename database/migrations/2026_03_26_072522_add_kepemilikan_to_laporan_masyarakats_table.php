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
            /**
             * Menambahkan kolom kepemilikan.
             * Bagian ->after('jenis_aset') dihapus karena kolom tersebut 
             * tidak ditemukan di tabel laporan_masyarakats.
             */
            $table->string('kepemilikan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_masyarakats', function (Blueprint $table) {
            // Menghapus kolom jika migration di-rollback
            $table->dropColumn('kepemilikan');
        });
    }
};