<?php

namespace App\Http\Requests\ToeicChatHistory;

use Illuminate\Foundation\Http\FormRequest;

class CreateToeicChatHistoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'attempt_id' => 'required|exists:toeic_test_attempts,id',
            'question_number' => 'required|integer|min:1|max:200',
            'question_id' => 'required_if:question_number,null|exists:questions,id',
        ];
    }
}
