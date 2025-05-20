<?php

namespace App\Http\Requests\ToeicTest;

use Illuminate\Foundation\Http\FormRequest;

class GetToeicTestsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:0',
            'limit' => 'sometimes|integer|min:1',
            'search' => 'sometimes|string',
            'filteredTag' => 'sometimes|numeric'
        ];
    }
}
