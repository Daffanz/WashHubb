<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'nomor_po',
        'supplier_id',
        'jenis_po',
        'status_validasi',
        'status_id',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'status_validasi' => 'string',
            'jenis_po'        => 'string',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->nomor_po)) {
                $date    = now()->format('Ymd');
                $count   = self::whereDate('created_at', today())->count() + 1;
                $model->nomor_po = 'PO/' . $date . '/' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'po_id');
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(DistribusiBarang::class, 'po_id');
    }

    public function isDraft(): bool
    {
        return $this->status?->name === 'draft';
    }

    public function isDikirim(): bool
    {
        return $this->status?->name === 'dikirim';
    }

    public function isDisetujui(): bool
    {
        return $this->status_validasi === 'disetujui';
    }

    public function scopeForSupplier($query, $userId)
    {
        return $query->whereHas('supplier', fn ($q) => $q->where('user_id', $userId));
    }
}
