<?php

namespace PhpDevUmesh\LaravelApiResponse\Exceptions;

use Exception;
use PhpDevUmesh\LaravelApiResponse\Facades\ApiResponse;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    public static function make(string $message = '', int $statusCode = 400): static
    {
        return new static($message, $statusCode);
    }

    public static function validationFailed(mixed $errors): static
    {
        $firstError = '';
        if ($errors instanceof \Illuminate\Support\MessageBag) {
            $firstError = $errors->first();
        } elseif (is_string($errors)) {
            $firstError = $errors;
        } elseif (is_array($errors)) {
            $firstError = reset($errors);
            if (is_array($firstError)) {
                $firstError = reset($firstError);
            }
        }
        return new static($firstError ?: 'Validation failed', 422);
    }

    public static function notFound(string $model = 'Resource'): static
    {
        return new static("{$model} not found", 404);
    }

    public static function serverError(string $message = 'Server error'): static
    {
        return new static($message, 500);
    }

    public static function throwIf(mixed $condition, string $message = '', int $statusCode = 400): void
    {
        if ($condition) {
            throw new static($message, $statusCode);
        }
    }

    public static function throwIfNotSave(mixed $result, string $message = 'Something went wrong', int $statusCode = 400): void
    {
        if (!$result) {
            throw new static($message, $statusCode);
        }
    }

    public static function throwIfEmpty(mixed $object, string $message = 'Not found', int $statusCode = 404): void
    {
        if (empty($object)) {
            throw new static($message, $statusCode);
        }
    }

    public function render(): JsonResponse
    {
        return ApiResponse::error($this->getMessage(), $this->getCode() ?: 400);
    }
}
