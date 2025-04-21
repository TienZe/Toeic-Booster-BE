<?php

namespace App\Http\Requests\Collection;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCollectionRequest extends FormRequest
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
            'description' => 'sometimes|nullable|string',
            'book_purchase_link' => 'sometimes|nullable|string|url|max:255',
            'thumbnail' => [
                'sometimes',
                new Base64Image
            ],
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
            'name.required' => 'The collection name is required.',
            'name.max' => 'The collection name cannot exceed 255 characters.',
            'book_purchase_link.url' => 'The book purchase link must be a valid URL.',
        ];
    }
}
