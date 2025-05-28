<?php

namespace App\Http\Requests\ToeicTestAttempt;

use Illuminate\Foundation\Http\FormRequest;

class GetAttemptDetailsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'with_result_summary' => 'boolean',
        ];
    }
}
