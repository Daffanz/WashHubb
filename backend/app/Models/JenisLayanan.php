<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisLayanan extends Model
{
    use SoftDeletes;

    protected $table = 'jenis_layanans';

    protected $fillable = [
        'nama',
        'harga_standar_per_kg',
    ];

    protected function casts(): array
    {
        return [
            'harga_standar_per_kg' => 'decimal:2',
        ];
    }

    public function layananBahanBakus(): HasMany
    {
        return $this->hasMany(JenisLayananBahanBaku::class, 'jenis_layanan_id');
    }

    public function materials()
    {
        return $this->belongsToMany(BahanBaku::class, 'jenis_layanan_bahan_bakus', 'jenis_layanan_id', 'bahan_baku_id')
            ->withPivot('jumlah_konsumsi');
    }
}
