<?php
return [
    'url' => env('QDRANT_URL', 'http://localhost:6333'),
    'collection' => env('QDRANT_COLLECTION', 'user_documents'),
];
