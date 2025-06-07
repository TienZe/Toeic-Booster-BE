<?php

declare(strict_types=1);

namespace App\Services;

use App\Entities\PaginatedList;
use App\Models\Collection;
use App\Repositories\CollectionRepository;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Http;
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

    /**
     * Get recommended collections.
     *
     * @param array{
     *     filter_title?: string|null,
     *     filter_categories?: array|null,
     *     page?: int,
     *     limit?: int
     * } $options  Optional filters and pagination options:
     *   - filter_title: (string|null) Filter by collection title.
     *   - filter_categories: (array|null) Filter by category IDs.
     *   - page: (int) Page number for pagination (0-based).
     *   - limit: (int) Number of items per page.
     * @return EloquentCollection<int, Collection>
     */
    public function getRecommendedCollections($options = [])
    {
        $loggedInUserId = auth()->id();

        $options['limit'] = $options['limit'] ?? 10;
        $options['page'] = $options['page'] ?? 0;

        $collectionWeightArr = $this->getUserCollectionWeightArray($loggedInUserId);

        if (empty($collectionWeightArr)) {
            // If there are no collection weights, that means user doesn't have any rated or learned collections
            // So just browser (filter, search) based on the most taken collections
            return $this->getMostTakenCollections($options);
        }

        $preferredCollectionItems =$this->fetchPreferredCollectionIds($collectionWeightArr, $options);

        $collectionIds = array_column($preferredCollectionItems, 'id');

        $recommendedCollections = Collection::whereIn('id', $collectionIds)->get();
        $sortedCollections = $this->sortRecommendedCollectionByScore($recommendedCollections, $preferredCollectionItems);

        return $sortedCollections;
    }

    public function getMostTakenCollections($options)
    {
        $limit = $options['limit'] ?? 10;
        $page = $options['page'] ?? 0;

        $query = Collection::from('collections as c')
            ->leftJoin('lessons as l', 'c.id', '=', 'l.collection_id')
            ->leftJoin('lesson_learnings as ll', 'l.id', '=', 'll.lesson_id')
            ->groupBy('c.id')
            ->selectRaw('c.*, COUNT(DISTINCT ll.user_id) as taken_students')
            ->orderByDesc('taken_students')
            ->orderByDesc('c.id') # for consistent order for pagination
            ->limit($limit)
            ->offset($page * $limit);

        if (isset($options['filter_title'])) {
            $query->where('c.name', 'like', '%' . $options['filter_title'] . '%');
        }

        if (isset($options['filter_categories'])) {
            $query->leftJoin('collection_collection_tag as cct', 'c.id', '=', 'cct.collection_id')
                ->leftJoin('collection_tags as ct', 'cct.collection_tag_id', '=', 'ct.id')
                ->whereIn('ct.tag_name', $options['filter_categories']);
        }

        return $query->get();
    }

    public function getCollectionUserMightAlsoLike($userId, $limit = 8)
    {
        $learnedCollections = $this->getUserLearnedCollections($userId);
        $learnedCollectionIds = $learnedCollections->pluck('id')->toArray();
        $numberOfLearned = count($learnedCollectionIds);

        // 1. Get preferred collection, because the learned collections can be in the response, so just increase the limit
        // and then apply the filter and limit the response again
        $collectionWeightArr = $this->getUserCollectionWeightArray($userId);

        if (empty($collectionWeightArr)) {
            // If there are no collection weights, just return the most taken collections
            return $this->getMostTakenCollections([
                'limit' => $limit
            ]);
        }

        $preferredCollectionItems = collect($this->fetchPreferredCollectionIds($collectionWeightArr, [
            'limit' => $limit + $numberOfLearned
        ]));

        // 2. Remove the learned collections from the response
        $preferredCollectionItems = $preferredCollectionItems->filter(function($item) use ($learnedCollectionIds) {
            return !in_array($item['id'], $learnedCollectionIds);
        });

        // 3. Get the respective collections
        $collections = Collection::whereIn('id', $preferredCollectionItems->pluck('id')->toArray())->get();

        // 4. Sort by score DESC
        $sortedCollections = $this->sortRecommendedCollectionByScore($collections, $preferredCollectionItems->toArray());

        // 5. Limit the response again to ensure the response is not greater than the limit
        return $sortedCollections->take($limit);
    }

    public function getUserLearnedCollections($userId)
    {
        $learnedCollections = Collection::whereHas('lessons.lessonLearnings', function($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();

        return $learnedCollections;
    }

    /**
     * Get the weight of collections rated by user and learned by user
     *
     * @param int $userId
     * @return array{collection_id: int, weight: float}
     */
    private function getUserCollectionWeightArray($userId)
    {
        $collectionWeights = []; // map collection id to its weight (score)

        // 1. Compute weight of collections rated by user
        $ratedCollections =Collection::whereHas('ratings', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->with('ratings')
            ->get();

        foreach ($ratedCollections as $collection) {
            $rating = $collection->ratings->first()->rate;
            $score = $this->rateToScore($rating);
            $collectionWeights[$collection->id] = $score;
        }

        // 2. Compute the weight for collections learned by user (specifically, collections that have the user finish the filtering step)
        $learnedCollections = $this->getUserLearnedCollections($userId);

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

        return $collectionWeightArr;
    }

    /**
     * Fetch the preferred collection items from based on user's collection weight array
     *
     * @return array{id: int, score: float}
     */
    public function fetchPreferredCollectionIds($collectionWeights, $options)
    {
        if (empty($collectionWeights)) {
            throw new \InvalidArgumentException("Collection weights can not be empty");
        }

        $filterTitle = !empty($options['filter_title']) ? $options['filter_title'] : null;
        $filterCategories = !empty($options['filter_categories']) ? $options['filter_categories'] : null;
        $limit = $options['limit'] ?? 10;
        $page = $options['page'] ?? 0;

        $response = Http::withHeaders([
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post(getenv('COLLECTION_RECOMMENDATION_ENDPOINT') . "/recommendations", [
                'collection_weights' => $collectionWeights,
                'filter_categories' => $filterCategories,
                'filter_title' => $filterTitle,
                'limit' => $limit,
                'page' => $page
            ]);

        if (!$response->successful()) {
            // Handle error
            $statusCode = $response->status();
            $errorData = $response->json();

            throw new \Exception("Failed to fetch preferred collection ids with status code $statusCode, error data $errorData");
        }

        $data = $response->json();

        return $data['items'];
    }

    public function getSimilarCollections($collectionId, $limit = 10)
    {
        $similarCollectionResponse = $this->fetchSimilarCollections($collectionId, $limit);

        $similarCollectionIds = array_column($similarCollectionResponse, 'id');
        $similarCollections = Collection::whereIn('id', $similarCollectionIds)->get();

        // Sort by score DESC
        $sortedCollections = $this->sortRecommendedCollectionByScore($similarCollections, $similarCollectionResponse);

        return $sortedCollections;
    }

    /**
     * Sort the collections by score DESC
     *
     * @param EloquentCollection $collections
     * @param array{id: int, score: float} $recommendationItemResponse
     * @return EloquentCollection
     */
    private function sortRecommendedCollectionByScore($collections, $recommendationItemResponse)
    {
        $collectionId2Score = [];
        foreach ($recommendationItemResponse as $item) {
            $collectionId2Score[(string)$item['id'] . "_score"] = $item['score'];
        }

        $sortedCollections = $collections->sort(function ($a, $b) use ($collectionId2Score) {
            $compare = $collectionId2Score[$b->id . "_score"] - $collectionId2Score[$a->id . "_score"];

            if ($compare < 0) {
                return -1;
            } else if ($compare > 0) {
                return 1;
            }

            return 0;
        });

        return $sortedCollections;
    }

    public function fetchSimilarCollections($collectionId, $limit = 10)
    {
        $response = Http::withHeaders([
                'accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->get(getenv('COLLECTION_RECOMMENDATION_ENDPOINT') . "/recommendations/similar-collections/{$collectionId}", [
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