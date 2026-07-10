<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'jenis_supplier' => 'sometimes|in:bahan_baku,mesin',
            'alamat'         => 'sometimes|string',
            'telepon'        => 'sometimes|nullable|string|max:20',
            'status_id'      => 'sometimes|nullable|exists:statuses,id',
        ];
    }
}
