<?php

namespace App\Services;

use App\Models\Lesson;

class WordFolderService
{
    public function getWordFolders($ownerUserId)
    {
        $lessons = Lesson::with('firstLessonVocabulary.vocabulary')
            ->where('user_id', $ownerUserId)
            ->orderBy('created_at', 'desc')
            ->get();

        $lessons->each(function ($lesson) {
            $lesson->append(['reserved_thumbnail']);
        });

        return $lessons;
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
}