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
        Schema::create('maintenance_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique(); 
            $table->string('category')->default('dishub'); 
            
            // FIX: Relasi Seksi harus merujuk ke tabel seksis, bukan users
            $table->foreignId('seksi_id')->nullable()->constrained('seksis')->onDelete('cascade');

            $table->string('kepemilikan')->nullable(); 
            $table->string('subject')->nullable(); 
            $table->string('jenis_aset')->nullable(); 
            $table->text('edit_reason')->nullable(); 
            $table->date('deadline')->nullable(); 

            // Relasi Assets
            $table->foreignId('asset_id')->nullable()->constrained('assets')->onDelete('cascade');
            
            $table->unsignedBigInteger('category_id')->nullable(); 
            
            // Relasi Report (Pastikan tabel laporan_masyarakats sudah dibuat sebelumnya)
            $table->foreignId('report_id')->nullable()->constrained('laporan_masyarakats')->onDelete('cascade');
            
            $table->string('priority')->default('normal'); 
            $table->string('status')->default('pending');  
            
            $table->string('location_address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->text('description')->nullable();
            
            $table->string('foto_perbaikan')->nullable(); 
            $table->text('completion_notes')->nullable(); 
            
            $table->string('technician_name')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_tickets');
    }
};