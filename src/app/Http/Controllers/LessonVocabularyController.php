<?php

namespace App\Http\Controllers;

use App\Http\Requests\LessonVocabulary\BulkStoreLessonVocabularyRequest;
use App\Http\Requests\LessonVocabulary\GetLessonVocabulariesRequest;
use App\Models\LessonVocabulary;
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
    public function getLessonVocabularies(GetLessonVocabulariesRequest $request, $lessonId)
    {
        $query = $request->validated();

        $vocabularies = $this->lessonVocabularyService->getLessonVocabularies($lessonId, $query);

        return $vocabularies;
    }

    /**
     * Attach vocabularies to a lesson.
     */
    public function store(BulkStoreLessonVocabularyRequest $request, $lessonId)
    {
        $validated = $request->validated();
        $created = $this->lessonVocabularyService->bulkStore($lessonId, $validated['words']);

        return $created;
    }

    /**
     * Detach a vocabulary from a lesson.
     */
    public function destroy($lessonId, $vocabularyId)
    {
        $deleted = $this->lessonVocabularyService->deleteLessonVocabulary($lessonId, $vocabularyId);

        return [ "deleted" => $deleted ];
    }


    public function delete($lessonVocabularyId)
    {
        $deleted = LessonVocabulary::destroy($lessonVocabularyId);

        return [ "deleted" => $deleted ];
    }
}
