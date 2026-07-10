<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBahanBakuRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'kategori_id'   => 'sometimes|exists:kategori_bahan_bakus,id',
            'nama'          => 'sometimes|string|max:255',
            'satuan'        => 'sometimes|string|max:50',
            'harga_standar' => 'sometimes|numeric|min:0',
        ];
    }
}
