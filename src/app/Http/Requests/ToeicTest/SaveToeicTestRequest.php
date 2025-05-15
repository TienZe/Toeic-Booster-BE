<?php

namespace App\Http\Requests\ToeicTest;

use App\Enums\MediaFileType;
use App\Enums\ToeicPart;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveToeicTestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'sometimes|integer',
            'name' => 'sometimes|string',
            'question_groups' => 'sometimes|array',
            'question_groups.*.id' => 'sometimes|integer',
            'question_groups.*.transcript' => 'sometimes|string',
            'question_groups.*.part' => ['sometimes', Rule::in(ToeicPart::values())],
            'question_groups.*.questions' => 'sometimes|array',
            'question_groups.*.questions.*.id' => 'sometimes|integer',
            'question_groups.*.questions.*.questionNumber' => 'sometimes|integer',
            'question_groups.*.questions.*.question' => 'sometimes|string',
            'question_groups.*.questions.*.explanation' => 'sometimes|string',
            'question_groups.*.questions.*.A' => 'sometimes|string',
            'question_groups.*.questions.*.B' => 'sometimes|string',
            'question_groups.*.questions.*.C' => 'sometimes|string',
            'question_groups.*.questions.*.D' => 'sometimes|string',
            'question_groups.*.questions.*.correctAnswer' => 'sometimes|string|in:A,B,C,D',
            'question_groups.*.medias' => 'sometimes|array',
            'question_groups.*.medias.*.id' => 'sometimes|integer',
            'question_groups.*.medias.*.fileUrl' => 'sometimes|string',
            'question_groups.*.medias.*.order' => 'sometimes|integer',
            'question_groups.*.medias.*.fileType' => ['sometimes', Rule::in(MediaFileType::IMAGE, MediaFileType::AUDIO)],
        ];
    }
}
