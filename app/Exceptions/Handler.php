<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

    protected $dontReport = [
        ValidationException::class,
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $this->logException($e);
        });

        // Custom handling for specific exceptions
        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Resource not found',
                    'error_code' => 'NOT_FOUND'
                ], 404);
            }
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated',
                    'error_code' => 'UNAUTHENTICATED'
                ], 401);
            }
        });
    }

    protected function logException(Throwable $exception): void
    {
        $context = [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'previous' => $exception->getPrevious() ? get_class($exception->getPrevious()) : null,
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'user_id' => auth()->id(),
        ];

        // Add request data safely
        if (request()->all()) {
            $input = request()->except(['password', 'password_confirmation', 'credit_card']);
            $context['request_data'] = json_encode($input);
        }

        // Log to different channels based on exception type
        if ($exception instanceof ValidationException) {
            Log::channel('error')->info('Validation error', $context);
        } elseif ($exception instanceof AuthenticationException) {
            Log::channel('security')->warning('Authentication failure', $context);
        } elseif ($exception instanceof \Stripe\Exception\CardException) {
            Log::channel('payment')->error('Payment processing error', $context);
        } else {
            Log::channel('error')->error('Application error', $context);
            
            // Send critical errors to Slack
            if ($this->isCriticalException($exception)) {
                Log::channel('slack')->critical('Critical error occurred', $context);
            }
        }
    }

    protected function isCriticalException(Throwable $exception): bool
    {
        return $exception instanceof \PDOException
            || $exception instanceof \ErrorException
            || $exception->getCode() >= 500;
    }
}
