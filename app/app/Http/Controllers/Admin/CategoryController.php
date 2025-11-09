<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Requests\Admin\UploadCategoryImageRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CategoryController extends AdminController
{
    public function index(Request $request)
    {
        $request->validate([
            'tree' => ['nullable', 'in:true,false,1,0'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        if ($request->boolean('tree')) {
            $categories = Category::query()
                ->withCount('products')
                ->with(['children' => function ($query) {
                    $query->orderBy('position')->withCount('products');
                }])
                ->whereNull('parent_id')
                ->orderBy('position')
                ->get();

            return CategoryResource::collection($categories)
                ->response()
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        }

        $categories = Category::query()
            ->with('parent')
            ->withCount('products')
            ->when($request->filled('parent_id'), fn ($query) => $query->where('parent_id', $request->integer('parent_id')))
            ->orderBy('position')
            ->paginate($request->integer('per_page', 15));

        return CategoryResource::collection($categories)
            ->response()
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function store(StoreCategoryRequest $request): CategoryResource
    {
        $category = Category::create($request->validated());

        return CategoryResource::make($category->fresh(['parent', 'children']));
    }

    public function show(Category $category): CategoryResource
    {
        $category->load(['parent', 'children' => function ($query) {
            $query->orderBy('position');
        }])->loadCount('products');

        return CategoryResource::make($category);
    }

    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return CategoryResource::make($category->fresh(['parent', 'children']));
    }

    public function destroy(Category $category): Response
    {
        // Delete image if exists
        if ($category->image_url) {
            $this->deleteImageFile($category->image_url);
        }

        $category->delete();

        return response()->noContent();
    }

    public function uploadImage(UploadCategoryImageRequest $request, Category $category): JsonResponse
    {
        try {
            // Check if storage directory exists and is writable
            $storagePath = storage_path('app/public/categories/images');
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

            // Delete old image if exists
            if ($category->image_url) {
                $this->deleteImageFile($category->image_url);
            }

            // Store the new image
            $file = $request->file('image');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            
            // Use storeAs with public disk
            $path = $file->storeAs('categories/images', $filename, 'public');

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
            $imageUrl = Storage::disk('public')->url($path);

            // Update category with new image URL
            $category->update(['image_url' => $imageUrl]);

            Log::info('Category image uploaded successfully', [
                'category_id' => $category->id,
                'path' => $path,
                'url' => $imageUrl,
            ]);

            return response()->json([
                'message' => 'Image uploaded successfully',
                'image_url' => $imageUrl,
                'category' => CategoryResource::make($category->fresh()),
            ]);
        } catch (\Exception $e) {
            Log::error('Category image upload failed', [
                'category_id' => $category->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to upload image: '.$e->getMessage(),
            ], 500);
        }
    }

    public function deleteImage(Category $category): JsonResponse
    {
        if (! $category->image_url) {
            return response()->json([
                'message' => 'No image to delete',
            ], 404);
        }

        $this->deleteImageFile($category->image_url);
        $category->update(['image_url' => null]);

        return response()->json([
            'message' => 'Image deleted successfully',
            'category' => CategoryResource::make($category->fresh()),
        ]);
    }

    private function deleteImageFile(?string $imageUrl): void
    {
        if (! $imageUrl) {
            return;
        }

        try {
            // Extract path from URL
            $path = str_replace(Storage::disk('public')->url(''), '', $imageUrl);
            
            // Delete file if exists
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info('Category image deleted', ['path' => $path]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete category image file', [
                'url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
