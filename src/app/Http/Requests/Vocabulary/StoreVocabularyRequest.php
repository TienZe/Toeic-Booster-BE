<?php

namespace App\Http\Requests\Vocabulary;

use App\Enums\PartOfSpeech;
use App\Rules\Base64Image;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVocabularyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'word' => 'required|string|max:255|unique:vocabularies',
            'thumbnail' => ['sometimes', 'string', new Base64Image],
            'part_of_speech' => [
                'required',
                'string',
                Rule::enum(PartOfSpeech::class),
            ],
            'meaning' => 'required|string',
            'definition' => 'nullable|string',
            'pronunciation' => 'required|string',
            'pronunciation_audio' => 'sometimes|required|string',
            'example' => 'nullable|string',
            'example_meaning' => 'nullable|string',
            'example_audio' => 'nullable|string',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'word.required' => 'The word is required.',
            'word.unique' => 'This word already exists in the vocabulary.',
            'part_of_speech.required' => 'The part of speech is required.',
            'part_of_speech.in' => 'The selected part of speech is invalid.',
            'meaning.required' => 'The meaning is required.',
        ];
    }
}
