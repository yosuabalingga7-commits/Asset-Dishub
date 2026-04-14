<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaintenanceSeeder extends Seeder
{
    public function run()
    {
        // Ganti 'maintenance_tickets' menjadi 'tickets' sesuai hasil migrasi kamu
        DB::table('tickets')->insert([
            [
                'asset_id' => 1, 
                'priority' => 'Tinggi',
                'status' => 'Menunggu',
                'description' => 'Lampu PJU padam di area simpang Padalarang',
                'reporter_name' => 'Budi Santoso',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'asset_id' => 2,
                'priority' => 'Sedang',
                'status' => 'Dalam Proses',
                'description' => 'CCTV mati total terkena petir',
                'reporter_name' => 'Siti Aminah',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'asset_id' => 3,
                'priority' => 'Rendah',
                'status' => 'Selesai',
                'description' => 'Pembersihan rutin unit traffic light',
                'reporter_name' => 'Andi Wijaya',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        $this->command->info('Data Tiket Maintenance berhasil ditanam!');
    }
}