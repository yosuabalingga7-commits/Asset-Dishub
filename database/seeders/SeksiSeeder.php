<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seksi;
use Illuminate\Support\Facades\DB;

class SeksiSeeder extends Seeder
{
    public function run(): void
    {
        $seksis = [
            ['nama_seksi' => 'Perlengkapan Jalan'],
            ['nama_seksi' => 'Lalu Lintas'],
            ['nama_seksi' => 'Angkutan Umum'],
            ['nama_seksi' => 'Parkir dan Retribusi'],
            ['nama_seksi' => 'Pengujian Kendaraan'],
            ['nama_seksi' => 'Manajemen Rekayasa Lalu Lintas'],
        ];

        foreach ($seksis as $seksi) {
            Seksi::updateOrCreate(
                ['nama_seksi' => $seksi['nama_seksi']],
                $seksi
            );
        }
    }
}