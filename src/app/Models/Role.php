<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = ['kode', 'nama', 'keterangan'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isSekre(): bool
    {
        return str_starts_with($this->kode, 'sekre_');
    }

    public function isKa(): bool
    {
        return str_starts_with($this->kode, 'ka_');
    }
}