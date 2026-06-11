<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MaintenanceTicket extends Model
{
    use HasFactory;

    protected $table = 'maintenance_tickets';

    protected $fillable = [
        'ticket_code',
        'report_id',
        'asset_id',
        'user_id',          // Penting: Untuk menyimpan ID petugas spesifik
        'seksi_id',         // Penting: Untuk menyimpan ID seksi/bidang
        'category',
        'kepemilikan',
        'subject',
        'description',
        'location_address',
        'latitude',
        'longitude',
        'status',
        'priority',
        'technician_name',
        'started_at',
        'finished_at',
        'edit_reason',
        'deadline',
        'category_id',
        'jenis_aset',
        'foto_perbaikan',   
        'completion_notes', 
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
        'deadline'    => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Otomatis mengubah status string menjadi slug untuk keperluan CSS/Badge
     */
    public function getStatusSlugAttribute()
    {
        $status = strtolower($this->status);

        if (Str::contains($status, ['baik', 'bagus', 'normal'])) {
            return 'baik';
        }
        if (Str::contains($status, ['proses', 'perbaikan', 'progres', 'jalan'])) {
            return 'proses';
        }
        if (Str::contains($status, ['rusak', 'mati', 'error'])) {
            return 'rusak';
        }
        if (Str::contains($status, ['kritis', 'parah', 'darurat'])) {
            return 'kritis';
        }

        return $status;
    }

    /**
     * Relasi ke Laporan Masyarakat
     */
    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    /**
     * Relasi ke Data Aset
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Relasi ke Petugas (User)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault([
            'name' => 'Belum Ditentukan'
        ]);
    }

    /**
     * Relasi ke Bidang/Seksi
     */
    public function seksi()
    {
        // Menghubungkan ke model Seksi (pastikan Model Seksi sudah ada)
        return $this->belongsTo(Seksi::class, 'seksi_id')->withDefault([
            'nama_seksi' => 'Belum Ditugaskan'
        ]);
    }

    /**
     * Relasi ke Log Aktivitas Tiket
     */
    public function logs()
    {
        return $this->hasMany(MaintenanceLog::class, 'ticket_id');
    }

    /**
     * Cek apakah aset milik Dishub
     */
    public function isDishub()
    {
        return strtoupper($this->kepemilikan) === 'DISHUB' || strtolower($this->category) === 'dishub';
    }

    /**
     * Cek apakah aset milik Umum/Pihak 3
     */
    public function isUmum()
    {
        return !$this->isDishub();
    }
}