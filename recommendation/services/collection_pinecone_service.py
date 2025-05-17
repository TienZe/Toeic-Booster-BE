from pinecone import Pinecone
from sentence_transformers import SentenceTransformer
from typing import List, Dict, Any, Optional
import logging
import os
from config import PINECONE_API_KEY, PINECONE_INDEX_NAME

logger = logging.getLogger(__name__)

class CollectionPineconeService:
    def __init__(self):
        self.model = SentenceTransformer('all-MiniLM-L6-v2')
        try:
            self.pc = Pinecone(api_key=PINECONE_API_KEY)
            self.index = self.pc.Index(PINECONE_INDEX_NAME)
            logger.info(f"Connected to Pinecone index: {PINECONE_INDEX_NAME}")
        except Exception as e:
            logger.error(f"Failed to connect to Pinecone: {e}")
            # For development purposes, we'll allow the service to start
            # even if Pinecone connection fails
            self.pc = None
            self.index = None
    
    def simply_tokenize(self, text: str) -> List[str]:
        return text.lower().strip().split()
    
    def get_embedding(self, text: str) -> List[float]:
        """Generate embedding for input text using Sentence Transformer"""
        return self.model.encode(text).tolist()
    
    def query_similar(
        self, 
        semantic_query: Optional[str] = None, 
        vector: Optional[List[float]] = None,
        filter: Optional[Dict[str, Any]] = None, 
        limit: int = 10
    ) -> List[Dict[str, Any]]:
        """Query Pinecone for similar vectors"""
        if not self.index:
            logger.error("Pinecone index not initialized")
            return []
            
        if vector is None and semantic_query:
            # Just perform semantic search
            vector = self.get_embedding(semantic_query)
        
        if vector is None:
            logger.error("No query or vector provided")
            return []
            
        try:
            print("pinecone query with filter", filter)
            response = self.index.query(
                vector=vector,
                filter=filter,
                top_k=limit,
                # include_metadata=True
            )
            return response.get("matches", [])
        except Exception as e:
            logger.error(f"Pinecone query failed: {e}")
            return []
    
    def fetch_vectors(self, ids: List[str]) -> Dict[str, List[float]]:
        """Fetch vectors from Pinecone by ids"""
        if not self.index:
            logger.error("Pinecone index not initialized")
            return {}
        
        return { id: vector.values for id, vector in self.index.fetch(ids=ids).vectors.items() }
            
            
        
# global init
pinecone_service = CollectionPineconeService()