<?php

namespace App\Services;

use App\Models\documents;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\QdrantService;

class PdfProcessingService
{
    public function __construct(
        private readonly EmbeddingService $embeddingService,
        private readonly QdrantService $qdrantService
    ) {
    }

    public function process(UploadedFile $file, int $userId): array
    {
        $filePath = $file->store('pdfs', 'public');

        DB::beginTransaction();

        try {
            $document = documents::create([
                'user_id' => $userId,
                'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'filename' => basename($filePath),
                'size' => $file->getSize(),
                'description' => null,
            ]);
            $text = $this->extractText($filePath);
            $chunks = $this->chunkText($text);
            $this->qdrantService->storeChunks(
                chunks: $chunks,
                userId: $userId,
                filePath: $filePath
            );
            DB::commit();

            return [
                'document_id' => $document->id,
                'file_path' => $filePath,
                'chunks_count' => count($chunks),
            ];

        } catch (\Throwable $e) {
            DB::rollBack();
            Storage::disk('local')->delete($filePath);
            throw $e;
        }
    }

    private function extractText(string $filePath): string
    {
        $fullPath = Storage::disk('public')->path($filePath);

        $parser = new Parser();
        $pdf = $parser->parseFile($fullPath);
        $text = $pdf->getText();

        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding(
                $text,
                'UTF-8',
                'Windows-1256, ISO-8859-6, CP1256, UTF-8'
            );
        }

        $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);
        $text = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        if ($text === '') {
            throw new \RuntimeException('The PDF contains no extractable text.');
        }

        return $text;
    }

    private function chunkText(
        string $text,
        int $chunkSize = 500,
        int $overlap = 50
    ): array {
        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $chunks = [];

        $current = [];
        $length = 0;

        foreach ($words as $word) {
            $wordLength = mb_strlen($word) + 1;

            if ($length + $wordLength > $chunkSize && $current) {
                $chunks[] = implode(' ', $current);

                $overlapWords = max(1, intdiv($overlap, 5));
                $current = array_slice($current, -$overlapWords);
                $length = array_sum(array_map(
                    fn($w) => mb_strlen($w) + 1,
                    $current
                ));
            }

            $current[] = $word;
            $length += $wordLength;
        }

        if ($current) {
            $chunks[] = implode(' ', $current);
        }

        return $chunks;
    }
}
