<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportLog extends Model
{
    use HasFactory;

    protected $table = 'report_logs';

    protected $fillable = [
        'report_id',
        'user_id',
        'aksi',
        'keterangan'
    ];

    /**
     * Relasi balik ke Report
     */
    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    /**
     * Relasi ke User (Admin/Petugas yang melakukan update)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}