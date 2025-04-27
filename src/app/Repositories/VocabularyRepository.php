<?php

namespace App\Repositories;

use App\Models\Vocabulary;

class VocabularyRepository
{
    /**
     * Create a new vocabulary
     *
     * @param array $data
     * @return Vocabulary
     */
    public function create(array $data): Vocabulary
    {
        return Vocabulary::create($data);
    }

    public function update($idOrInstance, array $data): ?Vocabulary
    {
        $vocabulary = $idOrInstance instanceof Vocabulary ? $idOrInstance : Vocabulary::findOrFail($idOrInstance);

        $vocabulary->update($data);

        return $vocabulary;
    }
}
