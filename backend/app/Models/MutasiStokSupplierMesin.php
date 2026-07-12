<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiStokSupplierMesin extends Model
{
    protected $table = 'mutasi_stok_supplier_mesins';
    protected $fillable = ['stok_supplier_mesin_id', 'jenis_mutasi', 'jumlah', 'tanggal'];

    public function stokSupplier(): BelongsTo { return $this->belongsTo(StokSupplierMesin::class, 'stok_supplier_mesin_id'); }
}
