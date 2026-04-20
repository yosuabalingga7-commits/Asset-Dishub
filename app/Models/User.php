<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'name',
        'nip',        // Login via NIP (Unique)
        'email',
        'password',
        'password_plain', // TAMBAHKAN INI
        'no_wa',      // Digunakan untuk Bot WhatsApp
        'foto',        
        'role',       // super_admin, seksi
        'seksi_id',   // ID Seksi untuk pembagian tugas
        'status',     // TAMBAHKAN INI AGAR BISA DISIMPAN
        'is_active',
    ];

    /**
     * Atribut yang disembunyikan saat serialisasi.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting atribut ke tipe data tertentu.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'seksi_id' => 'integer',
        ];
    }

    // --- HELPER FUNCTIONS UNTUK ROLE ---

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isSeksi(): bool
    {
        return $this->role === 'seksi';
    }

    /**
     * Relasi ke model Seksi (Bidang)
     */
    public function seksi(): BelongsTo
    {
        return $this->belongsTo(Seksi::class, 'seksi_id');
    }

    /**
     * Relasi ke Tiket Maintenance
     */
    public function maintenanceTickets(): HasMany
    {
        return $this->hasMany(MaintenanceTicket::class, 'user_id');
    }

    /**
     * Relasi ke Task Logs
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class);
    }
}