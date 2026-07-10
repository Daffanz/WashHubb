<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id'       => 'required|exists:users,id',
            'jenis_supplier' => 'required|in:bahan_baku,mesin',
            'alamat'         => 'required|string',
            'telepon'        => 'nullable|string|max:20',
            'status_id'      => 'nullable|exists:statuses,id',
        ];
    }
}
