from pydantic import BaseModel
from typing import List, Optional, Dict, Any

# Request schema
class RecommendationRequest(BaseModel):
    user_id: Optional[int] = None
    query: Optional[str] = None
    limit: int = 10
    filter_categories: Optional[List[str]] = None
    filter_title: Optional[str] = None
    class Config:
        schema_extra = {
            "example": {
                "query": "python programming",
                "filter_categories": ["Grade 5", "Grade 6"],
                "filter_title": "collection title",
                "user_id": 1,
                "limit": 5
            }
        }

# Response schemas
class RecommendationItem(BaseModel):
    id: int
    score: float
    title: str
    description: Optional[str] = None
    category: Optional[str] = None
    metadata: Optional[Dict[str, Any]] = None

class RecommendationResponse(BaseModel):
    items: List[RecommendationItem]
    count: int 