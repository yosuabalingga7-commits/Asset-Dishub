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
            // Kita hapus baris 'kepemilikan' karena sudah ada di database dari migrasi sebelumnya
            
            // Tambahkan is_validated
            $table->boolean('is_validated')->default(false)->after('status');
            
            // Tambahkan catatan_admin
            $table->text('catatan_admin')->nullable()->after('is_validated');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_masyarakats', function (Blueprint $table) {
            // Hanya hapus kolom yang benar-benar dibuat di file ini
            $table->dropColumn(['is_validated', 'catatan_admin']);
        });
    }
};