<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistribusiBarang extends Model
{
    use SoftDeletes;

    protected $table = 'distribusi_barangs';

    protected $fillable = [
        'po_id',
        'nomor_distribusi',
        'status_id',
        'catatan',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->nomor_distribusi)) {
                $date  = now()->format('Ymd');
                $count = self::whereDate('created_at', today())->count() + 1;
                $model->nomor_distribusi = 'DIST/' . $date . '/' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function penerimaanBarangs(): HasMany
    {
        return $this->hasMany(PenerimaanBarang::class, 'distribusi_barang_id');
    }
}
