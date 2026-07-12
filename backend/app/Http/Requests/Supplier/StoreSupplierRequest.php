<?php
namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'user_id'        => 'required|exists:users,id',
            'jenis_supplier' => 'required|in:bahan_baku,mesin',
            'alamat'         => 'required|string',
            'katalog_produk' => 'nullable|string',
            'status_id'      => 'nullable|exists:statuses,id',
            'bahan_baku_ids' => 'sometimes|array',
            'bahan_baku_ids.*' => 'exists:bahan_bakus,id',
            'mesin_ids'      => 'sometimes|array',
            'mesin_ids.*'    => 'exists:mesins,id',
        ];
    }
}
