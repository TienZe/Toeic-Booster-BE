<?php

namespace App\Http\Controllers;

use App\Http\Requests\Collection\StoreCollectionRatingRequest;
use App\Services\CollectionRatingService;
use Illuminate\Http\JsonResponse;

class CollectionRatingController extends Controller
{
    private CollectionRatingService $collectionRatingService;

    public function __construct(CollectionRatingService $collectionRatingService)
    {
        $this->collectionRatingService = $collectionRatingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(int $collectionId)
    {
        return $this->collectionRatingService->getCollectionRating($collectionId);
    }

    /**
     * Store a newly created collection rating in storage.
     */
    public function store(StoreCollectionRatingRequest $request)
    {
        $userId = auth()->id();
        $data = $request->validated();

        $rating = $this->collectionRatingService->createCollectionRating($data, $userId);

        return $rating;
    }
}
