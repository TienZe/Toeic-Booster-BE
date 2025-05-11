<?php

namespace App\Services;

use App\Models\LessonLearning;
use App\Models\User;
use App\Repositories\LessonLearningRepository;

class LessonLearningService
{
    protected $lessonLearningRepository;

    public function __construct(LessonLearningRepository $lessonLearningRepository)
    {
        $this->lessonLearningRepository = $lessonLearningRepository;
    }

    /**
     * Sync lesson learning data for a user.
     *
     * @param User $user
     * @param array $lessonLearnings
     *      Each item: ['lesson_vocabulary_id' => int, 'already_known' => bool]
     */
    public function syncLessonLearnings($userId, $lessonId, array $lessonLearnings)
    {
        // Prepare data for sync: [lesson_vocabulary_id => ['already_known' => bool]]
        $syncData = [];
        foreach ($lessonLearnings as $item) {
            $syncData[] = [
                'user_id' => $userId,
                'lesson_id' => $lessonId,
                'lesson_vocabulary_id' => $item['lesson_vocabulary_id'], // belongs to the lesson id `lesson_id`
                'already_known' => $item['already_known'],
            ];
        }

        $this->lessonLearningRepository->delete($userId, $lessonId);

        $savedLessonLearnings = [];
        foreach ($syncData as $data) {
            $lessonLearning = LessonLearning::create($data);
            $savedLessonLearnings[] = $lessonLearning;
        }

        return $savedLessonLearnings;
    }

    public function getLessonLearnings($userId, $lessonId)
    {
        return $this->lessonLearningRepository->get($userId, $lessonId);
    }

    public function getUserLessonVocabularyFilteringResult($userId, $lessonId)
    {
        $lessonLearnings = $this->getLessonLearnings($userId, $lessonId);

        $knownVocabularyIds = $lessonLearnings->where('already_known', true)->count();
        $totalVocabularyIds = $lessonLearnings->count();

        return [
            'known_count' => $knownVocabularyIds,
            'unknown_count' => $totalVocabularyIds - $knownVocabularyIds,
            'total_count' => $totalVocabularyIds,
        ];
    }
}
