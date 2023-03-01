<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
                if ($e instanceof ValidationException) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'errors' => $e->errors()
                    ], 422);
                }
                //
                elseif ($e instanceof  \Illuminate\Auth\AuthenticationException) {
                    return $this->unauthenticated($request, $e);
                }
                //
                else if ($e instanceof NotFoundHttpException && $e->getMessage() == "") {
                    return response()->json([
                        'error' => 'Resource not found'
                    ], 404);
                }
                //
                else {
                    return response()->json([
                        'error' => $e->getMessage(),
                    ], 500);
                }
            }
            return parent::render($request, $e);
        });
    }
}
