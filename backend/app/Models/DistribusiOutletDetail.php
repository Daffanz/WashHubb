<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistribusiOutletDetail extends Model
{
    protected $table = 'distribusi_outlet_details';
    protected $fillable = ['distribusi_outlet_id', 'bahan_baku_id', 'jumlah_kirim'];
    protected function casts(): array { return ['jumlah_kirim' => 'decimal:4']; }

    public function distribusiOutlet(): BelongsTo { return $this->belongsTo(DistribusiOutlet::class, 'distribusi_outlet_id'); }
    public function bahanBaku(): BelongsTo { return $this->belongsTo(BahanBaku::class); }
}
