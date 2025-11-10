<?php

namespace App\Services\WooCommerce;

use Generator;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class WooCommerceClient
{
    protected string $baseUrl;
    protected string $consumerKey;
    protected string $consumerSecret;
    protected int $timeout;
    protected bool $verifySsl;
    protected int $perPage;

    public function __construct(
        ?string $baseUrl = null,
        ?string $consumerKey = null,
        ?string $consumerSecret = null,
        ?int $perPage = null,
        ?int $timeout = null,
        ?bool $verifySsl = null,
    ) {
        $config = config('services.woocommerce', []);

        $configuredBaseUrl = $baseUrl ?? $config['base_url'] ?? null;
        if (empty($configuredBaseUrl)) {
            throw new RuntimeException('WooCommerce base URL is not configured.');
        }

        $this->baseUrl = rtrim($configuredBaseUrl, '/');
        $this->consumerKey = $consumerKey ?? $config['consumer_key'] ?? '';
        $this->consumerSecret = $consumerSecret ?? $config['consumer_secret'] ?? '';

        if ($this->consumerKey === '' || $this->consumerSecret === '') {
            throw new RuntimeException('WooCommerce consumer key/secret are not configured.');
        }

        $this->timeout = (int) ($timeout ?? $config['timeout'] ?? 30);
        $this->verifySsl = filter_var($verifySsl ?? $config['verify_ssl'] ?? true, FILTER_VALIDATE_BOOL);
        $this->perPage = $this->normalizePerPage($perPage ?? $config['per_page'] ?? 50);
    }

    public function defaultPerPage(): int
    {
        return $this->perPage;
    }

    public function getCategories(array $query = []): Generator
    {
        return $this->fetchAll('products/categories', $query);
    }

    public function getProducts(array $query = []): Generator
    {
        return $this->fetchAll('products', $query);
    }

    public function getProductVariations(int $productId, array $query = []): Generator
    {
        return $this->fetchAll("products/{$productId}/variations", $query);
    }

    protected function fetchAll(string $endpoint, array $query = []): Generator
    {
        $page = 1;
        $perPage = $this->normalizePerPage($query['per_page'] ?? $this->perPage);

        do {
            $response = $this->get($endpoint, array_merge($query, [
                'page' => $page,
                'per_page' => $perPage,
            ]));

            $items = $response->json();
            if (! is_array($items)) {
                throw new RuntimeException("Unexpected response received from WooCommerce endpoint [{$endpoint}].");
            }

            foreach ($items as $item) {
                yield $item;
            }

            $totalPages = (int) ($response->header('X-WP-TotalPages') ?? 0);
            $page++;

            if ($totalPages <= 0 && empty($items)) {
                break;
            }

            if ($totalPages > 0 && $page > $totalPages) {
                break;
            }
        } while (! empty($items));
    }

    protected function get(string $endpoint, array $query = []): Response
    {
        $query = array_merge([
            'consumer_key' => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret,
        ], $query);

        return $this->newRequest()->get($endpoint, $query);
    }

    protected function newRequest(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout($this->timeout)
            ->throw();

        if (! $this->verifySsl) {
            $request = $request->withoutVerifying();
        }

        return $request;
    }

    protected function normalizePerPage(int $perPage): int
    {
        return max(1, min($perPage, 100));
    }
}
