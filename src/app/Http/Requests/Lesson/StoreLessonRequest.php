<?php

namespace App\Http\Requests\Lesson;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'thumbnail' => ['sometimes', new Base64Image]
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.max' => 'The lesson name cannot exceed 255 characters.',
            'thumbnail.base64image' => 'The thumbnail must be a valid base64 image.',
        ];
    }
}
