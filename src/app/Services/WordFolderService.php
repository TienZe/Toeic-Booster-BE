<?php

namespace App\Services;

use App\Models\Lesson;

class WordFolderService
{
    public function getWordFolders($ownerUserId)
    {
        return Lesson::where('user_id', $ownerUserId)
            ->orderBy('created_at', 'desc')
            ->get();
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