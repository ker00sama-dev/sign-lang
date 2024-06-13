<?php
namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\HttpResponseException;
use Throwable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;

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
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): JsonResponse
    {
        if ($request->is('api/*') || $request->wantsJson()) {
            $statusCode = $this->getStatusCode($e);
            $response = [
                'success' => false,
                'message' => $this->getErrorMessage($e),
                'status_code' => $statusCode,
            ];

            return response()->json($response, $statusCode);
        }

        return parent::render($request, $e);
    }

    /**
     * Get the status code for the exception.
     */
    protected function getStatusCode(Throwable $e): int
    {
        if ($e instanceof MethodNotAllowedHttpException) {
            return 405;
        } elseif ($e instanceof NotFoundHttpException) {
            return 404;
        } elseif ($e instanceof ValidationException) {
            return $e->status;
        } elseif ($e instanceof HttpResponseException) {
            return $e->getResponse()->getStatusCode();
        } elseif ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
            return $e->getResponse()->getStatusCode();
        } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            return $e->getStatusCode();
        }

        return 500; // Default to 500 if no specific status code is found
    }

    /**
     * Get the error message for the exception.
     */
    protected function getErrorMessage(Throwable $e): string
    {
        if ($e instanceof MethodNotAllowedHttpException) {
            return 'Method Not Allowed';
        } elseif ($e instanceof NotFoundHttpException) {
            return 'Not Found';
        } elseif ($e instanceof ValidationException) {
            return $e->validator->errors()->first();
        } elseif ($e instanceof HttpResponseException || $e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
            return $e->getResponse()->getContent();
        }

        return $e->getMessage();
    }
}
