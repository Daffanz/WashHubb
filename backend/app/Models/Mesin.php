<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesin extends Model
{
    use SoftDeletes;

    protected $table = 'mesins';

    protected $fillable = [
        'nama',
        'kode_mesin',
        'merk',
        'tipe',
        'kapasitas',
    ];

    protected function casts(): array
    {
        return [
            'kapasitas' => 'integer',
        ];
    }

    public function purchaseOrderItems(): MorphMany
    {
        return $this->morphMany(PurchaseOrderItem::class, 'item');
    }

    public function stokPusat()
    {
        return $this->hasOne(StokPusatMesin::class, 'mesin_id');
    }
}
