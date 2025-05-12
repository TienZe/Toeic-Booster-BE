<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LessonExamStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lesson_id' => ['required', 'integer', 'exists:lessons,id'],
            'duration' => ['required', 'integer', 'min:1'],
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.lesson_vocabulary_id' => ['required', 'integer', 'exists:lesson_vocabularies,id'],
            'answers.*.is_correct' => ['required', 'boolean'],
        ];
    }
}
