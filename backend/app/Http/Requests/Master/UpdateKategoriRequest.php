<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKategoriRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama' => 'sometimes|string|max:255|unique:kategori_bahan_bakus,nama,' . $this->route('kategori')?->id,
        ];
    }
}
