<?php

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;

class GetListOfCollectionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'page' => 'required|integer|min:0',
            'limit' => 'sometimes|integer|min:1',
            'search' => 'sometimes|nullable|string|max:255',
            'categories' => 'sometimes|array',
        ];
    }
}
