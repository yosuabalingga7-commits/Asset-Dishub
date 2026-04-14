<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DishubSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tambah User (Jika belum ada dari DatabaseSeeder)
        $userId = DB::table('users')->insertGetId([
            'name' => 'Petugas Lapangan',
            'email' => 'petugas@dishub.go.id',
            'password' => bcrypt('password'),
            'created_at' => now(),
        ]);

        // 2. Tambah Asset
        $assetId = DB::table('assets')->insertGetId([
            'name' => 'Lampu PJU Padalarang 01',
            'type' => 'PJU',
            'location' => 'Jl. Raya Padalarang No. 123',
            'condition' => 'Rusak Ringan',
            'created_at' => now(),
        ]);

        // 3. Tambah Tiket (Gunakan ID String sesuai migrasi kamu)
        DB::table('tickets')->insert([
            'id' => 'MTC-2026-001',
            'asset_id' => $assetId,
            'description' => 'Lampu mati total sejak tadi malam.',
            'priority' => 'Tinggi',
            'status' => 'Ditugaskan',
            'assigned_to' => $userId,
            'assigned_at' => now(),
            'created_at' => now(),
        ]);
    }
}