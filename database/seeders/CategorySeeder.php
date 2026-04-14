<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category; // Pastikan Model Category sudah dibuat
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data disesuaikan dengan permintaan Ketua (dengan angka jumlah tipe aset)
        $categories = [
            [
                'nama_kategori' => 'Penerangan Jalan Umum (PJU) (5)',
                'ikon_kategori' => '💡'
            ],
            [
                'nama_kategori' => 'Perlengkapan Jalan (4)',
                'ikon_kategori' => '🛑'
            ],
            [
                'nama_kategori' => 'Fasilitas Lalu Lintas (5)',
                'ikon_kategori' => '🛣️'
            ],
            [
                'nama_kategori' => 'Pengendalian & Pengawasan (4)',
                'ikon_kategori' => '📹'
            ],
            [
                'nama_kategori' => 'Prasarana Transportasi (2)',
                'ikon_kategori' => '🏢'
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['nama_kategori' => $cat['nama_kategori']], // Cek berdasarkan nama agar tidak duplikat
                [
                    // Slug akan otomatis jadi 'penerangan-jalan-umum-pju-5' dsb.
                    'slug' => Str::slug($cat['nama_kategori']),
                    'ikon_kategori' => $cat['ikon_kategori']
                ]
            );
        }
    }
}