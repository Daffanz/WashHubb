<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BahanBakuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'kategori'      => $this->whenLoaded('kategori', fn () => [
                'id'   => $this->kategori->id,
                'nama' => $this->kategori->nama,
            ]),
            'nama'          => $this->nama,
            'satuan'        => $this->satuan,
            'harga_standar' => (float) $this->harga_standar,
            'created_at'    => $this->created_at?->toDateTimeString(),
        ];
    }
}
