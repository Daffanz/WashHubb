<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class StokPusatMesin extends Model
{
    protected $table = 'stok_pusat_mesins';

    protected $fillable = [
        'mesin_id',
        'stok_saat_ini',
    ];

    protected function casts(): array
    {
        return [
            'stok_saat_ini' => 'integer',
        ];
    }

    public function mesin(): BelongsTo
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }

    public function mutasiStoks(): MorphMany
    {
        return $this->morphMany(MutasiStok::class, 'stok');
    }
}
