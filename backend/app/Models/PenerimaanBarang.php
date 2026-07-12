<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PenerimaanBarang extends Model
{
    protected $table = 'penerimaan_barangs';
    protected $fillable = ['distribusi_barang_id', 'nomor_penerimaan', 'tanggal_terima', 'subtotal', 'diskon', 'ppn', 'total_bayar', 'status_id'];
    protected function casts(): array { return ['tanggal_terima' => 'date', 'subtotal' => 'decimal:2', 'diskon' => 'decimal:2', 'ppn' => 'decimal:2', 'total_bayar' => 'decimal:2']; }

    public function distribusiBarang(): BelongsTo { return $this->belongsTo(DistribusiBarang::class, 'distribusi_barang_id'); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function detailBahanBakus(): HasMany { return $this->hasMany(PenerimaanDetailBahanBaku::class, 'penerimaan_barang_id'); }
    public function detailMesins(): HasMany { return $this->hasMany(PenerimaanDetailMesin::class, 'penerimaan_barang_id'); }
    public function returBarangs(): HasMany { return $this->hasMany(ReturBarang::class, 'penerimaan_barang_id'); }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->nomor_penerimaan)) {
                $date = now()->format('Ymd');
                $count = static::whereDate('created_at', today())->count() + 1;
                $model->nomor_penerimaan = 'REC/' . $date . '/' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
