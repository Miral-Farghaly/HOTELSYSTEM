<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Database\QueryException;
use Throwable;
use Illuminate\Support\Facades\Log;

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
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });

        // Handle API exceptions
        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                return $this->handleApiException($e, $request);
            }
        });
    }

    /**
     * Handle API exceptions and return JSON responses
     */
    private function handleApiException(Throwable $e, $request): \Illuminate\Http\JsonResponse
    {
        $error = [
            'message' => 'Server Error',
            'status_code' => 500,
        ];

        // Add debug information in non-production environments
        if (!app()->environment('production')) {
            $error['debug'] = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];
        }

        if ($e instanceof AuthenticationException) {
            $error['message'] = 'Unauthenticated';
            $error['status_code'] = 401;
        } elseif ($e instanceof ValidationException) {
            $error['message'] = 'Validation Error';
            $error['errors'] = $e->errors();
            $error['status_code'] = 422;
        } elseif ($e instanceof ModelNotFoundException) {
            $error['message'] = 'Resource not found';
            $error['status_code'] = 404;
        } elseif ($e instanceof NotFoundHttpException) {
            $error['message'] = 'Endpoint not found';
            $error['status_code'] = 404;
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            $error['message'] = 'Method not allowed';
            $error['status_code'] = 405;
        } elseif ($e instanceof QueryException) {
            $error['message'] = 'Database error';
            $error['status_code'] = 500;
            // Log the actual error but don't expose it
            \Log::error('Database error: ' . $e->getMessage());
        }

        // Log server errors
        if ($error['status_code'] >= 500) {
            \Log::error('Server Error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);
        }

        return response()->json([
            'error' => $error['message'],
            'status_code' => $error['status_code'],
            'errors' => $error['errors'] ?? null,
            'debug' => $error['debug'] ?? null,
        ], $error['status_code']);
    }
}
