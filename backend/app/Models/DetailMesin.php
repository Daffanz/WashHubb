<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailMesin extends Model
{
    protected $table = 'detail_mesins';
    protected $fillable = ['mesin_id', 'nomor_seri', 'outlet_id', 'tanggal_terima_pusat', 'status_id'];
    protected function casts(): array { return ['tanggal_terima_pusat' => 'date']; }

    public function mesin(): BelongsTo { return $this->belongsTo(Mesin::class); }
    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
}
