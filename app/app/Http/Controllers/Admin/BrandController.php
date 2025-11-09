<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Http\Requests\Admin\UploadBrandLogoRequest;
use App\Http\Resources\Admin\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BrandController extends AdminController
{
    public function index(): AnonymousResourceCollection
    {
        $brands = Brand::query()
            ->withCount('products')
            ->orderByDesc('updated_at')
            ->paginate(request('per_page', 15));

        return BrandResource::collection($brands);
    }

    public function store(StoreBrandRequest $request): BrandResource
    {
        $brand = Brand::create($request->validated());

        return BrandResource::make($brand->fresh(['products']));
    }

    public function show(Brand $brand): BrandResource
    {
        $brand->loadCount('products');

        return BrandResource::make($brand);
    }

    public function update(UpdateBrandRequest $request, Brand $brand): BrandResource
    {
        $brand->update($request->validated());

        return BrandResource::make($brand->fresh(['products']));
    }

    public function destroy(Brand $brand): Response
    {
        // Delete logo if exists
        if ($brand->logo_url) {
            $this->deleteLogoFile($brand->logo_url);
        }

        $brand->delete();

        return response()->noContent();
    }

    public function uploadLogo(UploadBrandLogoRequest $request, Brand $brand): JsonResponse
    {
        try {
            // Check if storage directory exists and is writable
            $storagePath = storage_path('app/public/brands/logos');
            if (! file_exists($storagePath)) {
                if (! mkdir($storagePath, 0755, true)) {
                    Log::error('Failed to create storage directory', ['path' => $storagePath]);
                    return response()->json([
                        'message' => 'Storage directory could not be created. Please check server permissions.',
                    ], 500);
                }
            }

            if (! is_writable($storagePath)) {
                Log::error('Storage directory is not writable', ['path' => $storagePath]);
                return response()->json([
                    'message' => 'Storage directory is not writable. Please check server permissions.',
                ], 500);
            }

            // Delete old logo if exists
            if ($brand->logo_url) {
                $this->deleteLogoFile($brand->logo_url);
            }

            // Store the new logo
            $file = $request->file('logo');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            
            // Use storeAs with public disk
            $path = $file->storeAs('brands/logos', $filename, 'public');

            if (! $path) {
                Log::error('Failed to store file', [
                    'filename' => $filename,
                    'disk' => 'public',
                ]);
                return response()->json([
                    'message' => 'Failed to store the file. Please try again.',
                ], 500);
            }

            // Generate full URL
            $logoUrl = Storage::disk('public')->url($path);

            // Update brand with new logo URL
            $brand->update(['logo_url' => $logoUrl]);

            Log::info('Logo uploaded successfully', [
                'brand_id' => $brand->id,
                'path' => $path,
                'url' => $logoUrl,
            ]);

            return response()->json([
                'message' => 'Logo uploaded successfully',
                'logo_url' => $logoUrl,
                'brand' => BrandResource::make($brand->fresh()),
            ]);
        } catch (\Exception $e) {
            Log::error('Logo upload failed', [
                'brand_id' => $brand->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to upload logo: '.$e->getMessage(),
            ], 500);
        }
    }

    public function deleteLogo(Brand $brand): JsonResponse
    {
        if (! $brand->logo_url) {
            return response()->json([
                'message' => 'No logo to delete',
            ], 404);
        }

        $this->deleteLogoFile($brand->logo_url);
        $brand->update(['logo_url' => null]);

        return response()->json([
            'message' => 'Logo deleted successfully',
            'brand' => BrandResource::make($brand->fresh()),
        ]);
    }

    private function deleteLogoFile(?string $logoUrl): void
    {
        if (! $logoUrl) {
            return;
        }

        try {
            // Extract path from URL
            $path = str_replace(Storage::disk('public')->url(''), '', $logoUrl);
            
            // Delete file if exists
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info('Logo deleted', ['path' => $path]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete logo file', [
                'url' => $logoUrl,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
