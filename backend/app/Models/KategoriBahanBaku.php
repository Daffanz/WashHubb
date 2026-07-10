<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriBahanBaku extends Model
{
    use SoftDeletes;

    protected $table = 'kategori_bahan_bakus';

    protected $fillable = ['nama'];

    public function bahanBakus(): HasMany
    {
        return $this->hasMany(BahanBaku::class, 'kategori_id');
    }
}
