<?php

namespace PhpDevUmesh\LaravelApiResponse\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use PhpDevUmesh\LaravelApiResponse\ResponseBuilder;

class WrapApiResponse
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (!$response instanceof JsonResponse) {
            return $response;
        }

        if ($response->headers->has('X-Api-Response-Wrapped')) {
            return $response;
        }

        $original = $response->getData(true);
        $format = config('api-response.format');

        if (isset($original[$format['success_key']]) && isset($original[$format['status_key']])) {
            return $response;
        }

        $builder = app(ResponseBuilder::class);
        $wrapped = [
            $format['success_key'] => $response->isSuccessful(),
            $format['status_key'] => $response->getStatusCode(),
            $format['message_key'] => '',
            $format['data_key'] => $original,
        ];

        $jsonResponse = response()->json($wrapped, $response->getStatusCode());
        $jsonResponse->headers->set('X-Api-Response-Wrapped', 'true');

        return $jsonResponse;
    }
}
