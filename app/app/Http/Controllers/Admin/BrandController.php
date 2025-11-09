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
        // Delete old logo if exists
        if ($brand->logo_url) {
            $this->deleteLogoFile($brand->logo_url);
        }

        // Store the new logo
        $file = $request->file('logo');
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('brands/logos', $filename, 'public');

        // Generate full URL
        $logoUrl = Storage::disk('public')->url($path);

        // Update brand with new logo URL
        $brand->update(['logo_url' => $logoUrl]);

        return response()->json([
            'message' => 'Logo uploaded successfully',
            'logo_url' => $logoUrl,
            'brand' => BrandResource::make($brand->fresh()),
        ]);
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

        // Extract path from URL
        $path = str_replace(Storage::disk('public')->url(''), '', $logoUrl);
        
        // Delete file if exists
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
