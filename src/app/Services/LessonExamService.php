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

    public function getLessonPracticeStatistics($lessonId, $userId)
    {
        $lessonExam = LessonExam::with('answers.lessonVocabulary')
            ->where('lesson_id', $lessonId)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->take(2)
            ->get();

        $currentPractice = $lessonExam->first();
        $mostRecentPractice = $lessonExam->last();


        $bestPracticeId = LessonExam::select([
                'le.id',
                DB::raw('COUNT(lea.id) as total_answers'),
                DB::raw('SUM(lea.is_correct) as correct_answers'),
                DB::raw('(SUM(lea.is_correct) / COUNT(lea.id)) as accuracy')
            ])
            ->from('lesson_exams as le')
            ->join('lesson_exam_answers as lea', 'le.id', '=', 'lea.lesson_exam_id')

            ->where('le.lesson_id', $lessonId)
            ->where('le.user_id', $userId)
            ->groupBy('le.id')
            ->orderBy('accuracy', 'desc')
            ->pluck('id')
            ->first();

        $bestPractice = LessonExam::with('answers.lessonVocabulary')
            ->find($bestPracticeId);

        $practiceArr = [
            'current' => $currentPractice,
            'mostRecent' => $mostRecentPractice,
            'best' => $bestPractice,
        ];

        foreach ($practiceArr as $key => $practice) {
            $correctAnswers = $practice->answers->filter(function ($answer) {
                return $answer->is_correct;
            });
            $incorrectAnswers = $practice->answers->filter(function ($answer) {
                return !$answer->is_correct;
            });

            $correctWords = $correctAnswers->map(function ($answer) {
                return $answer->lessonVocabulary;
            });
            $incorrectWords = $incorrectAnswers->map(function ($answer) {
                return $answer->lessonVocabulary;
            });

            $dto = [
                'id' => $practice->id,
                'correctWords' => $correctWords->map(function ($lessonVoca) {
                    return LessonVocabularyService::getLessonVocabularyDTO($lessonVoca);
                }),
                'incorrectWords' => $incorrectWords->map(function ($lessonVoca) {
                    return LessonVocabularyService::getLessonVocabularyDTO($lessonVoca);
                }),
                'duration' => $practice->duration,
                'numCorrect' => $correctAnswers->count(),
                'totalWords' => $practice->answers->count(),
            ];

            $practiceArr[$key] = $dto;
        }

        return $practiceArr;
    }
}
