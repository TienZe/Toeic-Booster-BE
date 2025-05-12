<?php

namespace App\Http\Requests\LessonVocabulary;

use Illuminate\Foundation\Http\FormRequest;

final class GetLessonVocabulariesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'with_user_lesson_learning' => 'sometimes|boolean',
        ];
    }
}
