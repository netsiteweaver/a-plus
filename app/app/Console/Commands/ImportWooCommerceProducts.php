<?php

namespace App\Console\Commands;

use App\Services\WooCommerce\WooCommerceException;
use App\Services\WooCommerce\WooCommerceProductImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'catalog:import-woocommerce', description: 'Import products, categories, variants, and media from a WooCommerce store.')]
class ImportWooCommerceProducts extends Command
{
    protected $signature = 'catalog:import-woocommerce
        {--product-id=* : Remote WooCommerce product IDs to import}
        {--status=* : WooCommerce statuses to include (publish, draft, etc.)}
        {--per-page= : Number of products to request per page}
        {--limit= : Maximum number of products to process}';

    protected $description = 'Import products, categories, variants, and media from a WooCommerce store.';

    public function handle(WooCommerceProductImporter $importer): int
    {
        if (! config('woocommerce.url')) {
            $this->components->error('WooCommerce URL is not configured. Set WOOCOMMERCE_URL in your environment.');

            return self::FAILURE;
        }

        if (! config('woocommerce.consumer_key') || ! config('woocommerce.consumer_secret')) {
            $this->components->warn('WooCommerce API credentials are not fully configured. Requests may fail if the store is private.');
        }

        $productIds = $this->parseIdOptions('product-id');
        $statuses = $this->parseStatuses();
        $perPage = $this->option('per-page') ? (int) $this->option('per-page') : null;
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;

        $this->components->info('Starting WooCommerce import…');
        $this->newLine();

        $callback = function (string $event, array $payload): void {
            match ($event) {
                'created' => $this->output->write('<info>+</info>'),
                'updated' => $this->output->write('<comment>~</comment>'),
                'skipped' => $this->output->write('<fg=yellow>-</>'),
                'failed' => $this->handleFailureFeedback($payload),
                default => null,
            };
        };

        $importOptions = [
            'product_ids' => $productIds,
        ];

        if ($statuses !== null) {
            $importOptions['statuses'] = $statuses;
        }

        if ($perPage !== null) {
            $importOptions['per_page'] = $perPage;
        }

        if ($limit !== null) {
            $importOptions['limit'] = $limit;
        }

        try {
            $summary = $importer->import($importOptions, $callback);
        } catch (WooCommerceException $exception) {
            $this->newLine();
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->newLine(2);
        $this->components->info('Import complete.');
        $this->displaySummary($summary);

        if (! empty($summary['failed'])) {
            $this->newLine();
            $this->components->error('Some products failed to import:');

            foreach ($summary['failed'] as $failure) {
                $remoteId = Arr::get($failure, 'remote_id') ?? Arr::get($failure, 'page') ?? 'unknown';
                $message = Arr::get($failure, 'message', 'Unknown error');
                $this->line("  • {$remoteId}: {$message}");
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function parseIdOptions(string $option): array
    {
        return collect($this->option($option))
            ->flatMap(fn ($value) => explode(',', (string) $value))
            ->map(fn ($value) => trim($value))
            ->filter(fn ($value) => $value !== '')
            ->map(fn ($value) => (int) $value)
            ->unique()
            ->values()
            ->all();
    }

    protected function parseStatuses(): ?array
    {
        $statuses = collect($this->option('status'))
            ->flatMap(fn ($value) => explode(',', (string) $value))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return empty($statuses) ? null : $statuses;
    }

    protected function handleFailureFeedback(array $payload): void
    {
        $this->output->writeln('');
        $remoteId = Arr::get($payload, 'remote_id') ?? 'unknown';
        $message = Arr::get($payload, 'message', 'Import failed');
        $this->components->error("Failed to import product {$remoteId}: {$message}");
    }

    protected function displaySummary(array $summary): void
    {
        $this->components->twoColumnDetail('Created', (string) Arr::get($summary, 'created', 0));
        $this->components->twoColumnDetail('Updated', (string) Arr::get($summary, 'updated', 0));
        $this->components->twoColumnDetail('Skipped', (string) Arr::get($summary, 'skipped', 0));
        $this->components->twoColumnDetail('Failed', (string) count(Arr::get($summary, 'failed', [])));
    }
}
