<?php

use App\Http\Controllers\Api\CatalogHomeController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Admin\AttributeController as AdminAttributeController;
use App\Http\Controllers\Admin\AttributeValueController as AdminAttributeValueController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductOptionController as AdminProductOptionController;
use App\Http\Controllers\Admin\ProductOptionValueController as AdminProductOptionValueController;
use App\Http\Controllers\Admin\ProductMediaController as AdminProductMediaController;
use App\Http\Controllers\Admin\ProductVariantController as AdminProductVariantController;
use App\Http\Controllers\Admin\ProductAttributeValueController as AdminProductAttributeValueController;
use App\Http\Controllers\Admin\RelatedProductController as AdminRelatedProductController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return new UserResource($request->user());
});

Route::prefix('catalog')->group(function () {
    Route::get('/home', CatalogHomeController::class);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
});

require __DIR__.'/auth.php';

Route::prefix('admin')
    ->middleware(['auth:sanctum'])
    ->name('admin.')
    ->group(function () {
        Route::get('brands', [AdminBrandController::class, 'index'])
            ->name('brands.index')
            ->middleware('permission:catalog.view');

        Route::post('brands', [AdminBrandController::class, 'store'])
            ->name('brands.store')
            ->middleware('permission:catalog.manage');

        Route::get('brands/{brand}', [AdminBrandController::class, 'show'])
            ->name('brands.show')
            ->middleware('permission:catalog.view');

        Route::put('brands/{brand}', [AdminBrandController::class, 'update'])
            ->name('brands.update')
            ->middleware('permission:catalog.manage');

        Route::delete('brands/{brand}', [AdminBrandController::class, 'destroy'])
            ->name('brands.destroy')
            ->middleware('permission:catalog.manage');

        Route::get('categories', [AdminCategoryController::class, 'index'])
            ->name('categories.index')
            ->middleware('permission:catalog.view');

        Route::post('categories', [AdminCategoryController::class, 'store'])
            ->name('categories.store')
            ->middleware('permission:catalog.manage');

        Route::get('categories/{category}', [AdminCategoryController::class, 'show'])
            ->name('categories.show')
            ->middleware('permission:catalog.view');

        Route::put('categories/{category}', [AdminCategoryController::class, 'update'])
            ->name('categories.update')
            ->middleware('permission:catalog.manage');

        Route::delete('categories/{category}', [AdminCategoryController::class, 'destroy'])
            ->name('categories.destroy')
            ->middleware('permission:catalog.manage');

        Route::get('products', [AdminProductController::class, 'index'])
            ->name('products.index')
            ->middleware('permission:catalog.view');

        Route::post('products', [AdminProductController::class, 'store'])
            ->name('products.store')
            ->middleware('permission:catalog.manage');

        Route::get('products/{product}', [AdminProductController::class, 'show'])
            ->name('products.show')
            ->middleware('permission:catalog.view');

        Route::put('products/{product}', [AdminProductController::class, 'update'])
            ->name('products.update')
            ->middleware('permission:catalog.manage');

        Route::delete('products/{product}', [AdminProductController::class, 'destroy'])
            ->name('products.destroy')
            ->middleware('permission:catalog.manage');

        Route::get('products/{product}/variants', [AdminProductVariantController::class, 'index'])
            ->name('products.variants.index')
            ->middleware('permission:catalog.view');

        Route::post('products/{product}/variants', [AdminProductVariantController::class, 'store'])
            ->name('products.variants.store')
            ->middleware('permission:catalog.manage');

        Route::get('products/{product}/variants/{variant}', [AdminProductVariantController::class, 'show'])
            ->name('products.variants.show')
            ->middleware('permission:catalog.view');

        Route::put('products/{product}/variants/{variant}', [AdminProductVariantController::class, 'update'])
            ->name('products.variants.update')
            ->middleware('permission:catalog.manage');

        Route::delete('products/{product}/variants/{variant}', [AdminProductVariantController::class, 'destroy'])
            ->name('products.variants.destroy')
            ->middleware('permission:catalog.manage');

        Route::get('products/{product}/options', [AdminProductOptionController::class, 'index'])
            ->name('products.options.index')
            ->middleware('permission:catalog.view');

        Route::post('products/{product}/options', [AdminProductOptionController::class, 'store'])
            ->name('products.options.store')
            ->middleware('permission:catalog.manage');

        Route::get('products/{product}/options/{option}', [AdminProductOptionController::class, 'show'])
            ->name('products.options.show')
            ->middleware('permission:catalog.view');

        Route::put('products/{product}/options/{option}', [AdminProductOptionController::class, 'update'])
            ->name('products.options.update')
            ->middleware('permission:catalog.manage');

        Route::delete('products/{product}/options/{option}', [AdminProductOptionController::class, 'destroy'])
            ->name('products.options.destroy')
            ->middleware('permission:catalog.manage');

        Route::get('products/{product}/options/{option}/values', [AdminProductOptionValueController::class, 'index'])
            ->name('products.options.values.index')
            ->middleware('permission:catalog.view');

        Route::post('products/{product}/options/{option}/values', [AdminProductOptionValueController::class, 'store'])
            ->name('products.options.values.store')
            ->middleware('permission:catalog.manage');

        Route::get('products/{product}/options/{option}/values/{value}', [AdminProductOptionValueController::class, 'show'])
            ->name('products.options.values.show')
            ->middleware('permission:catalog.view');

        Route::put('products/{product}/options/{option}/values/{value}', [AdminProductOptionValueController::class, 'update'])
            ->name('products.options.values.update')
            ->middleware('permission:catalog.manage');

        Route::delete('products/{product}/options/{option}/values/{value}', [AdminProductOptionValueController::class, 'destroy'])
            ->name('products.options.values.destroy')
            ->middleware('permission:catalog.manage');

        Route::get('products/{product}/media', [AdminProductMediaController::class, 'index'])
            ->name('products.media.index')
            ->middleware('permission:catalog.view');

        Route::post('products/{product}/media/upload', [AdminProductMediaController::class, 'upload'])
            ->name('products.media.upload')
            ->middleware('permission:catalog.manage');

        Route::post('products/{product}/media', [AdminProductMediaController::class, 'store'])
            ->name('products.media.store')
            ->middleware('permission:catalog.manage');

        Route::get('products/{product}/media/{medium}', [AdminProductMediaController::class, 'show'])
            ->name('products.media.show')
            ->middleware('permission:catalog.view');

        Route::put('products/{product}/media/{medium}', [AdminProductMediaController::class, 'update'])
            ->name('products.media.update')
            ->middleware('permission:catalog.manage');

        Route::delete('products/{product}/media/{medium}', [AdminProductMediaController::class, 'destroy'])
            ->name('products.media.destroy')
            ->middleware('permission:catalog.manage');

        Route::get('products/{product}/related', [AdminRelatedProductController::class, 'index'])
            ->name('products.related.index')
            ->middleware('permission:catalog.view');

        Route::post('products/{product}/related', [AdminRelatedProductController::class, 'store'])
            ->name('products.related.store')
            ->middleware('permission:catalog.manage');

        Route::get('products/{product}/related/{relatedProduct}', [AdminRelatedProductController::class, 'show'])
            ->name('products.related.show')
            ->middleware('permission:catalog.view');

        Route::put('products/{product}/related/{relatedProduct}', [AdminRelatedProductController::class, 'update'])
            ->name('products.related.update')
            ->middleware('permission:catalog.manage');

        Route::delete('products/{product}/related/{relatedProduct}', [AdminRelatedProductController::class, 'destroy'])
            ->name('products.related.destroy')
            ->middleware('permission:catalog.manage');

        Route::get('products/{product}/attributes', [AdminProductAttributeValueController::class, 'index'])
            ->name('products.attributes.index')
            ->middleware('permission:catalog.view');

        Route::post('products/{product}/attributes', [AdminProductAttributeValueController::class, 'store'])
            ->name('products.attributes.store')
            ->middleware('permission:catalog.manage');

        Route::get('products/{product}/attributes/{attributeValue}', [AdminProductAttributeValueController::class, 'show'])
            ->name('products.attributes.show')
            ->middleware('permission:catalog.view');

        Route::put('products/{product}/attributes/{attributeValue}', [AdminProductAttributeValueController::class, 'update'])
            ->name('products.attributes.update')
            ->middleware('permission:catalog.manage');

        Route::delete('products/{product}/attributes/{attributeValue}', [AdminProductAttributeValueController::class, 'destroy'])
            ->name('products.attributes.destroy')
            ->middleware('permission:catalog.manage');

        Route::get('attributes', [AdminAttributeController::class, 'index'])
            ->name('attributes.index')
            ->middleware('permission:catalog.view');

        Route::post('attributes', [AdminAttributeController::class, 'store'])
            ->name('attributes.store')
            ->middleware('permission:catalog.manage');

        Route::get('attributes/{attribute}', [AdminAttributeController::class, 'show'])
            ->name('attributes.show')
            ->middleware('permission:catalog.view');

        Route::put('attributes/{attribute}', [AdminAttributeController::class, 'update'])
            ->name('attributes.update')
            ->middleware('permission:catalog.manage');

        Route::delete('attributes/{attribute}', [AdminAttributeController::class, 'destroy'])
            ->name('attributes.destroy')
            ->middleware('permission:catalog.manage');

        Route::get('attributes/{attribute}/values', [AdminAttributeValueController::class, 'index'])
            ->name('attributes.values.index')
            ->middleware('permission:catalog.view');

        Route::post('attributes/{attribute}/values', [AdminAttributeValueController::class, 'store'])
            ->name('attributes.values.store')
            ->middleware('permission:catalog.manage');

        Route::get('attributes/{attribute}/values/{value}', [AdminAttributeValueController::class, 'show'])
            ->name('attributes.values.show')
            ->middleware('permission:catalog.view');

        Route::put('attributes/{attribute}/values/{value}', [AdminAttributeValueController::class, 'update'])
            ->name('attributes.values.update')
            ->middleware('permission:catalog.manage');

        Route::delete('attributes/{attribute}/values/{value}', [AdminAttributeValueController::class, 'destroy'])
            ->name('attributes.values.destroy')
            ->middleware('permission:catalog.manage');
    });
