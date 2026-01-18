<?php

namespace App\Http\Controllers\Api\V1\chat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class ChatController extends Controller
{
    /**
     * Handle user question and return an answer using RAG approach.
     */


    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'required|string',
        ]);

        $user = $request->user();
        $query = $request->question;
        $chunks = $this->getTopChunksFromQdrant($query, $user->id);
        \Log::info('Chunks: ' . json_encode($chunks, JSON_PRETTY_PRINT));

        if (empty($chunks)) {
            return response()->json([
                'answer' => 'No relevant context found in your documents.'
            ], 200);
        }
        $contextParts = collect($chunks)->map(function ($chunk, $index) {
            return "[Source: {$chunk['file_path']} | Chunk #{$chunk['chunk_index']} | Score: " . number_format($chunk['score'], 4) . "]\n{$chunk['text']}";
        })->all();

        $contextText = implode("\n\n==========\n\n", $contextParts);

        $prompt = "Use the following context to answer the user's question accurately and concisely. If the context doesn't contain enough information, say so.\n\nContext:\n{$contextText}\n\nQuestion:\n{$query}";
        \Log::info("Prompt sent to OpenRouter:\n" . $prompt);

        $answer = $this->callOpenRouter($prompt);

        return response()->json([
            'answer' => $answer ?? 'The LLM could not generate an answer.'
        ], 200);
    }
    /**
     * Get top chunks from Qdrant for the user's query
     */
    public function getTopChunksFromQdrant(string $query, int $userId): array
    {
        $embeddingService = app(\App\Services\EmbeddingService::class);
        $vector = $embeddingService->getEmbedding($query);

        if (!$vector) {
            \Log::warning('Failed to generate query embedding');
            return [];
        }

        $response = Http::post('http://localhost:6333/collections/user_documents/points/search', [
            'vector' => $vector,
            'limit' => 5,
            'with_payload' => true,
            'filter' => [
                'must' => [
                    [
                        'key' => 'user_id',
                        'match' => ['value' => $userId],
                    ],
                ],
            ],
        ]);

        if (!$response->successful()) {
            \Log::error('Qdrant search failed: ' . $response->body());
            return [];
        }

        $result = $response->json();
        $chunks = collect($result['result'] ?? [])
            ->map(function ($item) {
                return [
                    'text' => $item['payload']['text'] ?? '',
                    'file_path' => $item['payload']['file_path'] ?? '',
                    'chunk_index' => $item['payload']['chunk_index'] ?? 0,
                    'score' => $item['score'] ?? 0,
                ];
            })
            ->filter(fn($chunk) => !empty($chunk['text']))
            ->all();

        \Log::info('Retrieved detailed chunks from Qdrant:', $chunks);

        return $chunks;
    }
    /**
     * Call OpenRouter LLM
     */
    private function callOpenRouter(string $prompt): ?string
    {
        try {
            $client = new Client();
            $response = $client->post('https://openrouter.ai/api/v1/chat/completions', [
                RequestOptions::HEADERS => [
                    'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                    'HTTP-Referer' => 'http://localhost',
                    'X-Title' => 'RAG System',
                    'Content-Type' => 'application/json',
                ],
                RequestOptions::JSON => [
                    'model' => 'mistralai/devstral-2512:free',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                ],
                RequestOptions::TIMEOUT => 45,
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['choices'][0]['message']['content'] ?? null;

        } catch (\Exception $e) {
            \Log::error('OpenRouter Chat Error: ' . $e->getMessage());
            return null;
        }
    }
}
