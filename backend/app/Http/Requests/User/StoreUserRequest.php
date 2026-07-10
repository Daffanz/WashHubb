<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'role'      => ['required', 'string', 'exists:roles,name'],
            'status_id' => 'nullable|exists:statuses,id',
        ];
    }
}
