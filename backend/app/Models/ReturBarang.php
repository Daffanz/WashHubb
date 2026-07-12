<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturBarang extends Model
{
    protected $table = 'retur_barangs';
    protected $fillable = ['penerimaan_barang_id', 'tanggal_retur', 'status_id'];
    protected function casts(): array { return ['tanggal_retur' => 'datetime']; }

    public function penerimaanBarang(): BelongsTo { return $this->belongsTo(PenerimaanBarang::class, 'penerimaan_barang_id'); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function detailBahanBakus(): HasMany { return $this->hasMany(ReturDetailBahanBaku::class, 'retur_barang_id'); }
    public function detailMesins(): HasMany { return $this->hasMany(ReturDetailMesin::class, 'retur_barang_id'); }
}
