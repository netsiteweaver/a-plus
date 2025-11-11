<?php

namespace App\Services\WooCommerce;

use Generator;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WooCommerceClient
{
    protected PendingRequest $http;

    protected array $config;

    protected string $baseUrl;

    public function __construct()
    {
        $this->config = config('woocommerce', []);

        $this->baseUrl = $this->prepareBaseUrl(Arr::get($this->config, 'url'));

        $this->http = $this->buildRequest();
    }

    public function defaultStatuses(): array
    {
        return Arr::get($this->config, 'statuses', ['publish', 'draft']);
    }

    public function defaultPerPage(): int
    {
        return (int) Arr::get($this->config, 'per_page', 50);
    }

    public function listProducts(int $page = 1, array $parameters = []): array
    {
        $statusFilter = Arr::get($parameters, 'statuses', $this->defaultStatuses());

        $params = array_merge([
            'page' => $page,
            'per_page' => $this->defaultPerPage(),
            'orderby' => 'id',
            'order' => 'asc',
        ], Arr::except($parameters, ['statuses']));

        // WooCommerce REST API expects status as a single value, not comma-separated
        // If multiple statuses are provided, we'll use the first one
        if (! empty($statusFilter)) {
            $params['status'] = is_array($statusFilter) ? $statusFilter[0] : $statusFilter;
        }

        return $this->get('products', $params);
    }

    public function getProduct(int $remoteId, array $parameters = []): array
    {
        return $this->get('products/'.$remoteId, $parameters);
    }

    public function listProductVariations(int $remoteProductId, int $page = 1, array $parameters = []): array
    {
        $params = array_merge([
            'page' => $page,
            'per_page' => Arr::get($parameters, 'per_page', min(100, $this->defaultPerPage())),
            'orderby' => 'id',
            'order' => 'asc',
        ], $parameters);

        return $this->get("products/{$remoteProductId}/variations", $params);
    }

    /**
     * Get categories as a generator (used by WooCommerceImporter).
     *
     * @param  array  $query
     * @return Generator
     */
    public function getCategories(array $query = []): Generator
    {
        return $this->fetchAll('products/categories', $query);
    }

    /**
     * Get products as a generator (used by WooCommerceImporter).
     *
     * @param  array  $query
     * @return Generator
     */
    public function getProducts(array $query = []): Generator
    {
        return $this->fetchAll('products', $query);
    }

    /**
     * Get product variations as a generator (used by WooCommerceImporter).
     *
     * @param  int  $productId
     * @param  array  $query
     * @return Generator
     */
    public function getProductVariations(int $productId, array $query = []): Generator
    {
        return $this->fetchAll("products/{$productId}/variations", $query);
    }

    /**
     * Fetch all items from an endpoint using pagination, returning a generator.
     *
     * @param  string  $endpoint
     * @param  array  $query
     * @return Generator
     */
    protected function fetchAll(string $endpoint, array $query = []): Generator
    {
        $page = 1;
        $perPage = $this->normalizePerPage($query['per_page'] ?? $this->defaultPerPage());

        do {
            $response = $this->getWithResponse($endpoint, array_merge($query, [
                'page' => $page,
                'per_page' => $perPage,
            ]));

            $items = $response->json();
            if (! is_array($items)) {
                break;
            }

            foreach ($items as $item) {
                yield $item;
            }

            $totalPages = (int) ($response->header('X-WP-TotalPages') ?? 0);
            $page++;

            if ($totalPages > 0 && $page > $totalPages) {
                break;
            }

            if (empty($items)) {
                break;
            }
        } while (true);
    }

    /**
     * Normalize per_page value to be between 1 and 100.
     *
     * @param  int  $perPage
     * @return int
     */
    protected function normalizePerPage(int $perPage): int
    {
        return max(1, min($perPage, 100));
    }

    protected function get(string $endpoint, array $query = []): array
    {
        return $this->getWithResponse($endpoint, $query)->json();
    }

    protected function getWithResponse(string $endpoint, array $query = []): Response
    {
        $response = $this->http
            ->retry(
                (int) Arr::get($this->config, 'max_retries', 3),
                (int) Arr::get($this->config, 'retry_delay_ms', 500)
            )
            ->get($endpoint, $query);

        return $response->throw();
    }

    protected function buildRequest(): PendingRequest
    {
        if (empty($this->baseUrl)) {
            throw new WooCommerceException('WooCommerce base URL is not configured.');
        }

        $request = Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout((int) Arr::get($this->config, 'timeout', 15))
            ->withOptions([
                'verify' => (bool) Arr::get($this->config, 'verify', true),
            ]);

        $key = Arr::get($this->config, 'consumer_key');
        $secret = Arr::get($this->config, 'consumer_secret');

        if ($key && $secret) {
            $request = $request->withBasicAuth($key, $secret);
        }

        $request = $request->withHeaders([
            'User-Agent' => $this->buildUserAgent(),
        ]);

        return $request;
    }

    protected function buildUserAgent(): string
    {
        $appName = config('app.name', 'Laravel');
        $appVersion = config('app.version');

        return trim(sprintf('%s WooCommerceImporter%s', $appName, $appVersion ? "/{$appVersion}" : ''));
    }

    protected function prepareBaseUrl(?string $url): string
    {
        if (empty($url)) {
            return '';
        }

        $normalized = rtrim($url, '/');

        if (! Str::contains($normalized, '/wp-json')) {
            $normalized .= '/wp-json/wc/v3';
        }

        return $normalized.'/';
    }
}
