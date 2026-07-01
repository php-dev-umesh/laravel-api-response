<?php

namespace PhpDevUmesh\LaravelApiResponse;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResponseBuilder
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('api-response');
    }

    public function trans(string $message, array $replace = []): string
    {
        return $this->translate($message, $replace);
    }

    protected function translate(?string $message, array $replace = []): string
    {
        if (empty($message)) {
            return '';
        }

        $config = $this->config;

        if (!($config['auto_translate'] ?? true)) {
            return $message;
        }

        $prefix = $config['lang_prefix'] ?? 'message.';
        $replace = array_merge($config['lang_replace'] ?? [], $replace);
        $key = $prefix . $message;
        $translated = __($key, $replace);

        if (($config['lang_fallback'] ?? true) && $translated === $key) {
            return $message;
        }

        return $translated;
    }

    protected function buildResponse(
        bool $success,
        string $message = '',
        mixed $data = null,
        ?int $statusCode = null,
        array $extra = []
    ): array {
        $format = $this->config['format'];
        $statusCode ??= $success
            ? $this->config['default_status_success']
            : $this->config['default_status_error'];

        $response = [
            $format['success_key'] => $success,
            $format['status_key'] => $statusCode,
            $format['message_key'] => $this->translate($message),
            $format['data_key'] => $data,
        ];

        return array_merge($response, $extra);
    }

    protected function json(array $data, int $statusCode = 200, array $headers = []): JsonResponse
    {
        return response()->json($data, $statusCode, $headers);
    }

    public function success(mixed $data = [], string $message = '', array $replace = [], ?int $statusCode = null): JsonResponse
    {
        $statusCode ??= $this->config['default_status_success'];
        return $this->json(
            $this->buildResponse(true, $message, $data, $statusCode),
            $statusCode
        );
    }

    public function created(mixed $data = [], string $message = '', array $replace = []): JsonResponse
    {
        return $this->success($data, $message, $replace, 201);
    }

    public function ok(mixed $data = []): JsonResponse
    {
        return $this->success($data, '', [], 200);
    }

    public function noContent(): JsonResponse
    {
        $format = $this->config['format'];
        return $this->json([
            $format['success_key'] => true,
            $format['status_key'] => 204,
            $format['message_key'] => '',
            $format['data_key'] => null,
        ], 204);
    }

    public function message(string $message, array $replace = []): JsonResponse
    {
        return $this->success(null, $message, $replace);
    }

    public function error(string $message = '', ?int $statusCode = null, mixed $data = null): JsonResponse
    {
        $statusCode ??= $this->config['default_status_error'];
        return $this->json(
            $this->buildResponse(false, $message, $data, $statusCode),
            $statusCode
        );
    }

    public function validationError(mixed $errors, string $message = ''): JsonResponse
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

        $message = $message ?: ($firstError ?: 'Validation failed');
        $format = $this->config['format'];

        return $this->json([
            $format['success_key'] => false,
            $format['status_key'] => 422,
            $format['message_key'] => $this->translate($message),
            $format['data_key'] => null,
            'errors' => ($errors instanceof \Illuminate\Support\MessageBag) ? $errors->toArray() : $errors,
        ], 422);
    }

    public function paginated(LengthAwarePaginator $paginator, string $message = '', array $replace = []): JsonResponse
    {
        $format = $this->config['format'];
        $paginationConfig = $this->config['pagination'];
        $paginationFormat = $paginationConfig['format'] ?? 'standard';

        $extra = [];

        if ($paginationFormat === 'flat') {
            $extra['total_page'] = $paginator->lastPage();
            $extra['next_page'] = $paginator->nextPageUrl();
        } else {
            $keys = $paginationConfig['keys'];
            $extra['meta'] = [
                $keys['current_page'] => $paginator->currentPage(),
                $keys['last_page'] => $paginator->lastPage(),
                $keys['per_page'] => $paginator->perPage(),
                $keys['total'] => $paginator->total(),
                $keys['has_more'] => $paginator->hasMorePages(),
                $keys['next_page_url'] => $paginator->nextPageUrl(),
                $keys['prev_page_url'] => $paginator->previousPageUrl(),
            ];
        }

        $response = $this->buildResponse(true, $message, $paginator->items(), 200, $extra);
        return $this->json($response, 200);
    }

    public function resource(string $resourceClass, mixed $model, string $message = ''): JsonResponse
    {
        $data = (new $resourceClass($model))->resolve();
        return $this->success($data, $message);
    }

    public function collection(string $resourceClass, mixed $models, string $message = ''): JsonResponse
    {
        $data = $resourceClass::collection($models)->resolve();
        return $this->success($data, $message);
    }

    public function paginatedResource(
        string $resourceClass,
        LengthAwarePaginator $paginator,
        string $message = '',
        array $replace = []
    ): JsonResponse {
        $paginationConfig = $this->config['pagination'];
        $paginationFormat = $paginationConfig['format'] ?? 'standard';

        $data = $resourceClass::collection($paginator->items())->resolve();
        $extra = [];

        if ($paginationFormat === 'flat') {
            $extra['total_page'] = $paginator->lastPage();
            $extra['next_page'] = $paginator->nextPageUrl();
        } else {
            $keys = $paginationConfig['keys'];
            $extra['meta'] = [
                $keys['current_page'] => $paginator->currentPage(),
                $keys['last_page'] => $paginator->lastPage(),
                $keys['per_page'] => $paginator->perPage(),
                $keys['total'] => $paginator->total(),
                $keys['has_more'] => $paginator->hasMorePages(),
                $keys['next_page_url'] => $paginator->nextPageUrl(),
                $keys['prev_page_url'] => $paginator->previousPageUrl(),
            ];
        }

        $response = $this->buildResponse(true, $message, $data, 200, $extra);
        return $this->json($response, 200);
    }

    public function stream(callable $callback, string $message = '', array $replace = []): StreamedResponse
    {
        $message = $this->translate($message, $replace);

        return response()->stream(function () use ($callback, $message) {
            echo json_encode([
                'success' => true,
                'status' => 200,
                'message' => $message,
            ]) . "\n";
            $callback();
        }, 200, ['Content-Type' => 'application/x-ndjson']);
    }

    public function streamJson(array $data): StreamedResponse
    {
        return response()->stream(function () use ($data) {
            echo json_encode($data);
        }, 200, ['Content-Type' => 'application/json']);
    }

    public function sse(callable $callback): StreamedResponse
    {
        return response()->stream(function () use ($callback) {
            if (ob_get_level()) {
                ob_end_clean();
            }

            $emit = function (array $data, ?string $event = null) {
                if ($event) {
                    echo "event: {$event}\n";
                }
                echo 'data: ' . json_encode($data) . "\n\n";
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
            };

            $callback($emit);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function lazy($cursor, string $message = '', array $replace = []): StreamedResponse
    {
        $message = $this->translate($message, $replace);

        return response()->stream(function () use ($cursor, $message) {
            echo json_encode([
                'success' => true,
                'status' => 200,
                'message' => $message,
            ]) . "\n";
            foreach ($cursor as $item) {
                echo json_encode($item) . "\n";
            }
        }, 200, ['Content-Type' => 'application/x-ndjson']);
    }

    public function download(string $path, ?string $name = null): BinaryFileResponse
    {
        if (!file_exists($path)) {
            throw Exceptions\ApiException::make('File not found', 404);
        }
        return response()->download($path, $name);
    }

    public function file(string $path): BinaryFileResponse
    {
        if (!file_exists($path)) {
            throw Exceptions\ApiException::make('File not found', 404);
        }
        return response()->file($path);
    }

    public function streamDownload(callable $callback, string $name): StreamedResponse
    {
        return response()->streamDownload($callback, $name);
    }

    public function csv(array $headers, iterable $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, is_array($row) ? $row : (array) $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function downloadFromDisk(string $disk, string $path, ?string $name = null): StreamedResponse
    {
        if (!Storage::disk($disk)->exists($path)) {
            throw Exceptions\ApiException::make('File not found', 404);
        }

        $name ??= basename($path);
        $mime = Storage::disk($disk)->mimeType($path) ?? 'application/octet-stream';
        $size = Storage::disk($disk)->size($path);

        return response()->stream(function () use ($disk, $path) {
            $stream = Storage::disk($disk)->readStream($path);
            if ($stream) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Length' => $size,
            'Content-Disposition' => 'attachment; filename="' . $name . '"',
        ]);
    }
}
