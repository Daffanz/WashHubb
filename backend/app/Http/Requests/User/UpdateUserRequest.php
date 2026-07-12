<?php
namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $this->route('user')->id,
            'password'  => 'sometimes|string|min:8',
            'no_telp'   => 'sometimes|nullable|string|max:20',
            'role_id'   => 'sometimes|exists:roles,id',
            'status_id' => 'sometimes|nullable|exists:statuses,id',
            'wajib_ganti_password' => 'sometimes|boolean',
        ];
    }
}
