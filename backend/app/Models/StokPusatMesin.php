<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StokPusatMesin extends Model
{
    protected $table = 'stok_pusat_mesins';
    protected $fillable = ['mesin_id', 'stok_masuk', 'stok_keluar', 'stok_saat_ini'];

    public function mesin(): BelongsTo { return $this->belongsTo(Mesin::class); }
    public function mutasiStoks(): HasMany { return $this->hasMany(MutasiStokPusatMesin::class, 'stok_pusat_mesin_id'); }
}
