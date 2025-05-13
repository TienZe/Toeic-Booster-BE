<?php

namespace App\Http\Controllers;

use App\Http\Requests\WordFolder\PostNewWordFolderRequest;
use App\Http\Requests\WordFolder\UpdateWordFolderRequest;
use App\Services\WordFolderService;
use Illuminate\Support\Facades\Auth;

class WordFolderController extends Controller
{
    private WordFolderService $wordFolderService;

    public function __construct(WordFolderService $wordFolderService)
    {
        $this->wordFolderService = $wordFolderService;
    }

    public function index()
    {
        return $this->wordFolderService->getWordFolders(Auth::user()->id);
    }

    public function store(PostNewWordFolderRequest $request)
    {
        return $this->wordFolderService->createWordFolder(Auth::user()->id, $request->validated());
    }

    public function update(UpdateWordFolderRequest $request, $id)
    {
        return $this->wordFolderService->updateWordFolder($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->wordFolderService->deleteWordFolder($id);
    }

}
