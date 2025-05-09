<?php

namespace App\Http\Controllers;

use App\Http\Requests\LessonVocabulary\BulkStoreLessonVocabularyRequest;
use App\Services\LessonVocabularyService;

class LessonVocabularyController extends Controller
{

    protected $lessonVocabularyService;

    public function __construct(LessonVocabularyService $lessonVocabularyService)
    {
        $this->lessonVocabularyService = $lessonVocabularyService;
    }

    /**
     * Get all vocabularies for a lesson.
     */
    public function index($lessonId)
    {
        $vocabularies = $this->lessonVocabularyService->getLessonVocabularies($lessonId);

        return $vocabularies;
    }

    /**
     * Attach vocabularies to a lesson.
     */
    public function store(BulkStoreLessonVocabularyRequest $request, $lessonId)
    {
        $validated = $request->validated();
        $created = $this->lessonVocabularyService->bulkStore($lessonId,$validated['words']);

        return $created;
    }
}
