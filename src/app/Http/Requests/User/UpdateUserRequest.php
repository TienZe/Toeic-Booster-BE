<?php

namespace App\Http\Requests\User;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'avatar' => ['sometimes', 'required', new Base64Image],
            'new_password' => ['sometimes', 'required', 'string', 'min:6', 'confirmed'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'avatar.required' => 'The avatar field is required.',
            'new_password.required' => 'The new password field is required.',
            'new_password.string' => 'The new password must be a string.',
            'new_password.min' => 'The new password must be at least 6 characters.',
            'new_password.confirmed' => 'The new password confirmation does not match.',
            'status.required' => 'The status field is required.',
            'status.in' => 'The status must be either active or inactive.',
        ];
    }
}
