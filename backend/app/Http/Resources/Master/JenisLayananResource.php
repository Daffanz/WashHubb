<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JenisLayananResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'nama'                => $this->nama,
            'harga_standar_per_kg' => (float) $this->harga_standar_per_kg,
            'materials'           => BahanBakuResource::collection($this->whenLoaded('materials')),
            'created_at'          => $this->created_at?->toDateTimeString(),
        ];
    }
}
