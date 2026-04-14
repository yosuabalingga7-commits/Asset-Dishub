<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk menambah kolom category_id.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Kita tambahkan kolom category_id setelah kolom id
            // constrained('categories') memastikan id ini harus ada di tabel categories
            // onDelete('set null') artinya jika kategori dihapus, aset tidak ikut terhapus, hanya kategorinya jadi kosong
            $table->foreignId('category_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('categories')
                  ->onDelete('set null');
        });
    }

    /**
     * Batalkan migrasi (Rollback).
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Hapus foreign key dulu baru hapus kolomnya
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};