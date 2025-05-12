<?php

namespace App\Services;

use App\Models\LessonVocabulary;
use App\Repositories\LessonVocabularyRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LessonVocabularyService
{
    protected $lessonVocabularyRepository;

    public function __construct(LessonVocabularyRepository $lessonVocabularyRepository)
    {
        $this->lessonVocabularyRepository = $lessonVocabularyRepository;
    }

    /**
     * Bulk store lesson vocabularies.
     */
    public function bulkStore($lessonId, array $data)
    {
        $created = [];

        DB::transaction(function () use ($lessonId, $data, &$created) {
            foreach ($data as $item) {
                // Upload media files
                // ...

                $created[] = LessonVocabulary::create([
                    'lesson_id' => $lessonId,
                    ...$item
                ]);
            }
        });

        return $created;
    }

    public function getLessonVocabularies($lessonId, $options = [])
    {
        $lessonVocabularies = LessonVocabulary::with('vocabulary')->where('lesson_id', $lessonId);

        if (isset($options['with_user_lesson_learning']) && $options['with_user_lesson_learning']) {
            $userId = Auth::user()->id;

            if (!$userId) {
                throw new \Exception('User not found');
            }

            // Load related user learning in the lesson
            $lessonVocabularies = $lessonVocabularies->with('lessonLearnings', function ($query) use ($userId, $lessonId) {
                $query->where('user_id', $userId);
                $query->where('lesson_id', $lessonId);
            });
        }

        $lessonVocabularies = $lessonVocabularies->get();

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
                "user_lesson_learning" => $lessonVocabulary->lessonLearnings->first(), // only has 1 related user lesson learning for each lesson vocabulary
            ];
        });

        return $lessonVocabularies;
    }

    public function deleteLessonVocabulary($lessonId, $vocabularyId)
    {
        $lessonVocabulary = $this->lessonVocabularyRepository->get($lessonId, $vocabularyId);

        if (!$lessonVocabulary) {
            throw new \Exception('Lesson vocabulary not found');
        }

        // Delete media files
        if ($lessonVocabulary->thumbnail_public_id) {
            Cloudinary::uploadApi()->destroy($lessonVocabulary->thumbnail_public_id);
        }

        if ($lessonVocabulary->pronunciation_audio_public_id) {
            Cloudinary::uploadApi()->destroy($lessonVocabulary->pronunciation_audio_public_id);
        }

        if ($lessonVocabulary->example_audio_public_id) {
            Cloudinary::uploadApi()->destroy($lessonVocabulary->example_audio_public_id);
        }

        return $lessonVocabulary->delete();
    }
}
