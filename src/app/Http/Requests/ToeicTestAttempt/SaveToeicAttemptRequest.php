<?php

namespace App\Http\Requests\ToeicTestAttempt;

use App\Enums\ToeicPart;
use Illuminate\Foundation\Http\FormRequest;

class SaveToeicAttemptRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'toeic_test_id' => 'required|numeric',
            'taken_time' => 'required|numeric',
            'selected_parts' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $parts = explode(',', $value);
                    $validParts = ToeicPart::values();
                    foreach ($parts as $part) {
                        if (!in_array(trim($part), $validParts, true)) {
                            return $fail("The {$attribute} contains an invalid part: {$part}.");
                        }
                    }
                },
            ],
            'user_answers.*.question_id' => 'required|numeric',
            'user_answers.*.choice' => 'required|string',
        ];
    }
}
