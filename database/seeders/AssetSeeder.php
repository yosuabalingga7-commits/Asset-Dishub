<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        // Supaya tidak error saat mengosongkan tabel yang punya relasi
        Schema::disableForeignKeyConstraints();
        Asset::truncate();
        Schema::enableForeignKeyConstraints();

        $assets = [
            [
                'id_asset' => 'AST-PJU001',
                'nama' => 'PJU LED Philips 120W - Padalarang',
                // DISAMAKAN: Harus persis dengan nama di CategorySeeder
                'kategori' => 'Penerangan Jalan Umum (PJU) (5)',
                'jenis' => 'Lampu PJU LED',
                'merk' => 'Philips',
                'status' => 'Baik',
                'tgl_pemasangan' => '2023-05-10',
                'lat' => -6.8350,
                'lng' => 107.4600,
                'alamat' => 'Jl. Raya Padalarang KM 15',
                'icon_marker' => '💡',
                'catatan' => 'Kondisi tiang kokoh'
            ],
            [
                'id_asset' => 'AST-RLJ002',
                'nama' => 'Rambu Simpang Cimareme',
                'kategori' => 'Perlengkapan Jalan (4)',
                'jenis' => 'Rambu Larangan',
                'merk' => '3M Reflection',
                'status' => 'Rusak',
                'tgl_pemasangan' => '2022-11-20',
                'lat' => -6.8600,
                'lng' => 107.5100,
                'alamat' => 'Pertigaan Cimareme',
                'icon_marker' => '🛑',
                'catatan' => 'Panel rambu memudar'
            ],
            [
                'id_asset' => 'AST-FLL003',
                'nama' => 'Zebra Cross Depan Kantor Bupati',
                'kategori' => 'Fasilitas Lalu Lintas (5)',
                'jenis' => 'Marka Zebra Cross',
                'merk' => 'Thermoplastic Paint',
                'status' => 'Proses Perbaikan',
                'tgl_pemasangan' => '2024-01-15',
                'lat' => -6.8250,
                'lng' => 107.4800,
                'alamat' => 'Mekarsari, Ngamprah',
                'icon_marker' => '🛣️',
                'catatan' => 'Sedang pengecatan ulang'
            ],
            [
                'id_asset' => 'AST-WAS004',
                'nama' => 'CCTV Monitoring Simpang Tagog',
                'kategori' => 'Pengendalian & Pengawasan (4)', 
                'jenis' => 'CCTV Surveilans',
                'merk' => 'Hikvision',
                'status' => 'Baik',
                'tgl_pemasangan' => '2023-08-05',
                'lat' => -6.8400,
                'lng' => 107.4700,
                'alamat' => 'Pasar Tagog Padalarang',
                'icon_marker' => '📹',
                'catatan' => 'Koneksi FO stabil'
            ],
            [
                'id_asset' => 'AST-PRA005',
                'nama' => 'Halte Bus Gadobangkong',
                'kategori' => 'Prasarana Transportasi (2)',
                'jenis' => 'Halte Bus',
                'merk' => 'Lokal KBB',
                'status' => 'Kritis',
                'tgl_pemasangan' => '2020-02-14',
                'lat' => -6.8550,
                'lng' => 107.5200,
                'alamat' => 'Gadobangkong, Ngamprah',
                'icon_marker' => '🏢',
                'catatan' => 'Atap bocor dan banyak coretan'
            ]
        ];

        foreach ($assets as $asset) {
            Asset::create($asset);
        }

        $this->command->info('Data Aset Dishub KBB Berhasil Ditanam!');
    }
}