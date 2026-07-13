<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermintaanStokOutlet extends Model
{
    protected $table = 'permintaan_stok_outlets';
    protected $fillable = ['outlet_id', 'user_id', 'tanggal', 'status_id'];
    protected function casts(): array { return ['tanggal' => 'date']; }

    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function details(): HasMany { return $this->hasMany(PermintaanStokOutletDetail::class, 'permintaan_stok_outlet_id'); }
    public function distribusiOutlets(): HasMany { return $this->hasMany(DistribusiOutlet::class, 'permintaan_stok_outlet_id'); }
}
