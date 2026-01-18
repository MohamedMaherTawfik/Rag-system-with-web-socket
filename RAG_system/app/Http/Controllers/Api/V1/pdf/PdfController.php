<?php

namespace App\Http\Controllers\Api\V1\pdf;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Requests\PdfRequest;
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
}