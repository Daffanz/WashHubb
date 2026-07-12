<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenerimaanDetailBahanBaku extends Model
{
    protected $table = 'penerimaan_detail_bahan_bakus';
    protected $fillable = ['penerimaan_barang_id', 'distribusi_detail_id', 'qty_diterima', 'kondisi'];

    public function penerimaanBarang(): BelongsTo { return $this->belongsTo(PenerimaanBarang::class, 'penerimaan_barang_id'); }
    public function distribusiDetail(): BelongsTo { return $this->belongsTo(DistDetailBahanBaku::class, 'distribusi_detail_id'); }
}
