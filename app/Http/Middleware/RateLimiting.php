<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RateLimiting
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key          = $this->resolveRequestSignature($request);
        $maxAttempts  = $this->getMaxAttempts($request);
        $decayMinutes = $this->getDecayMinutes($request);

        if ($this->tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'error'       => 'Too Many Requests',
                'message'     => 'Rate limit exceeded. Please try again later.',
                'retry_after' => $this->getRetryAfter($key),
            ], 429);
        }

        $this->incrementAttempts($key, $decayMinutes);

        $response = $next($request);

        $this->addRateLimitHeaders($response, $key, $maxAttempts);

        return $response;
    }

    protected function resolveRequestSignature(Request $request): string
    {
        if ($user = $request->user()) {
            return 'user:'.$user->id;
        }

        return 'ip:'.$request->ip();
    }

    protected function getMaxAttempts(Request $request): int
    {
        // Different limits for different routes
        if ($request->is('api/*')) {
            return 60; // 60 requests per minute for API
        }

        if ($request->is('pos')) {
            return 120; // 120 requests per minute for POS
        }

        return 100; // Default limit
    }

    protected function getDecayMinutes(Request $request): int
    {
        return 1; // 1 minute decay
    }

    protected function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        $attempts = cache()->get($key, 0);

        return $attempts >= $maxAttempts;
    }

    protected function incrementAttempts(string $key, int $decayMinutes): void
    {
        $attempts = cache()->get($key, 0);
        cache()->put($key, $attempts + 1, now()->addMinutes($decayMinutes));
    }

    protected function getRetryAfter(string $key): int
    {
        $attempts = cache()->get($key, 0);

        return max(0, 60 - (time() % 60)); // Seconds until next minute
    }

    protected function addRateLimitHeaders(Response $response, string $key, int $maxAttempts): void
    {
        $attempts  = cache()->get($key, 0);
        $remaining = max(0, $maxAttempts - $attempts);

        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remaining);
        $response->headers->set('X-RateLimit-Reset', time() + 60);
    }
}
