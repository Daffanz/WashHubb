<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BahanBaku extends Model
{
    use SoftDeletes;

    protected $table = 'bahan_bakus';

    protected $fillable = [
        'kategori_id',
        'nama',
        'satuan',
        'harga_standar',
    ];

    protected function casts(): array
    {
        return [
            'harga_standar' => 'decimal:2',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriBahanBaku::class, 'kategori_id');
    }

    public function layananBahanBakus(): HasMany
    {
        return $this->hasMany(JenisLayananBahanBaku::class, 'bahan_baku_id');
    }

    public function purchaseOrderItems(): MorphMany
    {
        return $this->morphMany(PurchaseOrderItem::class, 'item');
    }

    public function stokPusat()
    {
        return $this->hasOne(StokPusatBahanBaku::class, 'bahan_baku_id');
    }
}
