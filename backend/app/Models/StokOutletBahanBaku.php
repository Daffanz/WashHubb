<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StokOutletBahanBaku extends Model
{
    protected $table = 'stok_outlet_bahan_bakus';
    protected $fillable = ['outlet_id', 'bahan_baku_id', 'stok_saat_ini', 'stok_minimum', 'stok_masuk', 'stok_keluar'];
    protected function casts(): array
    {
        return [
            'stok_saat_ini' => 'decimal:4',
            'stok_minimum' => 'decimal:4',
            'stok_masuk' => 'decimal:4',
            'stok_keluar' => 'decimal:4',
        ];
    }

    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }
    public function bahanBaku(): BelongsTo { return $this->belongsTo(BahanBaku::class); }
    public function mutasis(): HasMany { return $this->hasMany(MutasiStokOutletBahanBaku::class, 'stok_outlet_bahan_baku_id'); }
}
