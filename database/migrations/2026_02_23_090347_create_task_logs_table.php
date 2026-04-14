<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_logs', function (Blueprint $table) {
            $table->id();
            
            // PERBAIKAN: Nama tabel tujuan diganti dari 'tickets' menjadi 'maintenance_tickets'
            // agar sinkron dengan file migrasi 2026_02_22_093242_create_maintenance_tables.php
            $table->foreignId('ticket_id')->constrained('maintenance_tickets')->onDelete('cascade');
            
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action_type'); 
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();
            $table->text('note')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_logs');
    }
};