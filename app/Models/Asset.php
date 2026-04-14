<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    protected $fillable = [
        'category_id', // <-- TAMBAHAN: Kolom baru untuk relasi (Cara 2)
        'id_asset', 
        'nama', 
        'kategori', 
        'jenis', 
        'icon_marker', 
        'merk', 
        'status', 
        'tgl_pemasangan', 
        'lat', 
        'lng', 
        'alamat', 
        'foto', 
        'catatan'
    ];

    protected $casts = [
        'lat' => 'double',
        'lng' => 'double',
        'tgl_pemasangan' => 'date', 
    ];

    protected $appends = ['foto_url'];

    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }
        return 'https://via.placeholder.com/800x400?text=No+Image';
    }

    public function setIdAssetAttribute($value)
    {
        $this->attributes['id_asset'] = strtoupper($value);
    }

    // --- TAMBAHAN FUNGSI BARU ---

    /**
     * Relasi ke Model Category
     * Menghubungkan Asset ke tabel Categories berdasarkan category_id
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}