<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribusiOutletDetail extends Model
{
    protected $table = 'distribusi_outlet_details';
    protected $fillable = ['distribusi_outlet_id', 'bahan_baku_id', 'mesin_id', 'tipe_item', 'jumlah_kirim'];
    protected function casts(): array { return ['jumlah_kirim' => 'decimal:4']; }

    public function distribusiOutlet(): BelongsTo { return $this->belongsTo(DistribusiOutlet::class, 'distribusi_outlet_id'); }
    public function bahanBaku(): BelongsTo { return $this->belongsTo(BahanBaku::class); }
    public function mesin(): BelongsTo { return $this->belongsTo(Mesin::class); }
    public function penerimaanOutlets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PenerimaanOutletDetail::class, 'distribusi_outlet_detail_id');
    }
}
