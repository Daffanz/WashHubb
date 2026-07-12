<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class BahanBaku extends Model
{
    protected $table = 'bahan_bakus';
    protected $fillable = ['nama', 'kategori_id', 'satuan', 'harga_standar', 'status_id'];
    protected function casts(): array { return ['harga_standar' => 'decimal:2']; }

    public function kategori(): BelongsTo { return $this->belongsTo(KategoriBahanBaku::class, 'kategori_id'); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function layananBahanBakus(): HasMany { return $this->hasMany(JenisLayananBahanBaku::class, 'bahan_baku_id'); }
    public function suppliers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_bahan_baku', 'bahan_baku_id', 'supplier_id')->withTimestamps();
    }
}
