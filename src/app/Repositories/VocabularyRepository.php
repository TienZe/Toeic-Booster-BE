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
}
