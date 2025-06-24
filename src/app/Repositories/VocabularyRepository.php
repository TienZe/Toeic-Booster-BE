<?php

namespace App\Repositories;

use App\Entities\PaginatedList;
use App\Models\Vocabulary;

class VocabularyRepository
{
    public function find(int $id): Vocabulary
    {
        return Vocabulary::findOrFail($id);
    }

    public function get(array $options): PaginatedList
    {
        $query = Vocabulary::query()->orderByDesc('id');

        if (isset($options['search'])) {
            $searchKey = $options['search'];
            $query->where(function ($query) use ($searchKey) {
                $query->where('word', 'like', '%' . $searchKey . '%')
                    ->orWhere('meaning', 'like', '%' . $searchKey . '%')
                    ->orWhere('id', 'like', '%' . $searchKey . '%');
            });
        }

        return PaginatedList::createFromQueryBuilder($query, $options["page"] ?? 0, $options["limit"] ?? 10);
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
