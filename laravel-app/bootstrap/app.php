<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\IsCompany;
use App\Http\Middleware\CamelCaseResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'auth' => Authenticate::class,
            'is_admin' => IsAdmin::class,
            'is_company' => IsCompany::class,
        ]);
        // APIルート全体にCamelCaseResponseを適用
        $middleware->api(append: [
            CamelCaseResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API認証エラーのハンドリング
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'messages' => ['認証が必要です。'],
                ], 401);
            }
        });
    })->create();