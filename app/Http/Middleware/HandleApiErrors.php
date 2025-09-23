<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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

            // Handle successful responses with flash messages
            if ($request->session()->has('success')) {
                $response->header('X-Flash-Success', $request->session()->get('success'));
            }

            if ($request->session()->has('error')) {
                $response->header('X-Flash-Error', $request->session()->get('error'));
            }

            if ($request->session()->has('warning')) {
                $response->header('X-Flash-Warning', $request->session()->get('warning'));
            }

            if ($request->session()->has('info')) {
                $response->header('X-Flash-Info', $request->session()->get('info'));
            }

            return $response;
        } catch (ValidationException $e) {
            // Handle validation errors
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (\Exception $e) {
            // Handle other exceptions
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'An error occurred',
                    'error' => config('app.debug') ? $e->getMessage() : 'Something went wrong',
                ], 500);
            }

            throw $e;
        }
    }
}
