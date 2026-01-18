<?php

namespace App\Http\Middleware;

use App\Http\Controllers\concerns\authApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    use authApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apikey = config('app.api_key');
        if ($request->header('x-api-key') != $apikey) {
            return $this->unauthorized('Invalid Api Key');
        }
        return $next($request);
    }
}
