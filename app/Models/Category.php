<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database
     */
    protected $table = 'categories';

    /**
     * Kolom yang dapat diisi melalui mass assignment.
     */
    protected $fillable = [
        'nama_kategori',
        'slug',
        'ikon_kategori'
    ];

    /**
     * Relasi ke Asset
     * Menghubungkan kategori ke tabel assets melalui category_id.
     * Kunci agar angka di dashboard tidak 0.
     */
    public function assets()
    {
        // Pastikan nama modelnya adalah Asset (singular)
        return $this->hasMany(Asset::class, 'category_id');
    }

    /**
     * Boot function untuk otomatis membuat slug jika tidak diisi.
     * Ini opsional tapi sangat membantu agar data slug selalu konsisten.
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->nama_kategori);
            }
        });
    }
}