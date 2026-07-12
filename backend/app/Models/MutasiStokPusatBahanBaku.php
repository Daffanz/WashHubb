<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiStokPusatBahanBaku extends Model
{
    protected $table = 'mutasi_stok_pusat_bahan_bakus';
    protected $fillable = ['stok_pusat_bahan_baku_id', 'jenis_mutasi', 'jumlah', 'tanggal', 'penerimaan_detail_id'];

    public function stokPusat(): BelongsTo { return $this->belongsTo(StokPusatBahanBaku::class, 'stok_pusat_bahan_baku_id'); }
    public function penerimaanDetail(): BelongsTo { return $this->belongsTo(PenerimaanDetailBahanBaku::class, 'penerimaan_detail_id'); }
}
