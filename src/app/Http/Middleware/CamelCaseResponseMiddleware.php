<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CamelCaseResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $data = $response->getData(true);

            $converted = $this->convertKeysToCamelCase($data);
            $response->setData($converted);
        }

        return $response;
    }

    /**
     * Convert array keys from snake_case to camelCase recursively.
     *
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    private function convertKeysToCamelCase(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = strlen($key) > 1 ? $this->snakeToCamel($key) : $key;

            if (is_array($value)) {
                $result[$newKey] = $this->convertKeysToCamelCase($value);
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Convert a string from snake_case to camelCase.
     */
    private function snakeToCamel(string $input): string
    {
        return lcfirst(str_replace('_', '', ucwords($input, '_')));
    }
}
