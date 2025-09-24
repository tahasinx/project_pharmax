<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        if ($request->expectsJson() || $request->is('api/*')) {
            return $response;
        }

        if (app()->environment(['local', 'testing'])) {
            return $response;
        }

        $status = $response->getStatusCode();

        if (in_array($status, [500, 503, 404, 403, 419, 429])) {
            return Inertia::render('Errors/Error', [
                'status' => $status,
                'error' => $this->getUserFriendlyErrorMessage($e, $status),
            ])->toResponse($request)->setStatusCode($status);
        }

        return $response;
    }

    /**
     * Get user-friendly error messages based on exception type and status code.
     */
    private function getUserFriendlyErrorMessage(Throwable $e, int $status): string
    {
        switch ($status) {
            case 404:
                return 'The page you are looking for could not be found.';
            case 403:
                return 'You do not have permission to access this resource.';
            case 419:
                return 'Your session has expired. Please refresh the page and try again.';
            case 429:
                return 'Too many requests. Please wait a moment before trying again.';
            case 500:
                if ($e instanceof \Illuminate\Database\QueryException) {
                    return 'A database error occurred. Please contact support if this persists.';
                }
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return 'Please check your input and try again.';
                }
                return 'An unexpected error occurred. Please try again later.';
            case 503:
                return 'The service is temporarily unavailable. Please try again later.';
            default:
                return 'An unexpected error occurred. Please try again later.';
        }
    }
}
