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
        'role',       // admin, seksi, kadis, petugas_lapangan
        'seksi_id',   // ID Seksi untuk pembagian tugas
        'status',     // TAMBAHKAN INI AGAR BISA DISIMPAN
        'is_active',
        'profile_photo_path', // TAMBAHKAN INI UNTUK FOTO PROFIL
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
            'is_active' => 'boolean',
            'seksi_id' => 'integer',
        ];
    }

    // --- HELPER FUNCTIONS UNTUK ROLE ---

    public function hasRole($role): bool
    {
        return $this->role === $role;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSeksi(): bool
    {
        return $this->role === 'seksi';
    }

    /**
     * Cek apakah user adalah Kepala Dinas (Kadis)
     */
    public function isKadis(): bool
    {
        return $this->role === 'kadis';
    }

    /**
     * Cek apakah user adalah Petugas Lapangan
     */
    public function isPetugasLapangan(): bool
    {
        return $this->role === 'petugas_lapangan';
    }

    public function seksi(): BelongsTo
    {
        return $this->belongsTo(Seksi::class, 'seksi_id');
    }

    public function maintenanceTickets(): HasMany
    {
        return $this->hasMany(MaintenanceTicket::class, 'user_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class);
    }
}