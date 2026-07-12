<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $table = 'purchase_orders';
    protected $fillable = ['nomor_po', 'supplier_id', 'dibuat_oleh_id', 'jenis_po', 'total_nilai', 'status_id'];

    protected function casts(): array { return ['total_nilai' => 'decimal:2']; }

    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function dibuatOleh(): BelongsTo { return $this->belongsTo(User::class, 'dibuat_oleh_id'); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function itemBahanBakus(): HasMany { return $this->hasMany(PurchaseOrderItemBahanBaku::class, 'po_id'); }
    public function itemMesins(): HasMany { return $this->hasMany(PurchaseOrderItemMesin::class, 'po_id'); }
    public function distribusiBarangs(): HasMany { return $this->hasMany(DistribusiBarang::class, 'po_id'); }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->nomor_po)) {
                $date = now()->format('Ymd');
                $count = static::whereDate('created_at', today())->count() + 1;
                $model->nomor_po = 'PO/' . $date . '/' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function isDiajukan(): bool { return $this->status?->kode === 'diajukan'; }
    public function isDikirim(): bool { return $this->status?->kode === 'dikirim'; }
    public function isDisetujui(): bool { return $this->status?->kode === 'disetujui'; }
    public function isSelesai(): bool { return $this->status?->kode === 'selesai'; }
}
