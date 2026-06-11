<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PengaduanSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan data lama agar tidak duplikat saat dijalankan ulang
        Schema::disableForeignKeyConstraints();
        DB::table('reports')->truncate();
        DB::table('report_logs')->truncate();
        Schema::enableForeignKeyConstraints();

        // 1. Data Pengaduan Contoh (Masyarakat)
        $lat1 = -6.8438;
        $lng1 = 107.4965;
        $reportId1 = DB::table('reports')->insertGetId([
            'ticket_number'     => 'TKT-20260303-001',
            'source'            => 'masyarakat',
            'nama_pelapor'      => 'Budi Santoso',
            'kontak_pelapor'    => '08123456789',
            'judul_laporan'     => 'Lampu PJU Padalarang Mati',
            'kondisi_aset'      => 'Rusak',
            'alamat'            => 'Jl. Raya Padalarang No. 123',
            'status'            => 'masuk',
            'deskripsi_keluhan' => 'Lampu jalan mati total sejak dua hari yang lalu, mohon segera diperbaiki karena gelap kalau malam.',
            'foto'              => 'pju_rusak.jpg',
            'coordinates'       => DB::raw("ST_GeomFromText('POINT($lng1 $lat1)', 4326)"),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // 2. Data Log Riwayat untuk Laporan Tersebut
        DB::table('report_logs')->insert([
            'report_id'  => $reportId1,
            'aksi'       => 'Laporan Masuk',
            'keterangan' => 'Laporan diterima oleh sistem dari masyarakat.',
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);
        
        // 3. Tambahkan satu lagi untuk contoh data yang sudah diproses (Masyarakat)
        $lat2 = -6.8732;
        $lng2 = 107.5458;
        DB::table('reports')->insert([
            'ticket_number'     => 'TKT-20260303-002',
            'source'            => 'masyarakat',
            'nama_pelapor'      => 'Siti Aminah',
            'judul_laporan'     => 'CCTV Simpang Cimahi Bermasalah',
            'kondisi_aset'      => 'Rusak',
            'alamat'            => 'Simpang Cimahi',
            'status'            => 'Proses Perbaikan',
            'deskripsi_keluhan' => 'Gambar blur dan sering mati sendiri.',
            'coordinates'       => DB::raw("ST_GeomFromText('POINT($lng2 $lat2)', 4326)"),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }
}