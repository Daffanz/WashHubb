<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $fillable = ['name', 'group', 'description'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Scopes per group
    public function scopeForGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
