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
            'katalog_produk' => 'sometimes|nullable|string',
            'status_id'      => 'sometimes|nullable|exists:statuses,id',
            'bahan_baku_ids' => 'sometimes|array',
            'bahan_baku_ids.*' => 'exists:bahan_bakus,id',
            'mesin_ids'      => 'sometimes|array',
            'mesin_ids.*'    => 'exists:mesins,id',
        ];
    }
}
