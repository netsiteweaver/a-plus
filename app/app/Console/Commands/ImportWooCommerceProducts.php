<?php

namespace App\Console\Commands;

use App\Services\WooCommerce\WooCommerceImporter;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportWooCommerceProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalog:import-woocommerce
        {--per-page= : Number of products to request per page (max 100)}
        {--since= : Only import products modified after this ISO-8601 datetime}
        {--product=* : Specific WooCommerce product IDs to import}
        {--skip-categories : Skip synchronising categories before importing products}
        {--dry-run : Run the import inside a transaction and roll back all changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from a WooCommerce store into the local catalog.';

    public function handle(WooCommerceImporter $importer): int
    {
        if (! config('services.woocommerce.base_url')) {
            $this->error('WooCommerce base URL is not configured (WOOCOMMERCE_BASE_URL).');

            return self::FAILURE;
        }

        if (! config('services.woocommerce.consumer_key') || ! config('services.woocommerce.consumer_secret')) {
            $this->error('WooCommerce consumer key/secret are not configured (WOOCOMMERCE_CONSUMER_KEY / WOOCOMMERCE_CONSUMER_SECRET).');

            return self::FAILURE;
        }

        $perPage = $this->option('per-page');
        if ($perPage !== null) {
            $perPage = (int) $perPage;

            if ($perPage < 1 || $perPage > 100) {
                $this->error('The --per-page option must be between 1 and 100.');

                return self::FAILURE;
            }
        }

        $since = null;
        if ($value = $this->option('since')) {
            try {
                $since = Carbon::parse($value);
            } catch (Throwable) {
                $this->error('The --since option must be a valid datetime string (ISO-8601 recommended).');

                return self::FAILURE;
            }
        }

        $productIds = array_unique(array_filter(
            array_map('intval', (array) $this->option('product')),
            fn ($id) => $id > 0
        ));

        $importOptions = [
            'per_page' => $perPage,
            'since' => $since,
            'remote_ids' => $productIds,
            'sync_categories' => ! $this->option('skip-categories'),
        ];

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Running in dry-run mode. All database changes will be rolled back.');
            DB::beginTransaction();
        }

        try {
            $stats = $importer->import($importOptions, $this->output);

            if ($dryRun) {
                DB::rollBack();
                $this->warn('Dry run completed. No changes were persisted.');
            }
        } catch (Throwable $exception) {
            if ($dryRun && DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            $this->error('Import failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Import completed successfully.');

        $rows = [
            $this->formatSummaryRow('Categories', $stats['categories'] ?? [], 'skipped'),
            $this->formatSummaryRow('Products', $stats['products'] ?? [], 'skipped'),
            $this->formatSummaryRow('Variants', $stats['variants'] ?? [], 'deleted'),
            $this->formatSummaryRow('Media', $stats['media'] ?? [], 'deleted'),
        ];

        $this->table(
            ['Entity', 'Created', 'Updated', 'Deleted/Skipped'],
            $rows
        );

        return self::SUCCESS;
    }

    /**
     * @param  array<string, int>  $stats
     * @return array<int, string|int>
     */
    protected function formatSummaryRow(string $entity, array $stats, string $fallbackKey): array
    {
        return [
            $entity,
            Arr::get($stats, 'created', 0),
            Arr::get($stats, 'updated', 0),
            Arr::get($stats, $fallbackKey, 0),
        ];
    }
}
