<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Franchise extends Model
{
    protected $fillable = ['user_id'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function outlets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Outlet::class);
    }
}
