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
                'error' => $e->getMessage(),
            ])->toResponse($request)->setStatusCode($status);
        }

        return $response;
    }
}
