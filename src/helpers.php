<?php

use PhpDevUmesh\LaravelApiResponse\ResponseBuilder;

if (!function_exists('api_trans')) {
    function api_trans(string $key, array $replace = []): string
    {
        return app(ResponseBuilder::class)->trans($key, $replace);
    }
}
