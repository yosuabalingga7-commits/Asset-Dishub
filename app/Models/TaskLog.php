<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'user_id', 'action_type', 
        'status_from', 'status_to', 'note', 'attachment_path'
    ];

    // Relasi balik ke Tiket
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Relasi ke User (Siapa yang menginput log)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}