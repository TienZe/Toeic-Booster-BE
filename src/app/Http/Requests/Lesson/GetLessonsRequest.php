<?php

namespace App\Http\Requests\Lesson;

use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;

class GetLessonsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'with_user_learning_step' => 'sometimes|boolean',
        ];
    }
}
