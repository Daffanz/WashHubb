<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StokPusatBahanBaku extends Model
{
    protected $table = 'stok_pusat_bahan_bakus';
    protected $fillable = ['bahan_baku_id', 'stok_masuk', 'stok_keluar', 'stok_saat_ini'];

    public function bahanBaku(): BelongsTo { return $this->belongsTo(BahanBaku::class); }
    public function mutasiStoks(): HasMany { return $this->hasMany(MutasiStokPusatBahanBaku::class, 'stok_pusat_bahan_baku_id'); }
}
