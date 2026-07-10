<?php

namespace App\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistribusiRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'po_id'   => 'required|exists:purchase_orders,id',
            'catatan' => 'nullable|string',
        ];
    }
}
