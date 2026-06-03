<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan_masyarakats', function (Blueprint $table) {
            if (!Schema::hasColumn('laporan_masyarakats', 'id_asset')) {
                $table->string('id_asset')->nullable()->after('status');
            }
            if (!Schema::hasColumn('laporan_masyarakats', 'sumber_laporan')) {
                $table->string('sumber_laporan')->default('masyarakat')->after('id_asset');
            }
            if (!Schema::hasColumn('laporan_masyarakats', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('sumber_laporan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('laporan_masyarakats', function (Blueprint $table) {
            $table->dropColumn('id_asset');
            $table->dropColumn('sumber_laporan');
            $table->dropColumn('ip_address');
        });
    }
};