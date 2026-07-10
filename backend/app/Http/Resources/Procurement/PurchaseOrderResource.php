<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'nomor_po'        => $this->nomor_po,
            'supplier'        => $this->whenLoaded('supplier', fn () => [
                'id'   => $this->supplier->id,
                'nama' => $this->supplier->user?->name,
            ]),
            'jenis_po'        => $this->jenis_po,
            'status'          => $this->whenLoaded('status', fn () => [
                'id'   => $this->status->id,
                'name' => $this->status->name,
            ]),
            'status_validasi' => $this->status_validasi,
            'catatan'         => $this->catatan,
            'items'           => PurchaseOrderItemResource::collection($this->whenLoaded('items')),
            'created_at'      => $this->created_at?->toDateTimeString(),
        ];
    }
}
