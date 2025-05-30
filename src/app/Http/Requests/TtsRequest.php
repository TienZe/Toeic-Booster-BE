<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TtsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'min:1'],
        ];
    }
}
