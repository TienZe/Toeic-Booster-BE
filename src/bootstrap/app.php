<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middlewares
        $middleware->append(\App\Http\Middleware\CorsMiddleware::class);
        $middleware->append(\App\Http\Middleware\CamelCaseToSnakeCaseMiddleware::class);
        $middleware->append(\App\Http\Middleware\ApiResponseMiddleware::class);
        $middleware->append(\App\Http\Middleware\CamelCaseResponseMiddleware::class);

        // Define aliases for middlewares
        $middleware->alias([
            'jwt.auth' => \App\Http\Middleware\JwtMiddleware::class,
            'admin' => \App\Http\Middleware\RequireAdminPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
