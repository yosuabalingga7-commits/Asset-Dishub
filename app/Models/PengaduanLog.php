<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaduanLog extends Model
{
    use HasFactory;

    // Nama tabel sesuai hasil migrate:fresh tadi
    protected $table = 'pengaduan_logs';

    // Kolom yang boleh diisi secara massal
    protected $fillable = [
        'pengaduan_id',
        'user_id',
        'aksi',
        'keterangan'
    ];

    /**
     * Relasi balik ke Pengaduan
     * Satu log merujuk pada satu data pengaduan tertentu
     */
    public function pengaduan()
    {
        return $this->belongsTo(Pengaduan::class, 'pengaduan_id');
    }

    /**
     * Relasi ke User (Admin/Petugas yang melakukan update)
     * Ini yang dicari oleh Controller saat memanggil ->load(['logs.user'])
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}