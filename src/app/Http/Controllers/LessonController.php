<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lesson\StoreLessonRequest;
use App\Http\Requests\Lesson\UpdateLessonRequest;
use App\Models\Collection;
use App\Services\LessonService;
use App\Services\LessonVocabularyService;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    private LessonService $lessonService;
    private LessonVocabularyService $lessonVocabularyService;

    public function __construct(LessonService $lessonService, LessonVocabularyService $lessonVocabularyService)
    {
        $this->lessonService = $lessonService;
        $this->lessonVocabularyService = $lessonVocabularyService;
    }

    /**
     * Display a listing of the lessons.
     */
    public function index(Collection $collection)
    {
        return $collection->lessons;
    }

    /**
     * Store a newly created lesson in storage.
     */
    public function store(StoreLessonRequest $request, Collection $collection)
    {
        $input = $request->validated();
        $input['collection_id'] = $collection->id;

        $lesson = $this->lessonService->createLesson($input);

        return $lesson;
    }

    /**
     * Display the specified lesson.
     */
    public function show(Request $request, string $id)
    {
        $lesson = $this->lessonService->getLessonById($id);

        if (isset($request->with_words)) {
            $lesson->words = $this->lessonVocabularyService->getLessonVocabularies($lesson->id);
        }

        return $lesson;
    }

    /**
     * Update the specified lesson in storage.
     */
    public function update(UpdateLessonRequest $request, string $id)
    {
        $lesson = $this->lessonService->updateLesson($id, $request->validated());

        return $lesson;
    }

    /**
     * Remove the specified lesson from storage.
     */
    public function destroy(string $id)
    {
        $deleted = $this->lessonService->deleteLesson($id);

        return ['deleted' => $deleted];
    }
}
