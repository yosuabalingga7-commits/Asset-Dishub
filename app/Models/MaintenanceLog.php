<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    use HasFactory;

    // Nama tabel (opsional jika sudah jamak, tapi aman untuk didefinisikan)
    protected $table = 'maintenance_logs';

    // Kolom yang boleh diisi secara massal
    // Menambahkan status_after dan progress_percent agar sinkron dengan dashboard
    protected $fillable = [
        'ticket_id', 
        'user_id',
        'action', 
        'note', 
        'photo_evidence', 
        'status_after', 
        'progress_percent'
    ];

    /**
     * Relasi ke Tiket Maintenance
     * Log ini mencatat riwayat dari satu tiket tertentu
     */
    public function ticket()
    {
        return $this->belongsTo(MaintenanceTicket::class, 'ticket_id');
    }

    /**
     * Relasi ke User (Petugas/Teknisi)
     * Mengetahui siapa yang melakukan update/tindakan ini
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Helper untuk mendapatkan URL foto bukti pengerjaan
     */
    public function getPhotoUrlAttribute()
    {
        return $this->photo_evidence 
            ? asset('storage/' . $this->photo_evidence) 
            : asset('images/no-image.png');
    }
}