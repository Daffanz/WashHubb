<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiStokOutletBahanBaku extends Model
{
    protected $table = 'mutasi_stok_outlet_bahan_bakus';
    protected $fillable = [
        'stok_outlet_bahan_baku_id', 'jenis_mutasi', 'jumlah',
        'tanggal', 'order_cucian_id', 'penerimaan_outlet_detail_id',
    ];
    protected function casts(): array
    {
        return ['jumlah' => 'decimal:4', 'tanggal' => 'datetime'];
    }

    public function stokOutlet(): BelongsTo { return $this->belongsTo(StokOutletBahanBaku::class, 'stok_outlet_bahan_baku_id'); }
    public function orderCucian(): BelongsTo { return $this->belongsTo(OrderCucian::class); }
    public function penerimaanOutletDetail(): BelongsTo { return $this->belongsTo(PenerimaanOutletDetail::class); }
}
