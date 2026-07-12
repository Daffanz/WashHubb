<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'jenis_supplier', 'alamat', 'katalog_produk', 'status_id'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function purchaseOrders(): HasMany { return $this->hasMany(PurchaseOrder::class); }

    public function bahanBakus(): BelongsToMany
    {
        return $this->belongsToMany(BahanBaku::class, 'supplier_bahan_baku', 'supplier_id', 'bahan_baku_id')->withTimestamps();
    }

    public function mesins(): BelongsToMany
    {
        return $this->belongsToMany(Mesin::class, 'supplier_mesin', 'supplier_id', 'mesin_id')->withTimestamps();
    }

    public function stokBahanBakus(): HasMany { return $this->hasMany(StokSupplierBahanBaku::class); }
    public function stokMesins(): HasMany { return $this->hasMany(StokSupplierMesin::class); }
}
