<?php

namespace App\Http\Requests\Procurement;

use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'jenis_po'    => 'required|in:bahan_baku,mesin',
            'catatan'     => 'nullable|string',
            'items'       => 'required|array|min:1',
            'items.*.item_type'    => 'required|string|in:App\Models\BahanBaku,App\Models\Mesin',
            'items.*.item_id'      => 'required|integer',
            'items.*.jumlah'       => 'required|numeric|min:0.0001',
            'items.*.harga_satuan' => 'required|numeric|min:0',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) return;

            $supplier = Supplier::find($this->supplier_id);
            if (!$supplier) return;

            // For bahan_baku PO: validate all items belong to the supplier
            if ($this->jenis_po === 'bahan_baku') {
                $supplierBahanBakuIds = $supplier->bahanBakus()->pluck('bahan_baku_id')->toArray();
                foreach ($this->items as $i => $item) {
                    if (($item['item_type'] ?? '') !== 'App\Models\BahanBaku') continue;
                    if (!in_array($item['item_id'], $supplierBahanBakuIds)) {
                        $validator->errors()->add(
                            "items.{$i}.item_id",
                            "Bahan baku ID {$item['item_id']} tidak tersedia di supplier ini."
                        );
                    }
                }
            }
        });
    }
}
