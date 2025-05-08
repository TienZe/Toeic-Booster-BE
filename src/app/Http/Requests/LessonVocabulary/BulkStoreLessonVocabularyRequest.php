<?php

declare(strict_types=1);

namespace App\Http\Requests\LessonVocabulary;

use Illuminate\Foundation\Http\FormRequest;

final class BulkStoreLessonVocabularyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'words' => 'required|array',
            'words.*.vocabulary_id' => 'required|integer',
            'words.*.thumbnail' => 'nullable|string',
            'words.*.part_of_speech' => 'nullable|string',
            'words.*.meaning' => 'nullable|string',
            'words.*.definition' => 'nullable|string',
            'words.*.pronunciation' => 'nullable|string',
            'words.*.pronunciation_audio' => 'nullable|string',
            'words.*.example' => 'nullable|string',
            'words.*.example_meaning' => 'nullable|string',
            'words.*.example_audio' => 'nullable|string',
        ];
    }
}
