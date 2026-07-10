<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KategoriResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'nama'            => $this->nama,
            'bahan_bakus_count' => $this->whenCounted('bahanBakus'),
            'created_at'      => $this->created_at?->toDateTimeString(),
        ];
    }
}
