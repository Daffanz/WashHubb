<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class StokPusatBahanBaku extends Model
{
    protected $table = 'stok_pusat_bahan_bakus';

    protected $fillable = [
        'bahan_baku_id',
        'stok_saat_ini',
    ];

    protected function casts(): array
    {
        return [
            'stok_saat_ini' => 'decimal:4',
        ];
    }

    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function mutasiStoks(): MorphMany
    {
        return $this->morphMany(MutasiStok::class, 'stok');
    }
}
