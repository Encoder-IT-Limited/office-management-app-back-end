<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        // $this->reportable(function (Throwable $e) {
        //     //
        // });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
//                if ($e instanceof ValidationException) {
//                    return response()->json([
//                        'message' => $e->getMessage(),
//                        'errors' => $e->errors()
//                    ], 422);
//                }
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return $this->unauthenticated($request, $e);
                }
                if ($e instanceof NotFoundHttpException && $e->getMessage() == "") {
                    return $this->apiResponse($e);
                }
                if ($e instanceof UnauthorizedException) {
                    return response()->json([
                        'message' => $e->getMessage()
                    ], 403);
                }
                if ($e instanceof ModelNotFoundException) {
                    return response()->json([
                        'message' => 'Resource not found'
                    ], 404);
                }
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'errors' => $e->errors()
                    ], 422);
                }
                if ($e instanceof \Exception) {
                    return $this->apiResponse($e);
                }
                if ($e instanceof \Throwable) {
                    return $this->apiResponse($e);
                }
                return $this->apiResponse($e);
            }
            return parent::render($request, $e);
        });
    }

    private function apiResponse($exception): \Illuminate\Http\JsonResponse
    {
        $statusCode = 500;
        if (method_exists($exception, 'getStatusCode')) $statusCode = $exception->getStatusCode();

        $response = ['success' => false,];

        $response['message'] = match ($statusCode) {
            401 => 'Unauthenticated!',
            403 => 'Unauthorized, Forbidden!',
            404 => 'Resource Not Found',
            405 => 'Method Not Allowed',
            422 => $exception->original['message'],
            default => ($statusCode == 500) ? (config('app.debug') ? $exception->getMessage() : 'Oops, looks like something went wrong') : $exception->getMessage(),
        };
        if ($statusCode == 422) $response['errors'] = $exception->original['errors'];

        if (config('app.debug')) {
            $response['trace'] = $exception->getTrace();
            $response['code'] = $exception->getCode();
        }

        $response['status'] = $statusCode;

        return response()->json($response, $statusCode);
    }
}
