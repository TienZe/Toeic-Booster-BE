<?php

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\Base64Image;
class StoreCollectionRequest extends FormRequest
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
            'description' => 'sometimes|nullable|string',
            'book_purchase_link' => 'sometimes|nullable|string|url|max:255',
            'thumbnail' => [
                'sometimes',
                new Base64Image
            ],
            'tags' => 'required|array',
            'tags.*' => 'exists:collection_tags,id',
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
            'thumbnail.base64image' => 'The thumbnail must be a valid image.',
            'tags.required' => 'Please select at least one tag for the collection.',
            'tags.*.exists' => 'The selected tags are invalid.',
        ];
    }
}
