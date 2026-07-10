<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JenisLayananBahanBaku extends Model
{
    protected $table = 'jenis_layanan_bahan_bakus';

    protected $fillable = [
        'jenis_layanan_id',
        'bahan_baku_id',
        'jumlah_konsumsi',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_konsumsi' => 'decimal:4',
        ];
    }

    public function jenisLayanan(): BelongsTo
    {
        return $this->belongsTo(JenisLayanan::class, 'jenis_layanan_id');
    }

    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}
