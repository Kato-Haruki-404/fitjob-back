<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CamelCaseResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);
            $response->setData($this->convertKeysToCamelCase($data));
        }

        return $response;
    }

    /**
     * 配列のキーを再帰的にキャメルケースに変換
     */
    private function convertKeysToCamelCase(mixed $data): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        $result = [];
        foreach ($data as $key => $value) {
            $camelKey = is_string($key) ? $this->toCamelCase($key) : $key;
            $result[$camelKey] = $this->convertKeysToCamelCase($value);
        }

        return $result;
    }

    /**
     * スネークケースをキャメルケースに変換
     */
    private function toCamelCase(string $string): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }
}
