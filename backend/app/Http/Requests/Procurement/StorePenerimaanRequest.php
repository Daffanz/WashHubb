<?php

namespace App\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class StorePenerimaanRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'distribusi_barang_id' => 'required|exists:distribusi_barangs,id',
            'tanggal_terima'       => 'required|date',
            'total_bayar'          => 'required|numeric|min:0',
            'catatan'              => 'nullable|string',
        ];
    }
}
