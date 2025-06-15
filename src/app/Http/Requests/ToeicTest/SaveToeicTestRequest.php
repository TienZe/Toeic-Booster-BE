<?php

namespace App\Http\Requests\ToeicTest;

use App\Enums\MediaFileType;
use App\Enums\ToeicPart;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveToeicTestRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => 'sometimes|integer',
            'name' => 'required_without:id|string',
            'category' => 'required_without:id|integer|exists:toeic_test_categories,id',
            'status' => 'sometimes|required|string|in:active,inactive,pending',
            'question_groups' => 'sometimes|array',
            'question_groups.*.id' => 'sometimes',
            'question_groups.*.transcript' => 'sometimes|nullable|string',
            'question_groups.*.passage' => 'sometimes|nullable|string',
            'question_groups.*.part' => ['sometimes', Rule::in(ToeicPart::values())],
            'question_groups.*.group_index' => 'integer',
            'question_groups.*.questions' => 'sometimes|array',
            'question_groups.*.questions.*.id' => 'sometimes|integer',
            'question_groups.*.questions.*.question_number' => 'sometimes|integer',
            'question_groups.*.questions.*.question' => 'sometimes|nullable|string',
            'question_groups.*.questions.*.explanation' => 'sometimes|nullable|string',
            'question_groups.*.questions.*.A' => 'sometimes|nullable|string',
            'question_groups.*.questions.*.B' => 'sometimes|nullable|string',
            'question_groups.*.questions.*.C' => 'sometimes|nullable|string',
            'question_groups.*.questions.*.D' => 'sometimes|nullable|string',
            'question_groups.*.questions.*.correct_answer' => 'sometimes|nullable|string|in:A,B,C,D',
            'question_groups.*.medias' => 'sometimes|array',
            'question_groups.*.medias.*.id' => 'sometimes|integer',
            'question_groups.*.medias.*.file_url' => 'sometimes|string',
            'question_groups.*.medias.*.order' => 'sometimes|nullable|integer',
            'question_groups.*.medias.*.file_type' => ['sometimes', Rule::in(MediaFileType::IMAGE, MediaFileType::AUDIO)],
        ];
    }
}
