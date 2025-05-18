<?php

namespace App\Http\Requests\Collection;

use Illuminate\Foundation\Http\FormRequest;

class GetRecommendedCollectionRequest extends FormRequest
{
    public function rules(): array
    {
        $data = parent::all();
        return [
            'filter_title' => 'sometimes|nullable|string',
            'filter_categories' => 'sometimes|array',
        ];
    }
}
