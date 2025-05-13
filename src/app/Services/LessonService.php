<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lesson;
use App\Repositories\LessonRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class LessonService
{
    private LessonRepository $lessonRepository;

    public function __construct(LessonRepository $lessonRepository)
    {
        $this->lessonRepository = $lessonRepository;
    }

    /**
     * Get lesson by ID
     *
     * @param int|string $id
     * @return Lesson
     */
    public function getLessonById(int|string $id): Lesson
    {
        return Lesson::findOrFail($id);
    }

    /**
     * Get all lessons
     *
     * @return Collection
     */
    public function getAllLessons(): Collection
    {
        return $this->lessonRepository->getAll();
    }

    /**
     * Create a new lesson
     *
     * @param array $data
     * @return Lesson
     */
    public function createLesson(array $data): Lesson
    {
        if (!empty($data['thumbnail'])) {
            $thumbnail = Cloudinary::uploadApi()->upload($data['thumbnail'], [
                "folder" => Lesson::THUMBNAIL_FOLDER,
            ]);

            $data['thumbnail'] = $thumbnail['secure_url'];
            $data['thumbnail_public_id'] = $thumbnail['public_id'];
        }
        return $this->lessonRepository->create($data);
    }

    /**
     * Update an existing lesson
     *
     * @param int|string $id
     * @param array $data
     * @return Lesson|null
     */
    public function updateLesson(int|string $id, array $data): ?Lesson
    {
        if (!empty($data['thumbnail'])) {
            // Upload new thumbnail
            $newThumbnail = Cloudinary::uploadApi()->upload($data['thumbnail'], [
                "folder" => Lesson::THUMBNAIL_FOLDER,
            ]);

            // Delete old thumbnail
            $lesson = $this->getLessonById($id);
            if ($lesson->thumbnail_public_id) {
                Cloudinary::uploadApi()->destroy($lesson->thumbnail_public_id);
            }

            $data['thumbnail'] = $newThumbnail['secure_url'];
            $data['thumbnail_public_id'] = $newThumbnail['public_id'];
        }

        return $this->lessonRepository->update($id, $data);
    }

    /**
     * Delete a lesson
     *
     * @param int|string $id
     * @return int
     */
    public function deleteLesson(int|string $id): int
    {
        $lesson = $this->getLessonById($id);

        // Delete thumbnail from Cloudinary
        if ($lesson->thumbnail_public_id) {
            Cloudinary::uploadApi()->destroy($lesson->thumbnail_public_id);
        }

        return $this->lessonRepository->delete($id);
    }

    public function getLessons($collectionId, $options = [])
    {
        $lessons = Lesson::where('collection_id', $collectionId);

        $withUserLearningProgress = isset($options['with_user_learning_progress']) && $options['with_user_learning_progress'];
        if ($withUserLearningProgress) {
            $userId = Auth::user()->id;

            if (!$userId) {
                throw new \Exception('User not found');
            }

            // Load filtering result of the logged in user
            $lessons->with(['lessonLearnings' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }]);

            // Load latest exam result of the logged in user
            $lessons->with(['lessonExams' => function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->limit(1);
            }]);
        }

        $lessons = $lessons->get();

        if ($withUserLearningProgress) {
            foreach ($lessons as $lesson) {
                // Set learning step for each lesson
                $lesson->learning_step = $lesson->lessonLearnings->isNotEmpty() ? Lesson::LEARNING_STEP_FILTERED : null;
                $lesson->learning_step = $lesson->lessonExams->isNotEmpty() ? Lesson::LEARNING_STEP_EXAMINED : $lesson->learning_step;
            }

            foreach ($lessons as $lesson) {
                $latestExam = $lesson->lessonExams->first();
                $retainedWords = 0;

                if ($latestExam) {
                    $latestExam->loadCount(['answers as retained_words' => function ($query) {
                        $query->where('is_correct', 1);
                    }]);

                    $retainedWords = $latestExam->retained_words;
                }

                $lesson->retained_words = $retainedWords;
            }
        }

        foreach ($lessons as $lesson) {
            unset($lesson->lessonLearnings);
            unset($lesson->lessonExams);
        }

        return $lessons;
    }
}
