<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistribusiBarang extends Model
{
    protected $table = 'distribusi_barangs';
    protected $fillable = ['po_id', 'nomor_distribusi', 'tanggal_kirim', 'status_id'];

    protected function casts(): array { return ['tanggal_kirim' => 'date']; }

    public function po(): BelongsTo { return $this->belongsTo(PurchaseOrder::class, 'po_id'); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function detailBahanBakus(): HasMany { return $this->hasMany(DistDetailBahanBaku::class, 'distribusi_barang_id'); }
    public function detailMesins(): HasMany { return $this->hasMany(DistDetailMesin::class, 'distribusi_barang_id'); }
    public function penerimaanBarangs(): HasMany { return $this->hasMany(PenerimaanBarang::class, 'distribusi_barang_id'); }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->nomor_distribusi)) {
                $date = now()->format('Ymd');
                $count = static::whereDate('created_at', today())->count() + 1;
                $model->nomor_distribusi = 'DIST/' . $date . '/' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
