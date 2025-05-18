from fastapi import APIRouter, HTTPException
from typing import List
from models.schemas import RecommendationRequest, RecommendationResponse, RecommendationItem
from services.collection_pinecone_service import pinecone_service
import numpy as np

router = APIRouter()

@router.post("/recommendations", response_model=RecommendationResponse)
async def get_recommendations(
    request: RecommendationRequest,
):
    """
    Get personalized recommendations based on collection_weights (user preferences), or query.
    At least one of collection_weights, or query must be provided.
    """
    if not any([request.collection_weights, request.query]):
        raise HTTPException(
            status_code=400, 
            detail="At least one of collection_weights, or query must be provided"
        )
    # Construct filter if category is specified
    filter_dict = {}
    if request.filter_categories:
        filter_dict["category_tokens"] = {"$in": request.filter_categories}
    
    if request.filter_title:
        filter_title_tokens = pinecone_service.simply_tokenize(request.filter_title)
        filter_dict["title_tokens"] = {"$in": filter_title_tokens}
    
    if request.query:
        # Perform semantic search
        results = pinecone_service.query_similar(
            semantic_query=request.query,
            filter=filter_dict if filter_dict else None,
            limit=request.limit
        )
    elif request.collection_weights:
        # Perform weighted vector search (based on user preferences)
        collection_id_2_weight = {str(int(item["collection_id"])): item["weight"] for item in request.collection_weights}
        collection_ids = list(collection_id_2_weight.keys())
        vectors_dict = pinecone_service.fetch_vectors(collection_ids)
        
        # print("collection_ids", collection_ids)
        # print("vectors_dict", vectors_dict)
        
        # Compute average weighted vector
        weighted_vector = np.zeros(len(vectors_dict[collection_ids[0]]))
        
        total_weight = sum(collection_id_2_weight.values())
        for collection_id, vector in vectors_dict.items():
            collection_weight = collection_id_2_weight[collection_id]
            weighted_vector += np.dot(vector, collection_weight)
        
        weighted_vector /= total_weight
        
        # print("weighted_vector", weighted_vector)
        # print("shape", weighted_vector.shape)
        
        # Query similar
        results = pinecone_service.query_similar(
            vector=list(weighted_vector),
            limit=request.limit,
            filter=filter_dict if filter_dict else None
        )
    
    # Transform results into response items
    items = []
    for match in results:
        # metadata = match.get("metadata", {})
        items.append(
            RecommendationItem(
                id=match.get("id", 0),
                score=match.get("score", 0),
                # title=metadata.get("title", ""),
                # description=metadata.get("description", ""),
                # category=metadata.get("category", ""),
                # metadata=metadata
            )
        )
    
    return RecommendationResponse(items=items, count=len(items))

@router.get("/recommendations/similar-collections/{collection_id}", response_model=RecommendationResponse)
async def get_similar_collections(
    collection_id: str,
    limit: int = 10,
):
    """
    Get similar collections based on a given collection_id.
    """
    vector_dict = pinecone_service.fetch_vectors([collection_id])
    
    if vector_dict is None or len(vector_dict) == 0:
        raise HTTPException(
            status_code=404,
            detail="Collection not found"
        )
    
    vector = vector_dict[collection_id]
    
    results = pinecone_service.query_similar(
        vector=vector,
        limit=limit + 1,
    )
    
    if len(results) < 1:
        return RecommendationResponse(items=[], count=0)
    
    # Remove the first item (the same collection)
    results = results[1:]
    
    # Transform results into response items
    items = []
    for match in results:
        items.append(
            RecommendationItem(
                id=match.get("id", 0),
                score=match.get("score", 0),
            )
        )
    
    return RecommendationResponse(items=items, count=len(items))