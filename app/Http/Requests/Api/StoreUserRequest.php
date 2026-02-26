<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Handled by Controller Policy
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:8',
            'role_id' => 'required_without:role_ids|exists:roles,id',
            'role_ids' => 'required_without:role_id|array',
            'role_ids.*' => 'exists:roles,id',
        ];
    }
}
