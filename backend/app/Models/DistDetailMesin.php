<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistDetailMesin extends Model
{
    protected $table = 'dist_detail_mesins';
    protected $fillable = ['distribusi_barang_id', 'po_item_id', 'jumlah_kirim'];

    public function distribusiBarang(): BelongsTo { return $this->belongsTo(DistribusiBarang::class, 'distribusi_barang_id'); }
    public function poItem(): BelongsTo { return $this->belongsTo(PurchaseOrderItemMesin::class, 'po_item_id'); }
}
