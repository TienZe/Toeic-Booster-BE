<?php

namespace App\Services;

use App\Models\Lesson;

class WordFolderService
{
    protected LessonVocabularyService $lessonVocabularyService;

    public function __construct(LessonVocabularyService $lessonVocabularyService)
    {
        $this->lessonVocabularyService = $lessonVocabularyService;
    }

    public function getWordFolders($ownerUserId)
    {
        $lessons = Lesson::with('firstLessonVocabulary.vocabulary')
            ->where('user_id', $ownerUserId)
            ->orderBy('created_at', 'desc')
            ->get();

        $lessons->each(function ($lesson) {
            $lesson->append(['reserved_thumbnail']);
            $lesson->makeHidden(['firstLessonVocabulary']);
        });

        return $lessons;
    }

    public function getWordFoldersOfLoggedInUser()
    {
        $wordFolders = $this->getWordFolders(auth()->id());

        return $wordFolders;
    }

    public function createWordFolder($ownerUserId, array $data)
    {
        $wordFolder = Lesson::create([
            ...$data,
            'user_id' => $ownerUserId,
        ]);

        return $wordFolder;
    }

    public function updateWordFolder($wordFolderId, array $data)
    {
        $wordFolder = Lesson::findOrFail($wordFolderId);

        $wordFolder->update($data);

        return $wordFolder;
    }

    public function deleteWordFolder($wordFolderId)
    {
        return Lesson::destroy($wordFolderId);
    }

    public function getWordFolderDetails($wordFolderIdOrName)
    {
        $loggedInUserId = auth()->id();
        $folder = Lesson::where('user_id', $loggedInUserId)
            ->where(function ($query) use ($wordFolderIdOrName) {
                $query->where('id', $wordFolderIdOrName)
                    ->orWhere('name', $wordFolderIdOrName);
            })
            ->firstOrFail();

        $folder->append(['num_of_words']);

        $folder->words = $this->lessonVocabularyService->getLessonVocabularies($folder->id);

        return $folder;
    }

    public function createWordFolderForLoggedInUser(array $data)
    {
        return $this->createWordFolder(auth()->id(), $data);
    }
}
