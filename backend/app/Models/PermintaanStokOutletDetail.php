<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermintaanStokOutletDetail extends Model
{
    protected $table = 'permintaan_stok_outlet_details';
    protected $fillable = [
        'permintaan_stok_outlet_id', 'bahan_baku_id', 'mesin_id', 'tipe_item',
        'jumlah_diminta', 'jumlah_disetujui', 'alasan', 'status_id',
    ];
    protected function casts(): array
    {
        return ['jumlah_diminta' => 'decimal:4', 'jumlah_disetujui' => 'decimal:4'];
    }

    public function permintaanStokOutlet(): BelongsTo { return $this->belongsTo(PermintaanStokOutlet::class, 'permintaan_stok_outlet_id'); }
    public function bahanBaku(): BelongsTo { return $this->belongsTo(BahanBaku::class); }
    public function mesin(): BelongsTo { return $this->belongsTo(Mesin::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
}
