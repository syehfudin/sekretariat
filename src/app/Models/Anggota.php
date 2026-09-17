<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Anggota extends Model
{
    protected $table = 'anggota';

    protected $fillable = ['nama_lengkap', 'pekerjaan'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'anggota_user');
    }

    public function scopeForUser($query, User $user)
    {
        return $query->whereHas('users', fn ($q) => $q->where('users.id', $user->id));
    }
}