<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Throwable;

class Handler extends ExceptionHandler
{

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ThrottleRequestsException && $request->is('api/*')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Too many attempts.'
            ], 429);
        }

        return parent::render($request, $exception);
    }

}