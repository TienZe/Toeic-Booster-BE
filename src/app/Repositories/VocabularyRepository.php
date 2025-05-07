<?php

namespace App\Repositories;

use App\Entities\PaginatedList;
use App\Models\Vocabulary;
use Illuminate\Database\Eloquent\Collection;

class VocabularyRepository
{
    public function find(int $id): Vocabulary
    {
        return Vocabulary::findOrFail($id);
    }

    public function get(array $options): PaginatedList
    {
        $query = Vocabulary::query();

        if (isset($options['search'])) {
            $searchKey = $options['search'];
            $query->where('word', 'like', '%' . $searchKey . '%');
        }

        return PaginatedList::createFromQueryBuilder($query, $options["page"] ?? 1, $options["limit"] ?? 10);
    }

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
