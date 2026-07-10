<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function admins(): HasMany
    {
        return $this->hasMany(User::class)
            ->where('role', UserRole::Admin);
    }

    public function members(): HasMany
    {
        return $this->hasMany(User::class)
            ->where('role', UserRole::Member);
    }

    public function scopeWithDirectoryCounts(Builder $query): Builder
    {
        return $query->withCount(['admins', 'members']);
    }
}
