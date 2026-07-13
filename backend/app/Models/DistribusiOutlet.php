<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistribusiOutlet extends Model
{
    protected $table = 'distribusi_outlets';
    protected $fillable = ['permintaan_stok_outlet_id', 'tanggal_kirim', 'status_id'];
    protected function casts(): array { return ['tanggal_kirim' => 'date']; }

    public function permintaanStokOutlet(): BelongsTo { return $this->belongsTo(PermintaanStokOutlet::class, 'permintaan_stok_outlet_id'); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function details(): HasMany { return $this->hasMany(DistribusiOutletDetail::class, 'distribusi_outlet_id'); }
    public function penerimaanOutlets(): HasMany { return $this->hasMany(PenerimaanOutlet::class, 'distribusi_outlet_id'); }
}
