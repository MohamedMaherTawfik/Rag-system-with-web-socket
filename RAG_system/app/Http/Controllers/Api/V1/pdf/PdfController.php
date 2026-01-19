<?php

namespace App\Http\Controllers\Api\V1\pdf;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Requests\PdfRequest;
use App\Models\documents;
use App\Services\PdfProcessingService;
use Illuminate\Http\JsonResponse;

class PdfController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PdfProcessingService $pdfService
    ) {
    }

    public function upload(PdfRequest $request): JsonResponse
    {
        try {
            $result = $this->pdfService->process(
                file: $request->file('file'),
                userId: auth()->id()
            );

            return $this->success($result, 'PDF uploaded and indexed successfully.');

        } catch (\Throwable $e) {
            \Log::error('PDF Upload Failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->error($e->getMessage(), 500);
        }
    }

    public function documents()
    {
        $documents = documents::where('user_id', auth()->id())->get();
        if ($documents->isEmpty()) {
            return $this->success([], 'No documents found');
        }
        return $this->success($documents);
    }

    public function download($filename)
    {
        $path = storage_path("app/private/pdfs/{$filename}");

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->download($path);
    }

}