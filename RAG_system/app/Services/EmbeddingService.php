<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EmbeddingService
{
    public function getEmbedding(string $text): ?array
    {
        try {
            $apiKey = env('OPENROUTER_API_KEY');
            if (!$apiKey) {
                \Log::error('OPENROUTER_API_KEY is missing in .env');
                return null;
            }

            $text = trim($text);
            if ($text === '')
                return null;

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => 'http://localhost',
                    'X-Title' => 'RAG System',
                ])
                ->post('https://openrouter.ai/api/v1/embeddings', [
                    'model' => 'thenlper/gte-base',
                    'input' => $text,
                ]);

            if (!$response->successful()) {
                \Log::error('OpenRouter Embedding Error: ' . $response->status() . ' - ' . $response->body());
                return null;
            }

            $data = $response->json();
            return $data['data'][0]['embedding'] ?? null;
        } catch (\Exception $e) {
            \Log::error('OpenRouter Embedding Exception: ' . $e->getMessage());
            return null;
        }
    }
}
