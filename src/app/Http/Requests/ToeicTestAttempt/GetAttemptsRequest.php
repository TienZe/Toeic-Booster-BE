<?php

namespace App\Http\Requests\ToeicTestAttempt;

use Illuminate\Foundation\Http\FormRequest;

class GetAttemptsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'recent_days' => 'sometimes|string',
            'limit' => 'sometimes|integer',
            'page' => 'sometimes|integer',
            'toeic_test_id' => 'sometimes|integer',
        ];
    }
}
