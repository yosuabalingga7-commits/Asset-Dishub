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
        'user_id',
        'seksi_id',
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

    public function report()
    {
        return $this->belongsTo(LaporanMasyarakat::class, 'report_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withDefault([
            'name' => 'Belum Ditentukan'
        ]);
    }

    public function seksi()
    {
        // PERBAIKAN: Relasi harus ke Model Seksi, bukan User
        return $this->belongsTo(Seksi::class, 'seksi_id')->withDefault([
            'nama_seksi' => 'Belum Ditugaskan'
        ]);
    }

    public function logs()
    {
        return $this->hasMany(MaintenanceLog::class, 'ticket_id');
    }

    public function isDishub()
    {
        return strtoupper($this->kepemilikan) === 'DISHUB' || strtolower($this->category) === 'dishub';
    }

    public function isUmum()
    {
        return !$this->isDishub();
    }
}