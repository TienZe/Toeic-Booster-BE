<?php

namespace App\Http\Middleware;

use App\Entities\ErrorMessage;
use App\Http\Resources\ApiResponse;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ApiResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $statusCode = $response->getStatusCode();

        if (isset($response->exception)) {
            // Construct response for exception
            $exception = $response->exception;
            $message = $exception->getMessage();
            $errorCode = $exception->getCode();
            $exceptionClassName = array_slice(explode('\\', get_class($exception)), -1)[0] ?? null;
            $validationErrors = null;

            if ($exception instanceof ValidationException) {
                $validationErrors = $exception->errors();
            }

            return ApiResponse::respond(null, false, new ErrorMessage($message, $errorCode, $validationErrors, $exceptionClassName))
                ->response()->setStatusCode($statusCode);
        }

        // Only transform JSON responses
        if (!$response instanceof JsonResponse) {
            return $response;
        }

        $success = $statusCode >= 200 && $statusCode < 300;
        $originalData = $response->getData(true);

        return ApiResponse::respond($originalData, $success)
            ->response()->setStatusCode($statusCode);
    }
}
