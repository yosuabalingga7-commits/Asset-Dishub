<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('map_settings', function (Blueprint $table) {
            $table->id();
            // Menggunakan double agar lebih fleksibel dibanding decimal yang kaku
            $table->double('latitude')->default(-6.8431);
            $table->double('longitude')->default(107.4912);
            $table->integer('zoom')->default(11);
            $table->timestamps();
        });

        // Masukkan data default pertama kali agar ID 1 selalu ada
        DB::table('map_settings')->insert([
            'latitude' => -6.8431,
            'longitude' => 107.4912,
            'zoom' => 11,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_settings');
    }
};