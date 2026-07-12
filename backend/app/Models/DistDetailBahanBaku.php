<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistDetailBahanBaku extends Model
{
    protected $table = 'dist_detail_bahan_bakus';
    protected $fillable = ['distribusi_barang_id', 'po_item_id', 'jumlah_kirim'];

    public function distribusiBarang(): BelongsTo { return $this->belongsTo(DistribusiBarang::class, 'distribusi_barang_id'); }
    public function poItem(): BelongsTo { return $this->belongsTo(PurchaseOrderItemBahanBaku::class, 'po_item_id'); }
}
