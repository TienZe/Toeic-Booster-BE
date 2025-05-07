<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vocabulary\UpdateVocabularyRequest;
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

    public function show(int $id)
    {
        $vocabulary = $this->vocabularyService->getVocabularyById($id);

        return $vocabulary;
    }

    /**
     * Store a newly created vocabulary in storage.
     */
    public function store(StoreVocabularyRequest $request)
    {
        $vocabulary = $this->vocabularyService->createVocabulary($request->validated());

        return $vocabulary;
    }

    /**
     * Update the specified vocabulary in storage.
     */
    public function update(UpdateVocabularyRequest $request, int $id)
    {
        $vocabulary = $this->vocabularyService->updateVocabulary($id, $request->validated());

        return $vocabulary;
    }
}
