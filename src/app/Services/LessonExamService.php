<?php

namespace App\Services;

use App\Models\LessonExam;
use App\Models\LessonExamAnswer;
use Illuminate\Support\Facades\DB;

class LessonExamService
{
    /**
     * Create a lesson exam with answers.
     *
     * @param array $data
     * @return LessonExam
     */
    public function createLessonExamWithAnswers(array $data): LessonExam
    {
        return DB::transaction(function () use ($data) {
            $lessonExam = LessonExam::create([
                'lesson_id' => $data['lesson_id'],
                'user_id' => $data['user_id'],
                'duration' => $data['duration'],
            ]);

            foreach ($data['answers'] as $answer) {
                $lessonExam->answers()->create([
                    'lesson_vocabulary_id' => $answer['lesson_vocabulary_id'],
                    'is_correct' => $answer['is_correct'],
                ]);
            }

            return $lessonExam;
        });
    }
}
