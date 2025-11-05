<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreBrandRequest;
use App\Http\Requests\Admin\UpdateBrandRequest;
use App\Http\Resources\Admin\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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
        $brand->delete();

        return response()->noContent();
    }
}
