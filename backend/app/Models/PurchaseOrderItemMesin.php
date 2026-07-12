<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItemMesin extends Model
{
    protected $table = 'purchase_order_item_mesins';
    protected $fillable = ['po_id', 'mesin_id', 'jumlah', 'harga_satuan', 'qty_disetujui', 'status_id', 'alasan'];
    protected function casts(): array { return ['jumlah' => 'integer', 'harga_satuan' => 'decimal:2', 'qty_disetujui' => 'integer']; }

    public function po(): BelongsTo { return $this->belongsTo(PurchaseOrder::class, 'po_id'); }
    public function mesin(): BelongsTo { return $this->belongsTo(Mesin::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
}
