<?php

namespace App\Repositories;

use App\Models\LessonVocabulary;

class LessonVocabularyRepository
{
    public function get($lessonId, $vocabularyId)
    {
        $lessonVocabulary = LessonVocabulary::where('lesson_id', $lessonId)->where('vocabulary_id', $vocabularyId)->first();

        return $lessonVocabulary;
    }
}