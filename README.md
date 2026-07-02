# Laravel API Response Builder — Consistent JSON Responses for Laravel

[![Packagist Version](https://img.shields.io/packagist/v/php-dev-umesh/laravel-api-response)](https://packagist.org/packages/php-dev-umesh/laravel-api-response)
[![Packagist Downloads](https://img.shields.io/packagist/dt/php-dev-umesh/laravel-api-response)](https://packagist.org/packages/php-dev-umesh/laravel-api-response)
[![PHP](https://img.shields.io/badge/PHP-8.1+-%23777BB4.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10|11|12|13-red.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![GitHub Stars](https://img.shields.io/github/stars/php-dev-umesh/laravel-api-response?style=social)](https://github.com/php-dev-umesh/laravel-api-response)

A comprehensive, fluent API response builder for Laravel with **30+ methods** covering success, error, pagination, API resources, streaming (NDJSON/SSE), file downloads, auto-translation, and exception handling — all in one consistent format. Eliminate duplicated JSON response code across your Laravel REST API. Includes **auto-translation**, **exception handling**, **ApiFormRequest**, and **auto-wrap middleware** for retrofitting existing APIs.

**Supports Laravel 10, 11, 12, and 13. Works with PHP 8.1+.**

> [📖 Documentation site](https://php-dev-umesh.github.io/laravel-api-response) • [Packagist](https://packagist.org/packages/php-dev-umesh/laravel-api-response) • [GitHub](https://github.com/php-dev-umesh/laravel-api-response)

---

## Features

- **Fluent response builder** — `ApiResponse::success()`, `error()`, `created()`, `noContent()`, etc.
- **Auto-translate** — every message auto-resolves via `__("message.$key")` with configurable prefix and fallback
- **Pagination** — standard meta format or flat format (matching `{total_page, next_page}` style)
- **API Resources** — wrap `UserResource` and collections seamlessly
- **Streaming** — NDJSON streams, Server-Sent Events (SSE), lazy collections
- **Downloads** — file download, inline preview, streaming CSV generation, storage disk downloads
- **Exception handling** — single trait or one-liner registration for common API exceptions
- **Form Request** — `ApiFormRequest` returns API-consistent validation errors
- **Auto-wrap middleware** — retrofit existing `response()->json()` code
- **Configurable** — custom response keys (success/status/message/data), status codes, translation behavior
- **Zero duplication** — no more copy-pasting helper classes between projects

---

## Installation

### From Packagist (recommended)

```bash
composer require php-dev-umesh/laravel-api-response
```

### From GitHub Packages

Add the GitHub Packages Composer registry and require the package:

```json
{
    "repositories": [
        { "type": "composer", "url": "https://composer.pkg.github.com/php-dev-umesh" }
    ],
    "require": {
        "php-dev-umesh/laravel-api-response": "^1.0"
    }
}
```

Then authenticate with a [GitHub Personal Access Token](https://github.com/settings/tokens) (classic PAT with `read:packages` scope):

```bash
composer config --global github-oauth.github.com YOUR_PAT
```

Now run:

```bash
composer require php-dev-umesh/laravel-api-response
```

### Publish Config (Optional)

```bash
php artisan vendor:publish --tag=api-response-config
```

This creates `config/api-response.php` (defaults work out of the box).

---

## Quick Start

```php
use PhpDevUmesh\LaravelApiResponse\Facades\ApiResponse;

// In any controller or route:
ApiResponse::success($user, 'user.found');
// → {success: true, status: 200, message: "User found", data: {...}}

ApiResponse::error('user.not_found', 404);
// → {success: false, status: 404, message: "User not found", data: null}
```

Or use the trait in your controllers:

```php
use PhpDevUmesh\LaravelApiResponse\Traits\ApiResponseTrait;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function index()
    {
        return $this->paginated(User::paginate(), 'users.loaded');
    }

    public function store(StoreUserRequest $request)
    {
        return $this->created(User::create(...), 'user.created');
    }
}
```

---

## Response Format

Default structure:

```json
{
  "success": true,
  "status": 200,
  "message": "User found",
  "data": { ... }
}
```

All keys are configurable in `config/api-response.php`:

```php
'format' => [
    'success_key' => 'success',
    'status_key'  => 'status',
    'message_key' => 'message',
    'data_key'    => 'data',
],
```

---

## Full API Reference

### Success Responses

| Method | Description | Default Status |
|--------|-------------|----------------|
| `success($data, $message, $replace, $statusCode)` | Generic success | 200 |
| `created($data, $message, $replace)` | Resource created | 201 |
| `ok($data)` | Success without message | 200 |
| `noContent()` | Success with no content | 204 |
| `message($message, $replace)` | Success with only message | 200 |

```php
ApiResponse::success($user, 'user.found');
ApiResponse::success($user, 'user.found', ['name' => $user->name]);
ApiResponse::created($user, 'user.created');
ApiResponse::ok($users);
ApiResponse::noContent();
ApiResponse::message('operation.completed');
```

### Error Responses

| Method | Description | Default Status |
|--------|-------------|----------------|
| `error($message, $statusCode, $data)` | Generic error | 400 |
| `validationError($errors, $message)` | Validation error | 422 |

```php
ApiResponse::error('user.not_found', 404);
ApiResponse::error('Unauthorized', 401);
ApiResponse::validationError($validator->errors());
```

### Pagination

| Method | Description |
|--------|-------------|
| `paginated($paginator, $message, $replace)` | Paginated collection |
| `paginatedResource($resourceClass, $paginator, $message, $replace)` | Paginated with API Resources |

```php
ApiResponse::paginated(User::paginate(), 'users.loaded');

// Standard format (config 'standard'):
// {success, status, message, data: [...], meta: {current_page, last_page, per_page, total, has_more, ...}}

// Flat format (config 'flat'):
// {success, status, message, data: [...], total_page: 10, next_page: "..."}
```

### API Resources

| Method | Description |
|--------|-------------|
| `resource($class, $model, $message)` | Single resource |
| `collection($class, $models, $message)` | Resource collection |
| `paginatedResource($class, $paginator, $message, $replace)` | Paginated resources |

```php
ApiResponse::resource(UserResource::class, $user);
ApiResponse::collection(UserResource::class, User::all());
ApiResponse::paginatedResource(UserResource::class, User::paginate(), 'users.loaded');
```

### Streaming

| Method | Description | Content-Type |
|--------|-------------|--------------|
| `stream($callback, $message, $replace)` | NDJSON stream with header | `application/x-ndjson` |
| `streamJson($data)` | Single JSON stream | `application/json` |
| `sse($callback)` | Server-Sent Events | `text/event-stream` |
| `lazy($cursor, $message, $replace)` | Lazy collection stream | `application/x-ndjson` |

```php
// Stream large dataset line-by-line (no memory spike)
ApiResponse::stream(function () {
    foreach (User::cursor() as $user) {
        echo json_encode(['id' => $user->id, 'name' => $user->name]) . "\n";
    }
}, 'exporting.users');

// Server-Sent Events for real-time progress
ApiResponse::sse(function ($emit) {
    foreach ($process as $step) {
        $emit(['progress' => $step / $total], 'progress');
        usleep(500000);
    }
    $emit(['message' => 'Complete'], 'complete');
});

// Lazy collection stream
ApiResponse::lazy(User::cursor(), 'exporting.users');
```

### Downloads

| Method | Description |
|--------|-------------|
| `download($path, $name)` | Force file download |
| `file($path)` | Inline file preview |
| `streamDownload($callback, $name)` | Generate file on the fly |
| `csv($headers, $rows, $filename)` | Stream CSV download |
| `downloadFromDisk($disk, $path, $name)` | Download from storage disk |

```php
ApiResponse::download(storage_path('app/report.pdf'), 'report.pdf');
ApiResponse::file(storage_path('app/invoice.pdf'));
ApiResponse::streamDownload(function () {
    $handle = fopen('php://output', 'w');
    fputcsv($handle, ['Name', 'Email']);
    foreach (User::cursor() as $user) {
        fputcsv($handle, [$user->name, $user->email]);
    }
    fclose($handle);
}, 'users.csv');

ApiResponse::csv(
    headers: ['Name', 'Email', 'Score'],
    rows: User::cursor()->map(fn($u) => [$u->name, $u->email, $u->score]),
    filename: 'scores.csv'
);

ApiResponse::downloadFromDisk('s3', 'exports/report.pdf', 'report.pdf');
```

---

## Auto-Translation

When `auto_translate` is `true` (default), every message string is automatically run through Laravel's `__()` helper.

```php
// config/api-response.php
'auto_translate' => true,
'lang_prefix'    => 'message.',
'lang_fallback'  => true,

// In your code — just pass the key:
ApiResponse::success($user, 'user.found');
// Automatically resolves to: __("message.user.found")
// If translation file has: "user.found" => "User found successfully"
// Response: { "message": "User found successfully" }

// With dynamic replacements:
ApiResponse::success($user, 'welcome_user', ['name' => 'Umesh']);
// Resolves to: __("message.welcome_user", ['name' => 'Umesh'])
// If lang file has: "welcome_user" => "Welcome, :name!"
// Response: { "message": "Welcome, Umesh!" }

// Fallback behavior:
// if "message.user.found" doesn't exist in lang files AND lang_fallback = true
// → uses "user.found" as the raw message

// Disable auto-translate:
'auto_translate' => false,
// Now strings pass through as-is: "user.found" → "user.found"
```

You can also use the `api_trans()` helper anywhere:

```php
$text = api_trans('user.found', ['name' => 'Umesh']);
```

Global replacement defaults can be set in config:

```php
'lang_replace' => ['app_name' => 'MyApp'],
// Merged with any per-call replacements
```

---

## Exception Handling

### Option A — Laravel 10: Handler Trait

Add the trait to `app/Exceptions/Handler.php`:

```php
<?php

namespace App\Exceptions;

use PhpDevUmesh\LaravelApiResponse\Exceptions\RendersApiExceptions;
use Throwable;

class Handler extends ExceptionHandler
{
    use RendersApiExceptions;

    public function render($request, Throwable $e)
    {
        // Try API rendering first, fall back to Laravel's default
        return $this->renderApiException($request, $e) ?? parent::render($request, $e);
    }
}
```

### Option B — Laravel 11/12/13: Bootstrap Registration

In `bootstrap/app.php`:

```php
use PhpDevUmesh\LaravelApiResponse\Exceptions\HandlerRegister;

return Application::configure(basePath: dirname(__DIR__))
    ->withExceptions(function ($exceptions) {
        HandlerRegister::register($exceptions);
    })
    // ...
    ->create();
```

### What gets handled automatically:

| Exception | HTTP Status |
|-----------|-------------|
| `AuthenticationException` | 401 |
| `ModelNotFoundException` | 404 |
| `ValidationException` | 422 (with errors) |
| `NotFoundHttpException` | 404 |
| `MethodNotAllowedHttpException` | 405 |
| `ThrottleRequestsException` | 429 |
| `ApiException` | Custom (default 400) |
| Any other `Throwable` | 500 (with debug trace if `APP_DEBUG=true`) |

---

## Throwable ApiException

Throw from anywhere in your application:

```php
use PhpDevUmesh\LaravelApiResponse\Exceptions\ApiException;

throw ApiException::make('user.not_found', 404);
throw ApiException::validationFailed($validator->errors());
throw ApiException::notFound('User');
throw ApiException::serverError('Something went wrong');

// Guard methods (throw on condition):
ApiException::throwIf($user->isBanned(), 'Account blocked', 403);
ApiException::throwIfNotSave($user->save());
ApiException::throwIfEmpty($collection);
```

---

## ApiFormRequest (Validation)

Replace `extends FormRequest` with `extends ApiFormRequest`:

```php
use PhpDevUmesh\LaravelApiResponse\Http\ApiFormRequest;

class StoreUserRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users',
            'name' => 'required|string|max:255',
        ];
    }
}
```

On validation failure, automatically returns:

```json
{
  "success": false,
  "status": 422,
  "message": "The email field is required.",
  "data": null,
  "errors": {
    "email": ["The email field is required."]
  }
}
```

---

## Auto-Wrap Middleware

Retrofit existing controllers that use raw `response()->json()` without modifying them:

```php
// In routes/api.php:
Route::middleware('api-response')->group(function () {
    Route::get('/users', function () {
        return response()->json(['id' => 1, 'name' => 'John']);
        // Automatically wrapped to:
        // {success: true, status: 200, message: "", data: {id: 1, name: "John"}}
    });
});
```

The middleware skips:
- Non-JSON responses
- Responses already in the package format (by detecting the `success`/`status` keys)
- Already-wrapped responses (detected via `X-Api-Response-Wrapped` header)

---

## Configuration Reference

### `config/api-response.php`

```php
<?php

return [
    // Response JSON key names
    'format' => [
        'success_key' => 'success',
        'status_key'  => 'status',
        'message_key' => 'message',
        'data_key'    => 'data',
    ],

    // Default status codes
    'default_status_success' => 200,
    'default_status_error'   => 400,

    // Auto-translation settings
    'auto_translate' => true,         // Auto-run messages through __()
    'lang_prefix'    => 'message.',   // Translation file prefix
    'lang_fallback'  => true,         // Use raw key if no translation found
    'lang_replace'   => [],           // Global replacement defaults

    // Pagination format
    'pagination' => [
        'format' => 'standard',       // 'standard' or 'flat'
        'keys' => [
            'current_page'  => 'current_page',
            'last_page'     => 'last_page',
            'per_page'      => 'per_page',
            'total'         => 'total',
            'has_more'      => 'has_more',
            'next_page_url' => 'next_page_url',
            'prev_page_url' => 'prev_page_url',
        ],
    ],

    // Streaming
    'stream' => [
        'format'     => 'ndjson',
        'chunk_size' => 100,
    ],

    // Downloads
    'download' => [
        'delete_after_send' => false,
        'max_file_size'     => 100,  // MB
    ],

    // Exception handling
    'exception_handling' => [
        'api_prefix'  => 'api/*',
        'debug_trace' => env('APP_DEBUG', false),
    ],

    // Predefined status codes (for reference, accessible via config)
    'status_codes' => [
        'OK'                => 200,
        'CREATED'           => 201,
        'NO_CONTENT'        => 204,
        'BAD_REQUEST'       => 400,
        'UNAUTHORIZED'      => 401,
        'FORBIDDEN'         => 403,
        'NOT_FOUND'         => 404,
        'METHOD_NOT_ALLOWED' => 405,
        'CONFLICT'          => 409,
        'UNPROCESSABLE'     => 422,
        'TOO_MANY_REQUESTS' => 429,
        'SERVER_ERROR'      => 500,
    ],
];
```

Access any config value:

```php
config('api-response.status_codes.CREATED');       // 201
config('api-response.format.success_key');          // 'success'
config('api-response.pagination.format');           // 'standard'
```

---

## Migration from Your Current Code

If you're using a custom `Helper` class similar to the one this package replaces:

| Current Code | Replace With |
|---|---|
| `Helper::SuccessReturn($data, 'key')` | `ApiResponse::success($data, 'key')` |
| `Helper::FalseReturn($data, 'key')` | `ApiResponse::error('key')` |
| `Helper::EmptyReturn('key')` | `ApiResponse::message('key')` or `ApiResponse::error('key', 404)` |
| `Helper::StatusReturn($data, 'key', [], [], 201)` | `ApiResponse::created($data, 'key')` |
| `Helper::SuccessReturnPagination($data, $total, $next, 'key')` | `ApiResponse::paginated($paginator, 'key')` |
| `Helper::UpdateObjectIfKeyExist($obj, $req, $keys)` | Use `$obj->fill($req->only($keys))` or `$obj->update($req->only($keys))` |
| `throw new PublicException($msg, $code)` | `throw ApiException::make($msg, $code)` |
| `PublicException::Validator($data, $rules)` | Use `ApiFormRequest` instead |
| `PublicException::NotSave($state)` | `ApiException::throwIfNotSave($state)` |
| `PublicException::Empty($obj)` | `ApiException::throwIfEmpty($obj)` |
| `PublicException::SaveAndCommit($obj)` | `DB::transaction(fn => ApiException::throwIfNotSave($obj->save()))` |
| `__("message." . $key)` boilerplate repeated everywhere | Set `auto_translate: true` — package handles it centrally |

---

## Testing (using Orchestra Testbench)

The package is tested against Laravel 10, 11, 12, and 13 using Orchestra Testbench.

```bash
composer require --dev orchestra/testbench
```

Example test:

```php
<?php

use PhpDevUmesh\LaravelApiResponse\Facades\ApiResponse;
use PhpDevUmesh\LaravelApiResponse\ApiResponseServiceProvider;

class ResponseBuilderTest extends Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [ApiResponseServiceProvider::class];
    }

    /** @test */
    public function it_returns_success_response()
    {
        $response = ApiResponse::success(['name' => 'John'], 'user.found');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 200,
            'message' => 'user.found',
            'data' => ['name' => 'John'],
        ]);
    }
}
```

---

## Requirements

- PHP 8.1 or higher
- Laravel 10, 11, 12, or 13

---

## Changelog

See [CHANGELOG](CHANGELOG.md) for recent changes.

---

## Contributing

See [CONTRIBUTING](CONTRIBUTING.md) for details.

---

## Community & Outreach

- **[Laravel News](https://laravel-news.com)** — Follow for Laravel ecosystem updates
- **[r/laravel](https://reddit.com/r/laravel)** — Discuss Laravel packages and development
- **[Laravel.io Forum](https://laravel.io/forum)** — Community discussions
- **[DEV.to](https://dev.to)** — Laravel tutorials and package showcases
- **X/Twitter** — Tag [@laravelphp](https://twitter.com/laravelphp) with your API projects

If you find this package useful, please [star the repo](https://github.com/php-dev-umesh/laravel-api-response) on GitHub — it helps others discover it!

## License

This package is open-source software licensed under the [MIT license](LICENSE).
