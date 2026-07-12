<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiStokSupplierBahanBaku extends Model
{
    protected $table = 'mutasi_stok_supplier_bahan_bakus';
    protected $fillable = ['stok_supplier_bahan_baku_id', 'jenis_mutasi', 'jumlah', 'tanggal'];

    public function stokSupplier(): BelongsTo { return $this->belongsTo(StokSupplierBahanBaku::class, 'stok_supplier_bahan_baku_id'); }
}
