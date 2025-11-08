<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

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
        $category->delete();

        return response()->noContent();
    }
}
