<?php

namespace PhpDevUmesh\LaravelApiResponse\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use PhpDevUmesh\LaravelApiResponse\ResponseBuilder;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HandlerRegister
{
    public static function register($exceptions = null): void
    {
        $register = new static();

        if ($exceptions !== null) {
            $register->registerWithExceptions($exceptions);
        }
    }

    public function registerWithExceptions($exceptions): void
    {
        $exceptions->render(function (AuthenticationException $e, $request) {
            return $this->handle($e, $request);
        });

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            return $this->handle($e, $request);
        });

        $exceptions->render(function (ValidationException $e, $request) {
            return $this->handle($e, $request);
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            return $this->handle($e, $request);
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            return $this->handle($e, $request);
        });

        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            return $this->handle($e, $request);
        });

        $exceptions->render(function (ApiException $e, $request) {
            return $this->handle($e, $request);
        });

        $exceptions->render(function (\Throwable $e, $request) {
            return $this->handle($e, $request);
        });
    }

    protected function handle(\Throwable $e, $request): mixed
    {
        $prefix = config('api-response.exception_handling.api_prefix', 'api/*');
        if (!$request->is($prefix) && !$request->expectsJson()) {
            return null;
        }

        $builder = app(ResponseBuilder::class);

        $response = match (true) {
            $e instanceof AuthenticationException => $builder->error('Unauthenticated', 401),
            $e instanceof ModelNotFoundException => $builder->error('Resource not found', 404),
            $e instanceof ValidationException => $builder->validationError($e->errors()),
            $e instanceof NotFoundHttpException => $builder->error('Route not found', 404),
            $e instanceof MethodNotAllowedHttpException => $builder->error('Method not allowed', 405),
            $e instanceof ThrottleRequestsException => $builder->error('Too many requests', 429),
            $e instanceof ApiException => $builder->error($e->getMessage(), $e->getCode() ?: 400),
            default => null,
        };

        if ($response !== null) {
            return $response;
        }

        if (config('api-response.exception_handling.debug_trace')) {
            return $builder->error($e->getMessage(), 500);
        }

        return $builder->error('Server error', 500);
    }
}
