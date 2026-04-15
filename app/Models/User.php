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
        'username',   // Login via Username/NIP
        'email',
        'password',
        'no_wa',      // Penting untuk Bot WA
        'foto',       // SUDAH DIUPDATE: Sinkron dengan migration dan controller
        'role',       // super_admin, seksi, petugas
        'is_active',
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
            'is_active' => 'boolean',
        ];
    }

    /** * HELPER FUNCTIONS UNTUK ROLE
     * Digunakan di Controller atau Blade: if(Auth::user()->isSuperAdmin())
     */
    
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isSeksi(): bool
    {
        return $this->role === 'seksi';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
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