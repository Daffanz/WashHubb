<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id'            => $this->id,
            'stok_saat_ini' => (float) $this->stok_saat_ini,
            'updated_at'    => $this->updated_at?->toDateTimeString(),
        ];

        // Polymorphic: could be StokPusatBahanBaku or StokPusatMesin
        if ($this->relationLoaded('bahanBaku') && $this->bahanBaku) {
            $data['type'] = 'bahan_baku';
            $data['bahan_baku'] = [
                'id'       => $this->bahanBaku->id,
                'nama'     => $this->bahanBaku->nama,
                'satuan'   => $this->bahanBaku->satuan,
                'kategori' => $this->bahanBaku->kategori?->nama,
            ];
        } elseif ($this->relationLoaded('mesin') && $this->mesin) {
            $data['type'] = 'mesin';
            $data['mesin'] = [
                'id'         => $this->mesin->id,
                'nama'       => $this->mesin->nama,
                'kode_mesin' => $this->mesin->kode_mesin,
                'merk'       => $this->mesin->merk,
            ];
        }

        return $data;
    }
}
