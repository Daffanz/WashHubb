<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MutasiStok extends Model
{
    protected $table = 'mutasi_stoks';

    protected $fillable = [
        'stok_type',
        'stok_id',
        'jenis_mutasi',
        'jumlah',
        'tanggal',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'jumlah'  => 'decimal:4',
            'tanggal' => 'datetime',
        ];
    }

    public function stok(): MorphTo
    {
        return $this->morphTo();
    }
}
