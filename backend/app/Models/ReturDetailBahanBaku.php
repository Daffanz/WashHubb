<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturDetailBahanBaku extends Model
{
    protected $table = 'retur_detail_bahan_bakus';
    protected $fillable = ['retur_barang_id', 'penerimaan_detail_id', 'qty_retur', 'alasan', 'foto_bukti', 'qty_pengganti', 'status_id'];

    public function returBarang(): BelongsTo { return $this->belongsTo(ReturBarang::class, 'retur_barang_id'); }
    public function penerimaanDetail(): BelongsTo { return $this->belongsTo(PenerimaanDetailBahanBaku::class, 'penerimaan_detail_id'); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
}
