<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisLayanan extends Model
{
    protected $table = 'jenis_layanans';
    protected $fillable = ['nama', 'harga_standar_per_kg', 'status_id'];
    protected function casts(): array { return ['harga_standar_per_kg' => 'decimal:2']; }

    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function layananBahanBakus(): HasMany { return $this->hasMany(JenisLayananBahanBaku::class, 'jenis_layanan_id'); }
    public function materials()
    {
        return $this->belongsToMany(BahanBaku::class, 'jenis_layanan_bahan_bakus', 'jenis_layanan_id', 'bahan_baku_id')
            ->withPivot('jumlah_konsumsi');
    }
}
