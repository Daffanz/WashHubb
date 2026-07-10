<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PurchaseOrderItem extends Model
{
    protected $table = 'purchase_order_items';

    protected $fillable = [
        'po_id',
        'item_type',
        'item_id',
        'jumlah',
        'harga_satuan',
        // subtotal is computed, not fillable
    ];

    protected function casts(): array
    {
        return [
            'jumlah'       => 'decimal:4',
            'harga_satuan' => 'decimal:2',
            'subtotal'     => 'decimal:2',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function item(): MorphTo
    {
        return $this->morphTo();
    }
}
