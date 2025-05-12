<?php

namespace App\Http\Controllers;

use App\Http\Requests\LessonExamStoreRequest;
use App\Services\LessonExamService;
use Illuminate\Support\Facades\Auth;

class LessonExamController extends Controller
{
    protected $lessonExamService;

    public function __construct(LessonExamService $lessonExamService)
    {
        $this->lessonExamService = $lessonExamService;
    }

    public function store(LessonExamStoreRequest $request)
    {
        $postData = $request->validated();
        $userId = Auth::user()->id;
        $postData['user_id'] = $userId;

        $lessonExam = $this->lessonExamService->createLessonExamWithAnswers($postData);

        return $lessonExam;
    }
}
