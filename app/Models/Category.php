<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'nama_kategori',
        'slug',
        'ikon_kategori'
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    // RELASI BARU: Menghubungkan Kategori ke Jenis/Tipe Aset
    public function assetTypes()
    {
        return $this->hasMany(AssetType::class, 'category_id');
    }

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