<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapSetting extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit agar sinkron dengan migration
    protected $table = 'map_settings';

    // Kolom yang boleh diisi secara massal (Mass Assignment)
    protected $fillable = [
        'latitude',
        'longitude',
        'zoom',
    ];

    /**
     * Karena tabel ini hanya akan punya 1 baris data (pengaturan tunggal),
     * kita bisa membuat helper function jika nanti dibutuhkan.
     */
}