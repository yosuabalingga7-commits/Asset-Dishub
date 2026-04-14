<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',        // Menambahkan kolom NIP
        'no_wa',      // Menambahkan kolom Nomor WA
        'foto',       // Menambahkan kolom Path Foto
        'role',       // Menambahkan kolom Role (admin/petugas/dinas)
        'is_active',  // Menambahkan status aktif
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean', // Cast status aktif ke boolean
        ];
    }

    /**
     * Relasi ke Tiket (One-to-Many)
     * Menampilkan daftar tiket yang ditugaskan kepada user/petugas ini.
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Relasi ke Task Logs
     * Menampilkan riwayat aktivitas/catatan yang pernah dibuat oleh user ini.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class);
    }
}