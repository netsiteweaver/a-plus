<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WooCommerce Store Base URL
    |--------------------------------------------------------------------------
    |
    | This should point to the WooCommerce REST API base endpoint, typically
    | https://your-store.com/wp-json/wc/v3. A trailing slash is optional.
    |
    */
    'url' => env('WOOCOMMERCE_URL'),

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Consumer key and secret generated from the WooCommerce REST API settings.
    | Keys are used for HTTP basic authentication when talking to WooCommerce.
    |
    */
    'consumer_key' => env('WOOCOMMERCE_CONSUMER_KEY'),
    'consumer_secret' => env('WOOCOMMERCE_CONSUMER_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Pagination & Query Defaults
    |--------------------------------------------------------------------------
    */
    'per_page' => (int) env('WOOCOMMERCE_PER_PAGE', 50),
    'statuses' => collect(explode(',', (string) env('WOOCOMMERCE_STATUSES', 'publish,draft')))
        ->map(fn ($status) => trim($status))
        ->filter()
        ->values()
        ->all(),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Behaviour
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('WOOCOMMERCE_TIMEOUT', 15),
    'verify' => filter_var(env('WOOCOMMERCE_VERIFY_TLS', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
        ?? true,
    'max_retries' => (int) env('WOOCOMMERCE_MAX_RETRIES', 3),
    'retry_delay_ms' => (int) env('WOOCOMMERCE_RETRY_DELAY_MS', 500),

    /*
    |--------------------------------------------------------------------------
    | Catalog Defaults
    |--------------------------------------------------------------------------
    */
    'default_currency' => env('WOOCOMMERCE_DEFAULT_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Media Import Settings
    |--------------------------------------------------------------------------
    |
    | Control how product images are handled during import.
    | - download_images: When true, downloads and stores images locally.
    |                    When false, stores remote URLs only (faster import).
    |
    */
    'download_images' => filter_var(env('WOOCOMMERCE_DOWNLOAD_IMAGES', true), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE)
        ?? true,
];
