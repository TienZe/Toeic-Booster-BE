<?php

namespace App\Repositories;

use App\Entities\PaginatedList;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CollectionRepository
{
    /**
     * Get all collections
     *
     * @return PaginatedList
     */
    public function get(array $options): PaginatedList
    {
        $query = Collection::query();

        if (isset($options['search'])) {
            // Search by collection name
            $query->where('name', 'like', '%' . $options['search'] . '%');
        }

        if (isset($options['categories'])) {
            // Filter by categories
            $query->whereHas('tags', function ($query) use ($options) {
                $query->whereIn('collection_tags.id', $options['categories']);
            });
        }

        return PaginatedList::createFromQueryBuilder($query, $options["page"], $options["limit"]);
    }

    /**
     * Create a new collection
     *
     * @param array $data
     * @return Collection
     */
    public function create(array $data): Collection
    {
        $tags = $data['tags'];
        unset($data['tags']);

        $collection = Collection::create($data);

        $collection->tags()->attach($tags);

        return $collection;
    }

    /**
     * Update an existing collection
     *
     * @param array $data
     * @return Collection|null
     */
    public function update($idOrInstance, array $data): ?Collection
    {
        $collection = $idOrInstance instanceof Collection ? $idOrInstance : Collection::findOrFail($idOrInstance);

        if (isset($data['tags'])) {
            $collection->tags()->sync($data['tags']);

            unset($data['tags']);
        }

        $collection->update($data);

        return $collection;
    }

    /**
     * Delete a collection
     *
     * @param int|string $id
     * @return int
     */
    public function delete(int|string $id): int
    {
        return Collection::destroy($id);
    }
}
