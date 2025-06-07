<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class GetPaginatedUsersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page' => 'sometimes|integer|min:0',
            'limit' => 'sometimes|integer|min:1',
            'search' => 'sometimes',
            'filtered_status' => 'sometimes|in:active,inactive',
        ];
    }
}
