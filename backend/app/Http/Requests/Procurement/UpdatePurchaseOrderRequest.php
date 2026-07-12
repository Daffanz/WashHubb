<?php

namespace App\Http\Requests\Procurement;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'jenis_po'    => 'sometimes|in:bahan_baku,mesin',
            'catatan'     => 'sometimes|nullable|string',
            'items'       => 'sometimes|array|min:1',
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
            if (!$this->has('items')) return;

            $po = $this->route('purchaseOrder');
            $supplierId = $this->supplier_id ?? $po->supplier_id;
            $jenisPo = $this->jenis_po ?? $po->jenis_po;

            if ($jenisPo !== 'bahan_baku') return;

            $supplier = Supplier::find($supplierId);
            if (!$supplier) return;

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
        });
    }
}
