<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleApiErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);

            // Log API errors
            if ($response->getStatusCode() >= 400) {
                $this->logError($request, $response);
            }

            return $response;
        } catch (\Exception $e) {
            return $this->handleException($request, $e);
        }
    }

    protected function handleException(Request $request, \Exception $e): Response
    {
        Log::error('API Error', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => $request->user()?->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while processing your request.',
                'code' => 500,
            ], 500);
        }

        return response()->view('errors.500', [
            'error' => $e->getMessage(),
        ], 500);
    }

    protected function logError(Request $request, Response $response): void
    {
        Log::warning('API Error Response', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
            'user_id' => $request->user()?->id,
            'ip' => $request->ip(),
        ]);
    }
}
