<?php

use App\Domain\User\Exception\DomainException;
use App\Domain\User\Exception\InvalidUserId;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Обработка доменных исключений -> 409 Conflict
        $exceptions->render(function (DomainException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'code' => class_basename($e),
                        'message' => $e->getMessage(),
                    ],
                ], 409);
            }
        });

        // Обработка невалидного UUID в роутах -> 422 Unprocessable Entity
        $exceptions->render(function (InvalidUserId $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'code' => 'invalid_uuid',
                        'message' => $e->getMessage(),
                    ],
                ], 422);
            }
        });

        // Обработка ненайденных моделей -> 404 Not Found
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'error' => [
                        'code' => 'not_found',
                        'message' => 'Resource not found.',
                    ],
                ], 404);
            }
        });
    })->create();
