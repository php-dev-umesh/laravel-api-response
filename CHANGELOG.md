# Changelog

All notable changes to this project will be documented in this file.

## 1.0.1 — 2026-07-01

- Added Laravel 13 support
- Added GitHub Packages distribution channel
- Added SEO-optimized docs site (GitHub Pages)
- Replaced `update-packagist.yml` with combined `release.yml` workflow (GitHub Release + Packagist notification)

## 1.0.0 — 2026-07-01

Initial release.

### Added

- `ApiResponse` facade with fluent response builder
- Response types: `success()`, `created()`, `ok()`, `noContent()`, `message()`, `error()`, `validationError()`
- Pagination support: `paginated()`, `paginatedResource()` (standard and flat formats)
- API Resource integration: `resource()`, `collection()`
- Streaming: `stream()`, `streamJson()`, `sse()`, `lazy()` (NDJSON and SSE)
- Downloads: `download()`, `file()`, `streamDownload()`, `csv()`, `downloadFromDisk()`
- Auto-translation with configurable prefix, fallback, and global replacements
- `ApiResponseTrait` for controller usage
- `ApiException` with `throwIf()`, `throwIfNotSave()`, `throwIfEmpty()` guards
- `RendersApiExceptions` trait for Laravel 10 `Handler.php`
- `HandlerRegister` for Laravel 11/12 `bootstrap/app.php`
- `ApiFormRequest` for API-consistent validation errors
- `WrapApiResponse` middleware for auto-wrapping existing JSON responses
- Configurable response format keys (`success`, `status`, `message`, `data`)
- `api_trans()` global helper function
- Compatible with Laravel 10, 11, 12, and 13
- PHP 8.1 minimum requirement
