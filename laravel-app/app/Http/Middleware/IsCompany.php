<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsCompany
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || (!$user->is_admin && !$user->is_company)) {
            return response()->json([
                'success' => false,
                'messages' => ['企業アカウントが必要です。'],
            ], 403);
        }

        return $next($request);
    }
}