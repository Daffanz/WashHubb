<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'item_type'     => $this->item_type,
            'item_id'       => $this->item_id,
            'item'          => $this->when($this->relationLoaded('item'), fn () => [
                'id'   => $this->item->id,
                'nama' => $this->item->nama,
            ]),
            'jumlah'        => (float) $this->jumlah,
            'harga_satuan'  => (float) $this->harga_satuan,
            'subtotal'      => (float) $this->subtotal,
        ];
    }
}
