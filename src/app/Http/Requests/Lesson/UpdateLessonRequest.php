<?php

namespace App\Http\Requests\Lesson;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'collection_id' => 'sometimes|required|exists:collections,id',
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
            'collection_id.exists' => 'The selected collection does not exist.',
        ];
    }
}
