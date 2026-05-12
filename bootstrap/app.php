<?php

use App\Domain\Order\Exceptions\InsufficientStockException;
use App\Domain\Order\Exceptions\InvalidOrderStatusException;
use App\Domain\Order\Exceptions\OrderNotFoundException;
use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\User\Exceptions\UserAlreadyExistsException;
use App\Interfaces\Http\Middleware\EnsureAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (OrderNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        });

        $exceptions->render(function (InvalidOrderStatusException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        });

        $exceptions->render(function (InsufficientStockException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        });

        $exceptions->render(function (ProductNotFoundException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        });

        $exceptions->render(function (UserAlreadyExistsException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => [
                    'email' => [$exception->getMessage()],
                ],
            ], 422);
        });
    })->create();
