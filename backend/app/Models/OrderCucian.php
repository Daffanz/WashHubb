<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCucian extends Model
{
    protected $table = 'order_cucians';
    protected $fillable = [
        'outlet_id', 'user_id', 'jenis_layanan_id', 'detail_mesin_id',
        'berat', 'total_harga', 'waktu_masuk', 'estimasi_selesai',
        'waktu_selesai', 'alasan_pembatalan', 'status_id',
    ];
    protected function casts(): array
    {
        return [
            'berat' => 'decimal:2',
            'total_harga' => 'decimal:2',
            'waktu_masuk' => 'datetime',
            'estimasi_selesai' => 'datetime',
            'waktu_selesai' => 'datetime',
        ];
    }

    public function outlet(): BelongsTo { return $this->belongsTo(Outlet::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function jenisLayanan(): BelongsTo { return $this->belongsTo(JenisLayanan::class); }
    public function detailMesin(): BelongsTo { return $this->belongsTo(DetailMesin::class); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
}
