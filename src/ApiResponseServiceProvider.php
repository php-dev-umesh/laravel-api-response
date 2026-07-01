<?php

namespace PhpDevUmesh\LaravelApiResponse;

use Illuminate\Support\ServiceProvider;
use PhpDevUmesh\LaravelApiResponse\Http\Middleware\WrapApiResponse;

class ApiResponseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/api-response.php',
            'api-response'
        );

        $this->app->singleton(ResponseBuilder::class, function () {
            return new ResponseBuilder();
        });

        $this->app->alias(ResponseBuilder::class, 'api-response');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/api-response.php' => config_path('api-response.php'),
            ], 'api-response-config');
        }

        $this->app['router']->aliasMiddleware('api-response', WrapApiResponse::class);
    }
}
