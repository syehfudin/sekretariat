<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'nama_lengkap', 'email', 'password', 'role_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function anggota(): BelongsToMany
    {
        return $this->belongsToMany(Anggota::class, 'anggota_user');
    }

    public function isSuperadmin(): bool
    {
        return $this->role?->kode === 'superadmin';
    }

    public function isSekre(): bool
    {
        return $this->role && str_starts_with($this->role->kode, 'sekre_');
    }

    public function isKa(): bool
    {
        return $this->role && str_starts_with($this->role->kode, 'ka_');
    }

    public function canManageAnggota(): bool
    {
        return $this->isSuperadmin() || $this->isSekre();
    }
}