<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class StoreMesinRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama'        => 'required|string|max:255',
            'kode_mesin'  => 'required|string|max:255|unique:mesins,kode_mesin',
            'merk'        => 'nullable|string|max:255',
            'tipe'        => 'nullable|string|max:255',
            'kapasitas'   => 'nullable|integer|min:0',
        ];
    }
}
