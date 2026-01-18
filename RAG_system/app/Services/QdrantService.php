<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class QdrantService
{
    private string $url;
    private string $collection;

    public function __construct()
    {
        $this->url = config('qdrant.url', 'http://localhost:6333');
        $this->collection = config('qdrant.collection', 'user_documents');
    }

    public function storeChunks(array $chunks, int $userId, string $filePath): void
    {
        $this->ensureCollectionExists();

        foreach ($chunks as $index => $chunk) {
            $embedding = app(EmbeddingService::class)->getEmbedding($chunk);

            if (!$embedding) {
                throw new \RuntimeException("Embedding failed for chunk {$index}");
            }

            $this->insert(
                id: Str::uuid()->toString(),
                vector: $embedding,
                payload: [
                    'user_id' => $userId,
                    'file_path' => $filePath,
                    'chunk_index' => $index,
                    'text' => $chunk,
                    'created_at' => now()->toISOString(),
                ]
            );
        }
    }

    private function ensureCollectionExists(): void
    {
        $res = Http::get("{$this->url}/collections/{$this->collection}");

        if ($res->status() === 404) {
            Http::put("{$this->url}/collections/{$this->collection}", [
                'vectors' => [
                    'size' => 768,
                    'distance' => 'Cosine',
                ],
            ]);
        }
    }

    private function insert(string $id, array $vector, array $payload): void
    {
        $res = Http::put(
            "{$this->url}/collections/{$this->collection}/points?wait=true",
            [
                'points' => [
                    [
                        'id' => $id,
                        'vector' => $vector,
                        'payload' => $payload,
                    ]
                ]
            ]
        );

        if (!$res->successful()) {
            throw new \RuntimeException($res->body());
        }
    }
}