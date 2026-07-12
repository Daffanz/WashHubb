<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StokSupplierMesin extends Model
{
    protected $table = 'stok_supplier_mesins';
    protected $fillable = ['supplier_id', 'mesin_id', 'stok_saat_ini', 'status_id'];

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function mesin(): BelongsTo { return $this->belongsTo(Mesin::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function mutasiStoks(): HasMany { return $this->hasMany(MutasiStokSupplierMesin::class, 'stok_supplier_mesin_id'); }
}
