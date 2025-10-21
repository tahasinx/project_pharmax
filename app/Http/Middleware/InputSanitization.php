<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InputSanitization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->sanitizeInput($request);

        return $next($request);
    }

    protected function sanitizeInput(Request $request): void
    {
        $input = $request->all();

        // Sanitize string inputs
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                $input[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                $input[$key] = $this->sanitizeArray($value);
            }
        }

        $request->merge($input);
    }

    protected function sanitizeString(string $value): string
    {
        // Remove null bytes
        $value = str_replace("\0", '', $value);

        // Trim whitespace
        $value = trim($value);

        // Remove potentially dangerous characters
        $value = preg_replace('/[<>"\']/', '', $value);

        // Limit length to prevent buffer overflow attacks
        if (strlen($value) > 10000) {
            $value = substr($value, 0, 10000);
        }

        return $value;
    }

    protected function sanitizeArray(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $array[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                $array[$key] = $this->sanitizeArray($value);
            }
        }

        return $array;
    }
}
