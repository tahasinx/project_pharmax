<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogging
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $this->logRequest($request, $response, $startTime);

        return $response;
    }

    protected function logRequest(Request $request, Response $response, float $startTime): void
    {
        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds

        $logData = [
            'timestamp' => now()->toISOString(),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $request->user()?->id,
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'request_size' => strlen($request->getContent()),
            'response_size' => strlen($response->getContent()),
        ];

        // Only log sensitive operations
        if ($this->shouldLog($request)) {
            $this->writeLog($logData);
        }
    }

    protected function shouldLog(Request $request): bool
    {
        $sensitiveMethods = ['POST', 'PUT', 'DELETE', 'PATCH'];
        $sensitiveRoutes = [
            'medicines',
            'invoices',
            'customers',
            'stocks',
            'purchases',
            'users',
            'settings',
            'terminal'
        ];

        return in_array($request->method(), $sensitiveMethods) ||
            $request->is($sensitiveRoutes) ||
            $request->is('api/*');
    }

    protected function writeLog(array $logData): void
    {
        $logFile = storage_path('logs/audit.log');
        $logEntry = json_encode($logData) . "\n";

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
