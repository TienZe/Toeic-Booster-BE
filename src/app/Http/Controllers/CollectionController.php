<?php

namespace App\Http\Controllers;

use App\Http\Requests\Collection\GetListOfCollectionRequest;
use App\Http\Requests\Collection\StoreCollectionRequest;
use App\Http\Requests\Collection\UpdateCollectionRequest;
use App\Services\CollectionService;

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


    public function recommendCollections()
    {
        $collections = $this->collectionService->getRecommendedCollections();

        return $collections;
    }
}
