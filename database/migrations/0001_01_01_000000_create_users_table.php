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
        // Tabel Utama Users - Update: Penambahan kolom status & password_plain
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('nip')->unique(); // Login menggunakan NIP
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('password_plain')->nullable(); // UNTUK ADMIN MELIHAT PASSWORD ASLI
            
            /** * ROLE MANAGEMENT
             * admin: Manajemen penuh aset & buat tiket/tugas.
             * seksi: Monitoring & eksekusi tugas per bidang.
             * kadis: Kepala Dinas (view only untuk dashboard eksekutif)
             */
            $table->enum('role', ['admin', 'seksi', 'kadis'])->default('seksi');
            
            // --- IDENTITAS SEKSI (WAJIB UNTUK PEMBAGIAN TUGAS) ---
            $table->unsignedBigInteger('seksi_id')->nullable(); 

            // Integrasi WhatsApp untuk bot notifikasi tugas
            $table->string('no_wa')->unique()->nullable(); 
            
            // Profil & Status
            $table->string('foto')->nullable(); 
            $table->string('status')->default('aktif'); // KOLOM BARU: Untuk indikator warna di tabel (Aktif/Nonaktif)
            $table->boolean('is_active')->default(true);
            
            // Profile Photo Path
            $table->string('profile_photo_path', 2048)->nullable();
            
            // Last Login IP Address untuk logging aktivitas
            $table->string('last_login_ip', 45)->nullable();
            
            // Last Login Time
            $table->timestamp('last_login_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        // Tabel Token Reset Password
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Tabel Sesi Login
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};