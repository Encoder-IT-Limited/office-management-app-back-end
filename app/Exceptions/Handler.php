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
//            if ($request->is('api/*')) {
//                if ($e instanceof ValidationException) {
//                    return response()->json([
//                        'message' => $e->getMessage(),
//                        'errors' => $e->errors()
//                    ], 422);
//                }
//                elseif ($e instanceof  \Illuminate\Auth\AuthenticationException) {
//                    return $this->unauthenticated($request, $e);
//                }
//                //
//                else if ($e instanceof NotFoundHttpException && $e->getMessage() == "") {
//                    return response()->json([
//                        'error' => 'Resource not found'
//                    ], 404);
//                }
//                //
//                else {
//                    return response()->json([
//                        'error' => $e->getMessage(),
//                    ], 500);
//                }
//            }
//            return parent::render($request, $e);

            $this->reportable(function (Throwable $e, $request) {
                if ($request->is('api/*') || $request->wantsJson()) {
                    $request->headers->set('Accept', 'application/json');
                    return $this->apiResponse($e);
                }
                return parent::render($request, $e);
            });
            // Model Not found ...
            $this->renderable(function (ModelNotFoundException $e, $request) {
                if ($request->wantsJson() || $request->is('api/*')) return $this->apiResponse($e);
                return parent::render($request, $e);
            });
            // 404 page not found ...
            $this->renderable(function (NotFoundHttpException $e, $request) {
                if ($request->wantsJson() || $request->is('api/*')) return $this->apiResponse($e);
                return parent::render($request, $e);
            });
            // Authentication Error ...
            $this->renderable(function (AuthenticationException $e, $request) {
                if ($request->wantsJson() || $request->is('api/*')) return $this->apiResponse($e);
                return parent::render($request, $e);
            });
            // Unauthorized Error ...
            $this->renderable(function (UnauthorizedException $e, $request) {
                if ($request->wantsJson() || $request->is('api/*')) return $this->apiResponse($e);
                return parent::render($request, $e);
            });
            // Validation Error ...
//        $this->renderable(function (ValidationException $e, $request) {
//            if ($request->wantsJson() || $request->is('api/*')) return $this->apiResponse($e);
//            return parent::render($request, $e);
//        });
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
            default => ($statusCode == 500) ? 'Whoops, looks like something went wrong' : $exception->getMessage(),
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
