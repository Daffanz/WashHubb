<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MesinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'nama'       => $this->nama,
            'kode_mesin' => $this->kode_mesin,
            'merk'       => $this->merk,
            'tipe'       => $this->tipe,
            'kapasitas'  => $this->kapasitas,
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
