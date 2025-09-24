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
                $response->headers->set('X-Flash-Success', $request->session()->get('success'));
            }

            if ($request->session()->has('error')) {
                $response->headers->set('X-Flash-Error', $request->session()->get('error'));
            }

            if ($request->session()->has('warning')) {
                $response->headers->set('X-Flash-Warning', $request->session()->get('warning'));
            }

            if ($request->session()->has('info')) {
                $response->headers->set('X-Flash-Info', $request->session()->get('info'));
            }

            return $response;
        } catch (ValidationException $e) {
            // Handle validation errors
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Please check your input and try again.',
                    'errors' => $e->errors(),
                ], 422);
            }

            throw $e;
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'A database error occurred. Please contact support if this persists.',
                    'error' => config('app.debug') ? $e->getMessage() : 'Database operation failed',
                ], 500);
            }

            throw $e;
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // Handle authentication errors
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You must be logged in to perform this action.',
                ], 401);
            }

            throw $e;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // Handle authorization errors
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to perform this action.',
                ], 403);
            }

            throw $e;
        } catch (\Exception $e) {
            // Handle other exceptions
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $this->getUserFriendlyErrorMessage($e),
                    'error' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred',
                ], 500);
            }

            throw $e;
        }
    }

    /**
     * Get user-friendly error messages based on exception type.
     */
    private function getUserFriendlyErrorMessage(\Exception $e): string
    {
        if ($e instanceof \Illuminate\Database\QueryException) {
            return 'A database error occurred. Please contact support if this persists.';
        }

        if ($e instanceof \Illuminate\Validation\ValidationException) {
            return 'Please check your input and try again.';
        }

        if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            return 'You must be logged in to perform this action.';
        }

        if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            return 'You do not have permission to perform this action.';
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            return 'The requested resource was not found.';
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
            return 'The request method is not allowed for this resource.';
        }

        return 'An unexpected error occurred. Please try again later.';
    }
}
