<?php

namespace App\Http\Requests\LessonVocabulary;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLessonVocabularyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'thumbnail' => 'sometimes|string',
            'part_of_speech' => 'sometimes|string',
            'meaning' => 'sometimes|string',
            'definition' => 'sometimes|string',
            'pronunciation' => 'sometimes|string',
            'pronunciation_audio' => 'sometimes|string',
            'example' => 'sometimes|string',
            'example_meaning' => 'sometimes|string',
            'example_audio' => 'sometimes|string',
        ];
    }
}
