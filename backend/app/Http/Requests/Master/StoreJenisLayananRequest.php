<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreJenisLayananRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama'                => 'required|string|max:255|unique:jenis_layanans,nama',
            'harga_standar_per_kg' => 'required|numeric|min:0',
        ];
    }
}
