<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    public $timestamps = false;

    protected $fillable = ['konteks', 'kode', 'label'];

    public function scopeForContext($query, string $konteks)
    {
        return $query->where('konteks', $konteks);
    }

    public static function getByContext(string $konteks, string $kode): ?self
    {
        return static::where('konteks', $konteks)->where('kode', $kode)->first();
    }
}
