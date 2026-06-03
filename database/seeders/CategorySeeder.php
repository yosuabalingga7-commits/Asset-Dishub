<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category; 
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menggunakan Icon Font Awesome (Real Icon) bukan emoji
        $categories = [
            [
                'nama_kategori' => 'Penerangan Jalan Umum (PJU) (5)',
                'ikon_kategori' => 'fa-lightbulb' 
            ],
            [
                'nama_kategori' => 'Perlengkapan Jalan (4)',
                'ikon_kategori' => 'fa-road' 
            ],
            [
                'nama_kategori' => 'Fasilitas Lalu Lintas (5)',
                'ikon_kategori' => 'fa-traffic-light' 
            ],
            [
                'nama_kategori' => 'Pengendalian & Pengawasan (4)',
                'ikon_kategori' => 'fa-video' 
            ],
            [
                'nama_kategori' => 'Prasarana Transportasi (2)',
                'ikon_kategori' => 'fa-building' 
            ],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(
                ['nama_kategori' => $cat['nama_kategori']], 
                [
                    'slug' => Str::slug($cat['nama_kategori']),
                    'ikon_kategori' => $cat['ikon_kategori']
                ]
            );
        }
    }
}