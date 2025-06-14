<?php

namespace App\Http\Requests\ToeicChatHistory;

use Illuminate\Foundation\Http\FormRequest;

class CreateToeicChatHistoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'attempt_id' => 'required|exists:toeic_test_attempts,id',
            'question_id' => 'required|exists:questions,id',
        ];
    }
}
