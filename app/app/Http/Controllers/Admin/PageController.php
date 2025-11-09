<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    /**
     * Display a listing of pages
     */
    public function index(Request $request): JsonResponse
    {
        $query = Page::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('is_system')) {
            $query->where('is_system', $request->boolean('is_system'));
        }

        $pages = $query->orderBy('title')->get();

        return response()->json($pages);
    }

    /**
     * Store a newly created page
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'slug' => 'required|string|unique:pages,slug',
            'title' => 'required|string',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'status' => 'required|in:draft,published,archived',
            'template' => 'required|in:default,full-width,custom',
            'is_system' => 'boolean',
        ]);

        $page = Page::create($validated);

        return response()->json($page, 201);
    }

    /**
     * Display the specified page
     */
    public function show(Page $page): JsonResponse
    {
        return response()->json($page);
    }

    /**
     * Update the specified page
     */
    public function update(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'slug' => ['sometimes', 'string', Rule::unique('pages', 'slug')->ignore($page->id)],
            'title' => 'sometimes|string',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'status' => 'sometimes|in:draft,published,archived',
            'template' => 'sometimes|in:default,full-width,custom',
        ]);

        $page->update($validated);

        return response()->json($page);
    }

    /**
     * Remove the specified page
     */
    public function destroy(Page $page): JsonResponse
    {
        // Prevent deletion of system pages
        if ($page->is_system) {
            return response()->json([
                'message' => 'System pages cannot be deleted'
            ], 403);
        }

        $page->delete();

        return response()->json(['message' => 'Page deleted successfully']);
    }
}
