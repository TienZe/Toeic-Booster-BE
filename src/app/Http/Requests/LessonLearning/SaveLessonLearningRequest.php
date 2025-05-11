<?php

namespace App\Http\Requests\LessonLearning;

use Illuminate\Foundation\Http\FormRequest;

class SaveLessonLearningRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "lesson_learnings" => "required|array",
            "lesson_learnings.*.lesson_vocabulary_id" => "required|exists:lesson_vocabularies,id",
            "lesson_learnings.*.already_known" => "required|boolean",
        ];
    }
}
