<?php

namespace App\Services;

use App\Models\LessonVocabulary;
use Illuminate\Support\Facades\DB;

class LessonVocabularyService
{
    /**
     * Bulk store lesson vocabularies.
     */
    public function bulkStore($lessonId, array $data)
    {
        $created = [];

        DB::transaction(function () use ($lessonId, $data, &$created) {
            foreach ($data as $item) {
                $created[] = LessonVocabulary::create([
                    'lesson_id' => $lessonId,
                    ...$item
                ]);
            }
        });

        return $created;
    }

    public function getLessonVocabularies($lessonId)
    {
        $lessonVocabularies = LessonVocabulary::with('vocabulary')->where('lesson_id', $lessonId)->get();

        $lessonVocabularies = $lessonVocabularies->map(function ($lessonVocabulary) {
            $defaultVocabulary = $lessonVocabulary->vocabulary;

            return [
                "id" => $lessonVocabulary->id,
                "lesson_id" => $lessonVocabulary->lesson_id,
                "vocabulary_id" => $lessonVocabulary->vocabulary_id,
                "word" => $defaultVocabulary?->word,
                "thumbnail" => $lessonVocabulary->thumbnail ?? $defaultVocabulary?->thumbnail,
                "part_of_speech" => $lessonVocabulary->part_of_speech ?? $defaultVocabulary?->part_of_speech,
                "meaning" => $lessonVocabulary->meaning ?? $defaultVocabulary?->meaning,
                "definition" => $lessonVocabulary->definition ?? $defaultVocabulary?->definition,
                "pronunciation" => $lessonVocabulary->pronunciation ?? $defaultVocabulary?->pronunciation,
                "pronunciation_audio" => $lessonVocabulary->pronunciation_audio ?? $defaultVocabulary?->pronunciation_audio,
                "example" => $lessonVocabulary->example ?? $defaultVocabulary?->example,
                "example_meaning" => $lessonVocabulary->example_meaning ?? $defaultVocabulary?->example_meaning,
                "example_audio" => $lessonVocabulary->example_audio ?? $defaultVocabulary?->example_audio,
            ];
        });

        return $lessonVocabularies;
    }
}
