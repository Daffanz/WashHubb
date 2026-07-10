<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'user'           => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ]),
            'jenis_supplier' => $this->jenis_supplier,
            'alamat'         => $this->alamat,
            'telepon'        => $this->telepon,
            'status'         => $this->whenLoaded('status', fn () => [
                'id'   => $this->status->id,
                'name' => $this->status->name,
            ]),
            'created_at'     => $this->created_at?->toDateTimeString(),
        ];
    }
}
