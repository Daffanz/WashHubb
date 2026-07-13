<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutasiStokOutletMesin extends Model
{
    protected $table = 'mutasi_stok_outlet_mesins';
    protected $fillable = ['stok_outlet_mesin_id', 'jenis_mutasi', 'jumlah', 'tanggal', 'penerimaan_outlet_detail_id'];
    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'tanggal' => 'datetime',
        ];
    }

    public function stokOutletMesin(): BelongsTo { return $this->belongsTo(StokOutletMesin::class, 'stok_outlet_mesin_id'); }
    public function penerimaanOutletDetail(): BelongsTo { return $this->belongsTo(PenerimaanOutletDetail::class, 'penerimaan_outlet_detail_id'); }
}
