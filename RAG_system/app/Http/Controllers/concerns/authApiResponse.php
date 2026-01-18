<?php

namespace App\Http\Controllers\concerns;
use Illuminate\Http\JsonResponse;
trait authApiResponse
{
    public function success($data = [], $message = 'Authenticated Successfully'): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], 200);
    }

    public function unauthorized($message = 'Unauthorized Request'): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], 401);
    }

    public function notFound($message = 'User not found'): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], 404);
    }

    public function tooManyRequests(string $message = 'Too many requests. Please try again later.'): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], 429);
    }

    public function error(string $message = 'An error occurred', int $statusCode = 500, $errors = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    public function validationError($errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    public function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], 403);
    }
}