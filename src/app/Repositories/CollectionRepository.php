<?php

namespace App\Repositories;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CollectionRepository
{
    /**
     * Get all collections
     *
     * @return EloquentCollection
     */
    public function getAll(): EloquentCollection
    {
        return Collection::all();
    }

    /**
     * Create a new collection
     *
     * @param array $data
     * @return Collection
     */
    public function create(array $data): Collection
    {
        return Collection::create($data);
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
