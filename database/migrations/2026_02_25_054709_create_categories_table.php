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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            // Nama Kategori: "Penerangan Jalan Umum (PJU)", "Pengendalian & Pengawasan", dll.
            $table->string('nama_kategori')->unique(); 
            
            // Slug: "penerangan-jalan-umum", "pengendalian-pengawasan" (untuk filter yang lebih stabil)
            $table->string('slug')->unique(); 
            
            // Ikon: Untuk simpan emoji (💡, 📹) atau nama icon font-awesome
            $table->string('ikon_kategori')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};