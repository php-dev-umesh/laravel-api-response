<?php

return [
    'format' => [
        'success_key' => 'success',
        'status_key' => 'status',
        'message_key' => 'message',
        'data_key' => 'data',
    ],

    'default_status_success' => 200,
    'default_status_error' => 400,

    'auto_translate' => true,
    'lang_prefix' => 'message.',
    'lang_fallback' => true,
    'lang_replace' => [],

    'pagination' => [
        'format' => 'standard',
        'keys' => [
            'current_page' => 'current_page',
            'last_page' => 'last_page',
            'per_page' => 'per_page',
            'total' => 'total',
            'has_more' => 'has_more',
            'next_page_url' => 'next_page_url',
            'prev_page_url' => 'prev_page_url',
        ],
    ],

    'stream' => [
        'format' => 'ndjson',
        'chunk_size' => 100,
    ],

    'download' => [
        'delete_after_send' => false,
        'max_file_size' => 100,
    ],

    'exception_handling' => [
        'api_prefix' => 'api/*',
        'debug_trace' => env('APP_DEBUG', false),
    ],

    'status_codes' => [
        'OK' => 200,
        'CREATED' => 201,
        'NO_CONTENT' => 204,
        'BAD_REQUEST' => 400,
        'UNAUTHORIZED' => 401,
        'FORBIDDEN' => 403,
        'NOT_FOUND' => 404,
        'METHOD_NOT_ALLOWED' => 405,
        'CONFLICT' => 409,
        'UNPROCESSABLE' => 422,
        'TOO_MANY_REQUESTS' => 429,
        'SERVER_ERROR' => 500,
    ],
];
