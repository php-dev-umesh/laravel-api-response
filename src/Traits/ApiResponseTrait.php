<?php

namespace PhpDevUmesh\LaravelApiResponse\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use PhpDevUmesh\LaravelApiResponse\Facades\ApiResponse;

trait ApiResponseTrait
{
    public function success(mixed $data = [], string $message = '', array $replace = [], ?int $statusCode = null)
    {
        return ApiResponse::success($data, $message, $replace, $statusCode);
    }

    public function created(mixed $data = [], string $message = '', array $replace = [])
    {
        return ApiResponse::created($data, $message, $replace);
    }

    public function ok(mixed $data = [])
    {
        return ApiResponse::ok($data);
    }

    public function noContent()
    {
        return ApiResponse::noContent();
    }

    public function message(string $message, array $replace = [])
    {
        return ApiResponse::message($message, $replace);
    }

    public function error(string $message = '', ?int $statusCode = null, mixed $data = null)
    {
        return ApiResponse::error($message, $statusCode, $data);
    }

    public function validationError(mixed $errors, string $message = '')
    {
        return ApiResponse::validationError($errors, $message);
    }

    public function paginated(LengthAwarePaginator $paginator, string $message = '', array $replace = [])
    {
        return ApiResponse::paginated($paginator, $message, $replace);
    }

    public function resource(string $resourceClass, mixed $model, string $message = '')
    {
        return ApiResponse::resource($resourceClass, $model, $message);
    }

    public function collection(string $resourceClass, mixed $models, string $message = '')
    {
        return ApiResponse::collection($resourceClass, $models, $message);
    }

    public function paginatedResource(string $resourceClass, LengthAwarePaginator $paginator, string $message = '', array $replace = [])
    {
        return ApiResponse::paginatedResource($resourceClass, $paginator, $message, $replace);
    }

    public function stream(callable $callback, string $message = '', array $replace = [])
    {
        return ApiResponse::stream($callback, $message, $replace);
    }

    public function streamJson(array $data)
    {
        return ApiResponse::streamJson($data);
    }

    public function sse(callable $callback)
    {
        return ApiResponse::sse($callback);
    }

    public function lazy($cursor, string $message = '', array $replace = [])
    {
        return ApiResponse::lazy($cursor, $message, $replace);
    }

    public function download(string $path, ?string $name = null)
    {
        return ApiResponse::download($path, $name);
    }

    public function file(string $path)
    {
        return ApiResponse::file($path);
    }

    public function streamDownload(callable $callback, string $name)
    {
        return ApiResponse::streamDownload($callback, $name);
    }

    public function csv(array $headers, iterable $rows, string $filename)
    {
        return ApiResponse::csv($headers, $rows, $filename);
    }

    public function downloadFromDisk(string $disk, string $path, ?string $name = null)
    {
        return ApiResponse::downloadFromDisk($disk, $path, $name);
    }
}
