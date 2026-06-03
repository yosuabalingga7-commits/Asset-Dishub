<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Tambahkan 'petugas_lapangan' ke ENUM role
        // MEMPERTAHANKAN nilai yang sudah ada: 'admin', 'seksi', 'kadis'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'seksi', 'kadis', 'petugas_lapangan') NOT NULL DEFAULT 'seksi'");
    }

    public function down()
    {
        // Kembalikan ke ENUM semula (tanpa petugas_lapangan)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'seksi', 'kadis') NOT NULL DEFAULT 'seksi'");
    }
};