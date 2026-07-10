<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MutasiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'stok_type'    => $this->stok_type,
            'stok_id'      => $this->stok_id,
            'stok'         => $this->when($this->relationLoaded('stok'), fn () => new StockResource($this->stok)),
            'jenis_mutasi' => $this->jenis_mutasi,
            'jumlah'       => (float) $this->jumlah,
            'tanggal'      => $this->tanggal->toDateTimeString(),
            'keterangan'   => $this->keterangan,
            'created_at'   => $this->created_at?->toDateTimeString(),
        ];
    }
}
