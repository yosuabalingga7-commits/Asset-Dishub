<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seksi extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database.
     * Secara default Laravel akan mencari 'seksis', 
     * tapi kita tegaskan di sini agar lebih aman.
     */
    protected $table = 'seksis';

    /**
     * Kolom yang boleh diisi secara massal (Mass Assignment).
     */
    protected $fillable = [
        'nama_seksi',
        'keterangan',
    ];

    /**
     * Relasi ke User (One-to-Many)
     * Satu Seksi memiliki banyak anggota User (bidang tugas).
     * * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): HasMany
    {
        // Menghubungkan ke model User menggunakan foreign key 'seksi_id'
        return $this->hasMany(User::class, 'seksi_id');
    }
}