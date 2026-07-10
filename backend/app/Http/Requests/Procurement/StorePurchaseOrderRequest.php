<?php

namespace App\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

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
}
