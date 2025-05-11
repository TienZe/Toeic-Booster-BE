<?php

namespace App\Http\Controllers;

use App\Http\Requests\LessonLearning\SaveLessonLearningRequest;
use App\Services\LessonLearningService;
use Illuminate\Http\Request;

class LessonLearningController extends Controller
{
    private $lessonLearningService;

    public function __construct(LessonLearningService $lessonLearningService)
    {
        $this->lessonLearningService = $lessonLearningService;
    }

    public function save(SaveLessonLearningRequest $request, $lessonId)
    {
        $loggedInUser = $request->user();
        return $this->lessonLearningService->syncLessonLearnings($loggedInUser->id, $lessonId, $request->input('lesson_learnings'));
    }

    public function getUserLessonVocabularyFilteringResult(Request $request, $lessonId)
    {
        $loggedInUser = $request->user();
        return $this->lessonLearningService->getUserLessonVocabularyFilteringResult($loggedInUser->id, $lessonId);
    }
}
