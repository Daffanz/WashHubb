<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StokOutletMesin extends Model
{
    protected $table = 'stok_outlet_mesins';
    protected $fillable = ['outlet_id', 'mesin_id', 'stok_saat_ini', 'stok_minimum', 'stok_masuk', 'stok_keluar'];
    protected function casts(): array
    {
        return [
            'stok_saat_ini' => 'integer',
            'stok_minimum' => 'integer',
            'stok_masuk' => 'integer',
            'stok_keluar' => 'integer',
        ];
    }

    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }
    public function mesin(): BelongsTo { return $this->belongsTo(Mesin::class); }
    public function mutasis(): HasMany { return $this->hasMany(MutasiStokOutletMesin::class, 'stok_outlet_mesin_id'); }
}
