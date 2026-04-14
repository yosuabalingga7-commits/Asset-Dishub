<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pengaduan;
use App\Models\PengaduanLog;
use Illuminate\Support\Facades\DB;

class PengaduanSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan data lama agar tidak duplikat saat dijalankan ulang
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Pengaduan::truncate();
        PengaduanLog::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Data Pengaduan Contoh
        $laporan = Pengaduan::create([
            'ticket_number'  => 'TKT-20260303-001',
            'nama_pelapor'   => 'Budi Santoso',
            'nik'            => '3201234567890001',
            'kontak_pelapor' => '08123456789',
            'whatsapp'       => '628123456789',
            'judul_laporan'  => 'Lampu PJU Padalarang Mati',
            'jenis_aset'     => 'Penerangan Jalan Umum (PJU)',
            'kondisi_aset'   => 'Rusak',
            'lokasi'         => 'Jl. Raya Padalarang No. 123',
            'alamat_manual'  => 'Dekat Jembatan layang, samping toko kelontong.',
            'status'         => 'masuk',
            'deskripsi'      => 'Lampu jalan mati total sejak dua hari yang lalu, mohon segera diperbaiki karena gelap kalau malam.',
            'lat'            => -6.8438,
            'lng'            => 107.4965,
            'foto'           => 'pju_rusak.jpg',
        ]);

        // 2. Data Log Riwayat untuk Laporan Tersebut
        PengaduanLog::create([
            'pengaduan_id' => $laporan->id,
            'aksi'         => 'Laporan Masuk',
            'keterangan'   => 'Laporan diterima oleh sistem dari masyarakat.',
            'created_at'   => now()->subHours(2),
        ]);
        
        // Tambahkan satu lagi untuk contoh data yang sudah diproses
        $laporan2 = Pengaduan::create([
            'ticket_number'  => 'TKT-20260303-002',
            'nama_pelapor'   => 'Siti Aminah',
            'judul_laporan'  => 'CCTV Simpang Cimahi Bermasalah',
            'jenis_aset'     => 'CCTV',
            'kondisi_aset'   => 'Rusak',
            'lokasi'         => 'Simpang Cimahi',
            'status'         => 'Proses Perbaikan',
            'deskripsi'      => 'Gambar blur dan sering mati sendiri.',
            'lat'            => -6.8732,
            'lng'            => 107.5458,
        ]);
    }
}