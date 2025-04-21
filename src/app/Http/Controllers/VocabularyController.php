<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Vocabulary\StoreVocabularyRequest;
use App\Services\VocabularyService;

class VocabularyController extends Controller
{
    private VocabularyService $vocabularyService;

    public function __construct(VocabularyService $vocabularyService)
    {
        $this->vocabularyService = $vocabularyService;
    }

    /**
     * Store a newly created vocabulary in storage.
     */
    public function store(StoreVocabularyRequest $request)
    {
        $vocabulary = $this->vocabularyService->createVocabulary($request->validated());

        return $vocabulary;
    }
}
