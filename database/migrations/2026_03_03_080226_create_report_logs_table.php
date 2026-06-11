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
        Schema::create('report_logs', function (Blueprint $table) {
            $table->id();
            
            /** * Relasi ke tabel reports (Laporan Utama)
             * Menggunakan index() agar pencarian riwayat laporan yang datanya ribuan tetap cepat.
             */
            $table->foreignId('report_id')
                  ->index() 
                  ->constrained('reports')
                  ->onDelete('cascade');

            /** * Relasi ke tabel users (Admin/Petugas yang memproses)
             * nullable() jika sistem yang melakukan log otomatis tanpa login user.
             */
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->string('aksi'); // Contoh: 'Laporan Masuk', 'Update Status', 'Penyelesaian'
            $table->text('keterangan')->nullable(); // Detail apa yang diubah
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_logs');
    }
};