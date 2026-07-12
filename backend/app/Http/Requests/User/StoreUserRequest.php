<?php
namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nama'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'no_telp'   => 'nullable|string|max:20',
            'role_id'   => 'required|exists:roles,id',
            'status_id' => 'nullable|exists:statuses,id',
        ];
    }
}
