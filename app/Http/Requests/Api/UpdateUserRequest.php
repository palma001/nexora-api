<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Handled by Controller Policy
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . ($this->user->id ?? $this->route('user')->id),
            'username' => 'nullable|string|max:255|unique:users,username,' . ($this->user->id ?? $this->route('user')->id),
            'password' => 'nullable|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }
}
