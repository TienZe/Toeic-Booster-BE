import os
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv()

# Pinecone configuration
PINECONE_API_KEY = os.getenv("PINECONE_API_KEY", "")
PINECONE_INDEX_NAME = os.getenv("PINECONE_INDEX_NAME", "")

# API settings
PROJECT_NAME = "Recommendation API"
API_PREFIX = "/api"

# Server settings
HOST = os.getenv("HOST", "0.0.0.0")
PORT = int(os.getenv("PORT", "8000")) 