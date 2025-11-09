<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentBlock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ContentBlockController extends Controller
{
    /**
     * Display a listing of content blocks
     */
    public function index(Request $request): JsonResponse
    {
        $query = ContentBlock::query();

        if ($request->has('page')) {
            $query->where('page', $request->page);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $blocks = $query->orderBy('page')->orderBy('position')->get();

        return response()->json($blocks);
    }

    /**
     * Store a newly created content block
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:content_blocks,key',
            'type' => 'required|string',
            'title' => 'nullable|string',
            'content' => 'required|array',
            'page' => 'required|string',
            'status' => 'required|in:draft,published,archived',
            'position' => 'sometimes|integer',
        ]);

        $block = ContentBlock::create($validated);

        ContentBlock::clearPageCache($block->page);

        return response()->json($block, 201);
    }

    /**
     * Display the specified content block
     */
    public function show(ContentBlock $contentBlock): JsonResponse
    {
        return response()->json($contentBlock);
    }

    /**
     * Update the specified content block
     */
    public function update(Request $request, ContentBlock $contentBlock): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'sometimes|string',
            'title' => 'nullable|string',
            'content' => 'sometimes|array',
            'page' => 'sometimes|string',
            'status' => 'sometimes|in:draft,published,archived',
            'position' => 'sometimes|integer',
        ]);

        $oldPage = $contentBlock->page;
        $contentBlock->update($validated);

        ContentBlock::clearPageCache($oldPage);
        if (isset($validated['page']) && $validated['page'] !== $oldPage) {
            ContentBlock::clearPageCache($validated['page']);
        }

        return response()->json($contentBlock);
    }

    /**
     * Remove the specified content block
     */
    public function destroy(ContentBlock $contentBlock): JsonResponse
    {
        $page = $contentBlock->page;
        $contentBlock->delete();

        ContentBlock::clearPageCache($page);

        return response()->json(['message' => 'Content block deleted successfully']);
    }

    /**
     * Reorder content blocks
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'blocks' => 'required|array',
            'blocks.*.id' => 'required|exists:content_blocks,id',
            'blocks.*.position' => 'required|integer',
        ]);

        foreach ($validated['blocks'] as $blockData) {
            ContentBlock::where('id', $blockData['id'])->update([
                'position' => $blockData['position'],
            ]);
        }

        ContentBlock::clearCache();

        return response()->json(['message' => 'Content blocks reordered successfully']);
    }
}
