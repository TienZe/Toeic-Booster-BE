<?php

namespace App\Services;

use App\Models\Collection;
use App\Models\CollectionRating;
use App\Models\User;

class CollectionRatingService
{
    /**
     * Create a new collection rating
     *
     * @param array $data
     * @param int $userId
     * @return array
     */
    public function createCollectionRating(array $data, int $userId): array
    {
        $collection = Collection::findOrFail($data['collection_id']);

        $rating = $collection->ratings()->create([
            'user_id' => $userId,
            'rate' => $data['rate'],
            'personal_message' => $data['personal_message'] ?? null,
        ]);

        return [
            'id' => $rating->id,
            'collection_id' => $rating->collection_id,
            'user_id' => $rating->user_id,
            'rate' => $rating->rate,
            'personal_message' => $rating->personal_message,
            'created_at' => $rating->created_at,
        ];
    }

    public function getCollectionRating(int $collectionId)
    {
        return CollectionRating::with(['user' => function($query) {
            $query->select('id', 'name', 'avatar');
        }])->where('collection_id', $collectionId)->get();
    }
}
