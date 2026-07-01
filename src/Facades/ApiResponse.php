<?php

namespace PhpDevUmesh\LaravelApiResponse\Facades;

use Illuminate\Support\Facades\Facade;
use PhpDevUmesh\LaravelApiResponse\ResponseBuilder;

class ApiResponse extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ResponseBuilder::class;
    }
}
