<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreKategoriRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255|unique:kategori_bahan_bakus,nama',
        ];
    }
}
