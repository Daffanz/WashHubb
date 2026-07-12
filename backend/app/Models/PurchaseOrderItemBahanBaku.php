<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItemBahanBaku extends Model
{
    protected $table = 'purchase_order_item_bahan_bakus';
    protected $fillable = ['po_id', 'bahan_baku_id', 'jumlah', 'harga_satuan', 'qty_disetujui', 'status_id', 'alasan'];
    protected function casts(): array { return ['jumlah' => 'decimal:4', 'harga_satuan' => 'decimal:2', 'qty_disetujui' => 'decimal:4']; }

    public function po(): BelongsTo { return $this->belongsTo(PurchaseOrder::class, 'po_id'); }
    public function bahanBaku(): BelongsTo { return $this->belongsTo(BahanBaku::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
}
