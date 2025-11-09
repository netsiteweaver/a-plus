<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CheckStorageSetup extends Command
{
    protected $signature = 'storage:check';

    protected $description = 'Check storage configuration and permissions';

    public function handle(): int
    {
        $this->info('🔍 Checking storage setup...');
        $this->newLine();

        $allGood = true;

        // Check 1: Storage directory exists
        $storagePath = storage_path('app/public');
        if (File::isDirectory($storagePath)) {
            $this->info('✓ Storage directory exists: '.$storagePath);
        } else {
            $this->error('✗ Storage directory missing: '.$storagePath);
            $allGood = false;
        }

        // Check 2: Storage directory is writable
        if (File::isWritable($storagePath)) {
            $this->info('✓ Storage directory is writable');
        } else {
            $this->error('✗ Storage directory is NOT writable');
            $this->warn('  Fix: chmod -R 775 storage');
            $this->warn('  Or: chown -R www-data:www-data storage');
            $allGood = false;
        }

        // Check 3: Brands logos directory
        $brandsPath = storage_path('app/public/brands/logos');
        if (File::isDirectory($brandsPath)) {
            $this->info('✓ Brands logos directory exists: '.$brandsPath);
        } else {
            $this->warn('⚠ Brands logos directory missing (will be created automatically)');
            if (File::makeDirectory($brandsPath, 0755, true, true)) {
                $this->info('  Created: '.$brandsPath);
            }
        }

        // Check 4: Public storage symlink
        $linkPath = public_path('storage');
        if (File::exists($linkPath)) {
            if (is_link($linkPath)) {
                $target = readlink($linkPath);
                $this->info('✓ Storage symlink exists: '.$linkPath.' → '.$target);
            } else {
                $this->error('✗ '.$linkPath.' exists but is not a symlink');
                $this->warn('  Fix: rm -rf public/storage && php artisan storage:link');
                $allGood = false;
            }
        } else {
            $this->error('✗ Storage symlink missing: '.$linkPath);
            $this->warn('  Fix: php artisan storage:link');
            $allGood = false;
        }

        // Check 5: PHP upload settings
        $this->newLine();
        $this->info('📋 PHP Upload Configuration:');
        $uploadMaxSize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');
        $maxFileUploads = ini_get('max_file_uploads');
        
        $this->line('  upload_max_filesize: '.$uploadMaxSize);
        $this->line('  post_max_size: '.$postMaxSize);
        $this->line('  max_file_uploads: '.$maxFileUploads);

        $uploadBytes = $this->parseSize($uploadMaxSize);
        $postBytes = $this->parseSize($postMaxSize);

        if ($uploadBytes < 2097152) { // 2MB
            $this->warn('  ⚠ upload_max_filesize is less than 2MB');
        }

        if ($postBytes < 2097152) { // 2MB
            $this->warn('  ⚠ post_max_size is less than 2MB');
        }

        // Check 6: Test file write
        $this->newLine();
        $testFile = storage_path('app/public/test_write.txt');
        try {
            File::put($testFile, 'test');
            if (File::exists($testFile)) {
                $this->info('✓ Test file write successful');
                File::delete($testFile);
            } else {
                $this->error('✗ Test file write failed');
                $allGood = false;
            }
        } catch (\Exception $e) {
            $this->error('✗ Test file write failed: '.$e->getMessage());
            $allGood = false;
        }

        // Check 7: Storage disk configuration
        $this->newLine();
        $this->info('💾 Storage Disk Configuration:');
        $publicDisk = config('filesystems.disks.public');
        $this->line('  driver: '.$publicDisk['driver']);
        $this->line('  root: '.$publicDisk['root']);
        $this->line('  url: '.$publicDisk['url']);
        $this->line('  visibility: '.$publicDisk['visibility']);

        // Summary
        $this->newLine();
        if ($allGood) {
            $this->info('✅ All checks passed! Storage is properly configured.');
            return self::SUCCESS;
        } else {
            $this->error('❌ Some checks failed. Please fix the issues above.');
            return self::FAILURE;
        }
    }

    private function parseSize(string $size): int
    {
        $unit = strtolower(substr($size, -1));
        $value = (int) substr($size, 0, -1);

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => (int) $size,
        };
    }
}

