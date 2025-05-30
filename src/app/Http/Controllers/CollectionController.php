<?php

namespace App\Http\Controllers;

use App\Http\Requests\Collection\GetListOfCollectionRequest;
use App\Http\Requests\Collection\GetRecommendedCollectionRequest;
use App\Http\Requests\Collection\StoreCollectionRequest;
use App\Http\Requests\Collection\UpdateCollectionRequest;
use App\Services\CollectionService;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    private CollectionService $collectionService;

    public function __construct(CollectionService $collectionService)
    {
        $this->collectionService = $collectionService;
    }

    /**
     * Display a listing of the collections.
     */
    public function index(GetListOfCollectionRequest $request)
    {
        $collections = $this->collectionService->getCollections($request->validated());

        return $collections;
    }

    /**
     * Store a newly created collection in storage.
     */
    public function store(StoreCollectionRequest $request)
    {
        $collection = $this->collectionService->createCollection($request->validated());

        return $collection;
    }

    /**
     * Display the specified collection.
     */
    public function show(string $id)
    {
        $collection = $this->collectionService->getCollectionById($id);

        return $collection;
    }

    /**
     * Update the specified collection in storage.
     */
    public function update(UpdateCollectionRequest $request, string $id)
    {
        $collection = $this->collectionService->updateCollection($id, $request->validated());

        return $collection;
    }

    /**
     * Remove the specified collection from storage.
     */
    public function destroy(string $id)
    {
        $deleted = $this->collectionService->deleteCollection($id);

        return [ "deleted" => $deleted ];
    }


    public function recommendCollections(GetRecommendedCollectionRequest $request)
    {
        $collections = $this->collectionService->getRecommendedCollections($request->validated());

        return $collections;
    }

    public function getSimilarCollections($id)
    {
        return $this->collectionService->getSimilarCollections($id);
    }

    public function getCollectionUserMightAlsoLike(Request $request)
    {
        $limit = $request->limit ?? 8;
        $userId = auth()->id();

        return $this->collectionService->getCollectionUserMightAlsoLike($userId, $limit);
    }
}
