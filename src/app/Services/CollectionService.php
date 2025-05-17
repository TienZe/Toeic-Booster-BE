<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\PaginatedList;
use App\Models\Collection;
use App\Repositories\CollectionRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Http;

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

        $updatedCollection = $this->collectionRepository->update($collection, $data);

        // Refresh the collection and all its relationships
        $updatedCollection->refresh();

        return $updatedCollection;
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

    public function rateToScore(float $rate)
    {
        $score = $rate - 2;

        if ($score < 0) {
            $score = 0;
        }

        return $score;
    }

    public function getRecommendedCollections($options = [])
    {
        $loggedInUserId = auth()->id();

        $collectionWeights = [];

        // 1. Compute weight of collections rated by user
        $ratedCollections =Collection::whereHas('ratings', function($query) use ($loggedInUserId) {
                $query->where('user_id', $loggedInUserId);
            })->with('ratings')
            ->get();

        foreach ($ratedCollections as $collection) {
            $rating = $collection->ratings->first()->rate;
            $score = $this->rateToScore($rating);
            $collectionWeights[$collection->id] = $score;
        }

        // 2. Compute the weight for collections learned by user (specifically, collections that have the user finish the filtering step)
        $learnedCollections = Collection::whereHas('lessons.lessonLearnings', function($query) use ($loggedInUserId) {
            $query->where('user_id', $loggedInUserId);
        })->get();

        foreach ($learnedCollections as $collection) {
            if (!isset($collectionWeights[$collection->id])) {
                $collectionWeights[$collection->id] = 1; // mark as learned with no over weighted
            }
        }

        $collectionWeightArr = [];
        foreach ($collectionWeights as $collectionId => $weight) {
            $collectionWeightArr[] = [
                'collection_id' => $collectionId,
                'weight' => $weight
            ];
        }

        $preferredCollections =$this->fetchPreferredCollectionIds($collectionWeightArr, $options);

        $collectionIds = array_column($preferredCollections, 'id');

        $recommendedCollections = Collection::whereIn('id', $collectionIds)->get();

        return $recommendedCollections;
    }

    public function fetchPreferredCollectionIds($collectionWeights, $options)
    {
        $filterTitle = $options['filter_title'] ?? null;
        $filterCategories = $options['filter_categories'] ?? null;
        $limit = $options['limit'] ?? 10;

        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
        ->post(getenv('COLLECTION_RECOMMENDATION_ENDPOINT'), [
            'collection_weights' => $collectionWeights,
            'filter_categories' => $filterCategories,
            'filter_title' => $filterTitle,
            'limit' => $limit
        ]);

        if (!$response->successful()) {
            // Handle error
            $statusCode = $response->status();
            $errorData = $response->json();

            throw new \Exception("Failed to fetch preferred collection ids: $statusCode, $errorData");
        }

        $data = $response->json();

        return $data['items'];
    }
}