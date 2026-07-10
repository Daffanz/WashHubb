<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistribusiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'nomor_distribusi'   => $this->nomor_distribusi,
            'purchase_order'     => $this->whenLoaded('purchaseOrder', fn () => [
                'id'        => $this->purchaseOrder->id,
                'nomor_po'  => $this->purchaseOrder->nomor_po,
            ]),
            'status'             => $this->whenLoaded('status', fn () => [
                'id'   => $this->status->id,
                'name' => $this->status->name,
            ]),
            'catatan'            => $this->catatan,
            'penerimaan_barangs' => PenerimaanResource::collection($this->whenLoaded('penerimaanBarangs')),
            'created_at'         => $this->created_at?->toDateTimeString(),
        ];
    }
}
