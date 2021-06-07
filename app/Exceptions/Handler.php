<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($request->wantsJson()) {
            if ($exception instanceof AuthenticationException || $exception instanceof AuthorizationException) {
                return response()->error(401, $exception->getMessage());
            }
            if ($exception instanceof MethodNotAllowedHttpException) {
                return response()->error(405, $exception->getMessage());
            }
            if ($exception instanceof ModelNotFoundException) {
                return response()->error(404, $exception->getMessage());
            }
            if ($exception instanceof NotFoundHttpException) {
                return response()->error(404, $exception->getMessage());
            }
            if ($exception instanceof ValidationException) {
                return response()->error(422, $exception->getMessage(), $exception->errors());
            }
            if ($exception instanceof HttpResponseException || $exception instanceof HttpException) {
                return response()->error($exception->getStatusCode() ?: 500, $exception->getMessage());
            }
            return response()->error(500, $exception->getMessage());
        }

        return parent::render($request, $exception);
    }
}
