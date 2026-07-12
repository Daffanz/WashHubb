<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StokSupplierBahanBaku extends Model
{
    protected $table = 'stok_supplier_bahan_bakus';
    protected $fillable = ['supplier_id', 'bahan_baku_id', 'stok_saat_ini', 'status_id'];

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function bahanBaku(): BelongsTo { return $this->belongsTo(BahanBaku::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function mutasiStoks(): HasMany { return $this->hasMany(MutasiStokSupplierBahanBaku::class, 'stok_supplier_bahan_baku_id'); }
}
