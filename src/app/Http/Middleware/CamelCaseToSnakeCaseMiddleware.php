<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CamelCaseToSnakeCaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        $converted = $this->convertKeysToSnakeCase($input);
        $request->replace($converted);

        return $next($request);
    }

    /**
     * Convert array keys from camelCase to snake_case recursively.
     *
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    private function convertKeysToSnakeCase(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $this->camelToSnake($key);

            if (is_array($value)) {
                $result[$newKey] = $this->convertKeysToSnakeCase($value);
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Convert a string from camelCase to snake_case.
     */
    private function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
}
