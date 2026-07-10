<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreMutasiRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'stok_type'    => 'required|string|in:App\Models\StokPusatBahanBaku,App\Models\StokPusatMesin',
            'stok_id'      => 'required|integer',
            'jenis_mutasi' => 'required|in:masuk,keluar,penyesuaian',
            'jumlah'       => 'required|numeric|min:0.0001',
            'keterangan'   => 'nullable|string',
        ];
    }
}
