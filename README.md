RAG System with Laravel 12 + Qdrant + WebSocket (Express/Socket.IO)
A secure, real-time Retrieval-Augmented Generation (RAG) system built with Laravel 12, Qdrant vector database, and a Node.js WebSocket server for live AI responses. Designed with API-first principles, user isolation, rate limiting, and clean architecture.

Features:
-User authentication: login, register, profile, update profile, pdf upload and talk to LLM Model

Secure API with:
-Bearer token auth (Laravel Sanctum)
-Mandatory API_KEY middleware
-Rate limiting (anti-DDoS)
-Input validation & atomic DB operations
-PDF upload with validation (type, size, integrity, not empty file)

Full RAG pipeline:
-PDF parsing → text chunking → embedding via GTE-base (via OpenRouter)
-Chunks stored in Qdrant with user_id payload

Real-time chat:
-Retrieve user-specific chunks from Qdrant
-Query Mistral Devstral (free) via OpenRouter
-Stream response via WebSocket (Express + Socket.IO)

Clean code structure:
-API versioning (/api/v1/...)
-Service classes (PDFService, ChatService)
-Unified JSON responses (success/error)

Frontend:
-Blade templates with @extends / @section
-Vite-powered dev server
-RTL/LTR support ready (for Arabic/English)

System Architecture:
Frontend (Blade + Vite)
↓ (HTTP API calls)
Laravel 12 (API Layer)
│
├── Auth: Sanctum + API_KEY middleware
├── PDF Upload → Parse → Chunk → Embed → Store in Qdrant (with user_id)
├── Chat Query → Fetch user chunks from Qdrant → Call LLM → Return via WebSocket
│
↓ (WebSocket events)
Node.js Server (Express + Socket.IO)
↓ (HTTP to OpenRouter)
OpenRouter API (GTE-base for embeddings, Mistral Devstral for answers)
↑
Qdrant (Vector DB in Docker)

All data is scoped to the authenticated user (user_id in every chunk payload).

End-to-End Data Flow:
-User registers/logs in → gets Sanctum token + uses API_KEY in headers.
Uploads PDF:
-Laravel validates file (PDF only, ≤5MB, Not Empty files.)
-Extracts text → splits into chunks
-Sends chunks to OpenRouter (gte-base) for embeddings
-Stores vectors in Qdrant with payload: { user_id, doc_id, chunk_text }

Asks a question:
-Laravel fetches only current user’s chunks from Qdrant (via user_id filter)
-Builds prompt → sends to OpenRouter (mistralai/devstral-2512:free)
-Real-time response:
-Laravel triggers WebSocket event
-Node.js server streams LLM response back to frontend
-User sees answer in real-time text field

Local Setup & Run Instructions:
Prerequisites

PHP 8.2+ -> create new .env file and use:

API_KEY=your_laravel_api_key_here
OPENAI_API_KEY=your_openai_api_key_here
OPENROUTER_API_KEY=your_openrouter_api_key_here

to prevent any error open the folder alone in vscode
php artisan migrate --seed
php artisan serve --port=8080
npm install
npm run dev

QDRANT_URL=http://localhost:6333
QDRANT_COLLECTION=user_documents

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,127.0.0.1:8080,

Composer install

cd websocket-server/
npm install
node index.js
.env file for node :
API_KEY=your_laravel_api_key_here // must match laravel api key
PORT=3001
Node.js v18+

docker run -p 6333:6333 qdrant/qdrant
http://localhost:6333/dashboard#/collections for qdrant ui dashboard

Security & Best Practices"
-API_KEY Middleware: Defined in bootstrap/app.php, applied globally.
-Rate Limiting: All endpoints protected (e.g., 60 requests/minute per IP).
-User Isolation: Every Qdrant chunk includes user_id; queries filtered by it.
-Atomic Operations: DB transactions used in critical paths (e.g., PDF upload + metadata).

Notes:
-The system is designed for synchronous LLM calls — waits for full response before sending via WebSocket.
-Frontend shows "Loading..." during processing.
-Ready for Arabic (RTL) support — just toggle dir="rtl" in layout.

Dependencies and Libraries Used:
Laravel Backend (PHP 8.2+)
Framework:
-Laravel 12

Authentication:
-Laravel Sanctum

PDF Parsing:
-spatie/pdf-to-text or custom parser using pdftotext

HTTP Client:
-Guzzle (via Laravel’s built-in HTTP client)

Validation & Security:
-Built-in Laravel validation
-Rate limiting (Illuminate\Cache\RateLimiter)

API Structure:
-Custom middleware for X-API-KEY validation
-API versioning via route groups (/api/v1/...)
-Database: SQLite (configurable via .env)
-All PHP dependencies are managed via Composer — see composer.json.

WebSocket Server (Node.js)
Runtime: Node.js v18+
Framework: Express
Real-time: Socket.IO
HTTP Client: Axios
Environment: dotenv for .env loading
Validation: Manual header validation (X-API-KEY, Authorization)
Managed via npm — see websocket-server/package.json.

Vector Database
Qdrant:
-qdrant/qdrant (v1.9+)
-Run via Docker (docker run -p 6333:6333 qdrant/qdrant)
-Used for storing embeddings with metadata (user_id, doc_id, text)

AI & Embeddings (via OpenRouter)
Embedding Model:
-thenlper/gte-base

→ Used to generate vector embeddings from text chunks
LLM for Answers: mistralai/devstral-2512:free
→ Used for generating RAG responses

Provider:
OpenRouter (unified API for multiple models)
API calls made via HTTP with Authorization: Bearer <OPENROUTER_API_KEY>

Frontend (Blade + Vite)
Templating: Laravel Blade (@extends, @section)
Build Tool: Vite (for asset bundling & HMR)
Styling: Plain CSS or Tailwind (optional — configurable)
AJAX: Native JavaScript (no frameworks like React/Vue)

A Working Example:

End-to-End User Flow

1. Register a New User
   POST http://localhost:8080/api/v1/register
   Content-Type: application/json
   X-API-KEY: your_secret_api_key_here

{
"name": "Ahmed",
"email": "ahmed@example.com",
"password": "secure123",
"password_confirmation": "secure123"
}

{
"status": "success",
"message": "Registered successfully.",
"data": {
"token": "56|hTdhxs2CAvwT3M42NBv4QJcmTdiidonH5wUX3bnL3509736e",
"user": {
"id": 7,
"name": "Mohamed Maher",
"email": "m7mdellham@gmail.com",
"role": "user",
"is_active": true,
"memory_enabled": false,
"last_login_at": "2026-01-18T20:37:42.462624Z"
}
}
}

2. Login (if not already logged in):
   POST http://localhost:8080/api/v1/login
   X-API-KEY: your_secret_api_key_here

{
"email": "admin@admin.com",
"password": "admin"
}

{
"status": "success",
"message": "Logged in successfully.",
"data": {
"user": {
"id": 1,
"name": "admin",
"email": "admin@admin.com",
"role": "admin",
"is_active": true,
"memory_enabled": false,
"last_login_at": "2026-01-18T20:38:01.969523Z"
},
"token": "57|QCKbJsGXsSRATKq97BJ7cK8LNNqNCZYkD9clKkNvd72519da"
}
}

3. Upload a PDF File & Ask a Question via Chat in same form

   POST http://localhost:8080/api/v1/pdf/upload
   Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
   X-API-KEY: your_secret_api_key_here
   Content-Type: multipart/form-data

   POST http://localhost:8080/api/v1/chat/query
   Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
   X-API-KEY: your_secret_api_key_here

{
"query": "What is the main conclusion of the document?"
}

first
you see uploading pdf file...

then
Receive Real-Time Answer via WebSocket:
processing...
