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
}
