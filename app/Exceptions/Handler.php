<?php

namespace App\Exceptions;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
            Log::error('An error occurred: ', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
        });
    }
    public function render($request, Throwable $exception)
    {
        // Log request data
        \Log::error('Request Data: ', $request->all());

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return response()->json([
                'error' => 'Validation Error',
                'messages' => $exception->validator->errors(),
            ], 422);
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            return response()->json([
                'error' => 'HTTP Error',
                'message' => $exception->getMessage(),
                'status' => $exception->getStatusCode(),
            ], $exception->getStatusCode());
        }

        return parent::render($request, $exception);
    }
}
