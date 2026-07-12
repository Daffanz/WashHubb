<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBahanBaku extends Model
{
    protected $table = 'kategori_bahan_bakus';
    protected $fillable = ['nama'];

    public function bahanBakus(): HasMany { return $this->hasMany(BahanBaku::class, 'kategori_id'); }
}
