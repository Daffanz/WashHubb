<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outlet extends Model
{
    protected $fillable = ['nama', 'kode_outlet', 'alamat', 'franchise_id', 'manager_outlet_id', 'status_id'];

    public function franchise(): BelongsTo { return $this->belongsTo(Franchise::class); }
    public function managerOutlet(): BelongsTo { return $this->belongsTo(User::class, 'manager_outlet_id'); }
    public function status(): BelongsTo { return $this->belongsTo(Status::class); }
    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_outlets')->withTimestamps();
    }
    public function stokBahanBakus(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StokOutletBahanBaku::class);
    }
    public function stokMesins(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StokOutletMesin::class);
    }
}
