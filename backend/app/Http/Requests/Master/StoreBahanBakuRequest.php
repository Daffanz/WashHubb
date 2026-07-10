<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreBahanBakuRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'kategori_id'   => 'required|exists:kategori_bahan_bakus,id',
            'nama'          => 'required|string|max:255',
            'satuan'        => 'required|string|max:50',
            'harga_standar' => 'required|numeric|min:0',
        ];
    }
}
