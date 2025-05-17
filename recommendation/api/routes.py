from fastapi import APIRouter, HTTPException, Depends
from typing import List
from models.schemas import RecommendationRequest, RecommendationResponse, RecommendationItem
from services.collection_pinecone_service import pinecone_service

router = APIRouter()

@router.post("/recommendations", response_model=RecommendationResponse)
async def get_recommendations(
    request: RecommendationRequest,
):
    """
    Get personalized recommendations based on user, item, or query.
    At least one of user_id, item_id, or query must be provided.
    """
    if not any([request.user_id, request.query]):
        raise HTTPException(
            status_code=400, 
            detail="At least one of user_id, item_id, or query must be provided"
        )
    # Construct filter if category is specified
    filter_dict = {}
    if request.filter_categories:
        filter_dict["category_tokens"] = {"$in": request.filter_categories}
    
    if request.filter_title:
        filter_title_tokens = pinecone_service.simply_tokenize(request.filter_title)
        filter_dict["title_tokens"] = {"$in": filter_title_tokens}
    
    # Get recommendations based on query
    if request.query:
        results = pinecone_service.query_similar(
            semantic_query=request.query,
            filter=filter_dict if filter_dict else None,
            limit=request.limit
        )
    elif request.user_id:
        # Here you would implement user-based recommendations
        # This is a simplified placeholder
        results = []
    
    # Transform results into response items
    items = []
    for match in results:
        metadata = match.get("metadata", {})
        items.append(
            RecommendationItem(
                id=metadata.get("id", 0),
                score=match.get("score", 0),
                title=metadata.get("title", ""),
                description=metadata.get("description", ""),
                category=metadata.get("category", ""),
                metadata=metadata
            )
        )
    
    return RecommendationResponse(items=items, count=len(items)) 