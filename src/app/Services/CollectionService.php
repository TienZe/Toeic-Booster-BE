<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\PaginatedList;
use App\Models\Collection;
use App\Repositories\CollectionRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class CollectionService
{
    private CollectionRepository $collectionRepository;

    public function __construct(CollectionRepository $collectionRepository)
    {
        $this->collectionRepository = $collectionRepository;
    }

    public function getCollectionById(int|string $id): Collection
    {
        return Collection::findOrFail($id);
    }

    /**
     * Get all collections
     *
     * @return PaginatedList
     */
    public function getCollections(array $options): PaginatedList
    {
        return $this->collectionRepository->get($options);
    }

    /**
     * Create a new collection
     *
     * @param array $data
     * @return Collection
     */
    public function createCollection(array $data): Collection
    {
        if (!empty($data['thumbnail'])) {
            $thumbnail = Cloudinary::uploadApi()->upload($data['thumbnail'], [
                "folder" => Collection::THUMBNAIL_FOLDER,
            ]);

            $data['thumbnail'] = $thumbnail['secure_url'];
            $data['thumbnail_public_id'] = $thumbnail['public_id'];
        }

        return $this->collectionRepository->create($data);
    }

    /**
     * Update an existing collection
     *
     * @param int|string $id
     * @param array $data
     * @return Collection|null
     */
    public function updateCollection($id, array $data): ?Collection
    {
        $collection = $this->getCollectionById($id);

        if (!empty($data['thumbnail'])) {
            $thumbnail = Cloudinary::uploadApi()->upload($data['thumbnail'], [
                "folder" => Collection::THUMBNAIL_FOLDER,
            ]);

            $data['thumbnail'] = $thumbnail['secure_url'];
            $data['thumbnail_public_id'] = $thumbnail['public_id'];

            // Delete the old avatar
            if ($collection->thumbnail_public_id) {
                $response =Cloudinary::uploadApi()->destroy($collection->thumbnail_public_id);
            }
        }

        return $this->collectionRepository->update($collection, $data);
    }

    /**
     * Delete a collection
     *
     * @param int|string $id
     * @return int
     */
    public function deleteCollection($id): int
    {
        $collection = $this->getCollectionById($id);

        // Delete thumbnail from Cloudinary
        if ($collection->thumbnail_public_id) {
            Cloudinary::uploadApi()->destroy($collection->thumbnail_public_id);
        }

        return $this->collectionRepository->delete($id);
    }
}
