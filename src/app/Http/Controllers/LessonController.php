<?php

namespace App\Http\Controllers;

use App\Http\Requests\Lesson\StoreLessonRequest;
use App\Http\Requests\Lesson\UpdateLessonRequest;
use App\Models\Collection;
use App\Services\LessonService;

class LessonController extends Controller
{
    private LessonService $lessonService;

    public function __construct(LessonService $lessonService)
    {
        $this->lessonService = $lessonService;
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
    public function show(string $id)
    {
        $lesson = $this->lessonService->getLessonById($id);

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
