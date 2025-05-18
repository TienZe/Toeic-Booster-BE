<?php

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;

class GetRecommendedCollectionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'filter_title' => 'sometimes|nullable|string',
            'filter_categories' => 'sometimes|array',
            'page' => 'sometimes|integer|min:0',
            'limit' => 'sometimes|integer',
        ];
    }
}
