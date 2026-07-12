<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiStokPusatMesin extends Model
{
    protected $table = 'mutasi_stok_pusat_mesins';
    protected $fillable = ['stok_pusat_mesin_id', 'jenis_mutasi', 'jumlah', 'tanggal', 'penerimaan_detail_id', 'retur_detail_id'];
    protected function casts(): array { return ['tanggal' => 'datetime']; }

    public function stokPusat(): BelongsTo { return $this->belongsTo(StokPusatMesin::class, 'stok_pusat_mesin_id'); }
    public function penerimaanDetail(): BelongsTo { return $this->belongsTo(PenerimaanDetailMesin::class, 'penerimaan_detail_id'); }
}
