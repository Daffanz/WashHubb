<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenerimaanOutletDetail extends Model
{
    protected $table = 'penerimaan_outlet_details';
    protected $fillable = ['penerimaan_outlet_id', 'distribusi_outlet_detail_id', 'qty_diterima'];
    protected function casts(): array { return ['qty_diterima' => 'decimal:4']; }

    public function penerimaanOutlet(): BelongsTo { return $this->belongsTo(PenerimaanOutlet::class, 'penerimaan_outlet_id'); }
    public function distribusiOutletDetail(): BelongsTo { return $this->belongsTo(DistribusiOutletDetail::class, 'distribusi_outlet_detail_id'); }
}
