<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * APIリクエストの場合はリダイレクトしない（401例外を投げる）
     */
    protected function redirectTo(Request $request): ?string
    {
        // APIリクエストの場合はnullを返す（リダイレクトしない）
        if ($request->is('api/*') || $request->expectsJson()) {
            return null;
        }
    }
}
