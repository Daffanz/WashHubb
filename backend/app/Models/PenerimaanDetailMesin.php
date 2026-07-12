<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenerimaanDetailMesin extends Model
{
    protected $table = 'penerimaan_detail_mesins';
    protected $fillable = ['penerimaan_barang_id', 'distribusi_detail_id', 'nomor_seri', 'qty_diterima', 'kondisi'];

    public function penerimaanBarang(): BelongsTo { return $this->belongsTo(PenerimaanBarang::class, 'penerimaan_barang_id'); }
    public function distribusiDetail(): BelongsTo { return $this->belongsTo(DistDetailMesin::class, 'distribusi_detail_id'); }
}
