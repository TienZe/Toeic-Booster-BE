<?php

namespace App\Repositories;

use App\Models\LessonLearning;
use Illuminate\Database\Eloquent\Collection;

class LessonLearningRepository
{
    public function delete($userId, $lessonId)
    {
        return LessonLearning::where('user_id', $userId)->where('lesson_id', $lessonId)->delete();
    }

    public function get($userId, $lessonId)
    {
        return LessonLearning::where('user_id', $userId)->where('lesson_id', $lessonId)->get();
    }
}