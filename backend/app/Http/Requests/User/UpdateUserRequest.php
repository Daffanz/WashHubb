<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'      => 'sometimes|string|max:255',
            'email'     => 'sometimes|email|unique:users,email,' . $this->user->id,
            'password'  => 'sometimes|string|min:8',
            'role'      => 'sometimes|string|exists:roles,name',
            'status_id' => 'sometimes|nullable|exists:statuses,id',
        ];
    }
}
