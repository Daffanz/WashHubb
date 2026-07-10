<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PenerimaanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'distribusi_barang'  => $this->whenLoaded('distribusiBarang', fn () => [
                'id'               => $this->distribusiBarang->id,
                'nomor_distribusi' => $this->distribusiBarang->nomor_distribusi,
                'purchase_order'   => $this->whenLoaded('distribusiBarang.purchaseOrder', fn () => [
                    'id'        => $this->distribusiBarang->purchaseOrder->id,
                    'nomor_po'  => $this->distribusiBarang->purchaseOrder->nomor_po,
                ]),
            ]),
            'tanggal_terima'     => $this->tanggal_terima->format('Y-m-d'),
            'total_bayar'        => (float) $this->total_bayar,
            'catatan'            => $this->catatan,
            'created_at'         => $this->created_at?->toDateTimeString(),
        ];
    }
}
