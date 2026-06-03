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
        Schema::create('pengumumen', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 255);
            $table->text('isi');
            $table->enum('status', ['aktif', 'arsip'])->default('aktif');
            $table->enum('target', ['semua', 'petugas_lapangan', 'kepala_seksi', 'admin'])->default('semua');
            $table->boolean('penting')->default(false);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('lampiran', 255)->nullable();
            $table->enum('jenis', [
                'perubahan_layanan',
                'info_operasional',
                'kebijakan_baru',
                'instruksi_petugas',
                'info_proyek',
                'surat_edaran'
            ])->default('info_operasional');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            // Foreign key ke tabel users
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
            
            // Index untuk optimasi query
            $table->index('status');
            $table->index('target');
            $table->index('penting');
            $table->index('jenis');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumumen');
    }
};