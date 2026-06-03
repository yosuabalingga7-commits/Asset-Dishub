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
        Schema::table('assets', function (Blueprint $table) {
            // Menambahkan kolom foto dan catatan untuk rekam medis aset perbaikan
            // Diletakkan setelah kolom 'status' agar posisi di database teratur
            $table->string('foto_terakhir')->nullable()->after('status');
            $table->text('catatan_terakhir')->nullable()->after('foto_terakhir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback
            $table->dropColumn(['foto_terakhir', 'catatan_terakhir']);
        });
    }
};