<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavigationMenu;
use App\Models\NavigationItem;
use App\Models\NavigationMegaColumn;
use App\Models\NavigationMegaItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NavigationItemController extends Controller
{
    /**
     * Get all navigation menus with items
     */
    public function index(): JsonResponse
    {
        $menus = NavigationMenu::with([
            'allItems.children',
            'allItems.megaColumns.items'
        ])->get();

        return response()->json($menus);
    }

    /**
     * Get a specific menu with all items
     */
    public function show(string $location): JsonResponse
    {
        $menu = NavigationMenu::with([
            'allItems.children',
            'allItems.megaColumns.items'
        ])->where('location', $location)->first();

        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }

        return response()->json($menu);
    }

    /**
     * Store a new navigation item
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:navigation_menus,id',
            'parent_id' => 'nullable|exists:navigation_items,id',
            'label' => 'required|string',
            'url' => 'nullable|string',
            'icon' => 'nullable|string',
            'description' => 'nullable|string',
            'is_mega' => 'boolean',
            'is_external' => 'boolean',
            'is_active' => 'boolean',
            'position' => 'integer',
            'hero' => 'nullable|array',
            'mega_columns' => 'nullable|array',
            'mega_columns.*.heading' => 'required|string',
            'mega_columns.*.items' => 'required|array',
            'mega_columns.*.items.*.label' => 'required|string',
            'mega_columns.*.items.*.url' => 'required|string',
        ]);

        $item = NavigationItem::create([
            'menu_id' => $validated['menu_id'],
            'parent_id' => $validated['parent_id'] ?? null,
            'label' => $validated['label'],
            'url' => $validated['url'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_mega' => $validated['is_mega'] ?? false,
            'is_external' => $validated['is_external'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'position' => $validated['position'] ?? 0,
            'hero' => $validated['hero'] ?? null,
        ]);

        // Create mega menu columns if provided
        if (isset($validated['mega_columns']) && $validated['is_mega']) {
            foreach ($validated['mega_columns'] as $index => $columnData) {
                $column = NavigationMegaColumn::create([
                    'navigation_item_id' => $item->id,
                    'heading' => $columnData['heading'],
                    'position' => $index,
                ]);

                foreach ($columnData['items'] as $itemIndex => $megaItemData) {
                    NavigationMegaItem::create([
                        'mega_column_id' => $column->id,
                        'label' => $megaItemData['label'],
                        'url' => $megaItemData['url'],
                        'position' => $itemIndex,
                    ]);
                }
            }
        }

        NavigationMenu::clearCache();

        return response()->json($item->load('megaColumns.items'), 201);
    }

    /**
     * Update a navigation item
     */
    public function update(Request $request, NavigationItem $navigationItem): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'sometimes|string',
            'url' => 'nullable|string',
            'icon' => 'nullable|string',
            'description' => 'nullable|string',
            'is_mega' => 'sometimes|boolean',
            'is_external' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'position' => 'sometimes|integer',
            'hero' => 'nullable|array',
        ]);

        $navigationItem->update($validated);

        NavigationMenu::clearCache();

        return response()->json($navigationItem);
    }

    /**
     * Delete a navigation item
     */
    public function destroy(NavigationItem $navigationItem): JsonResponse
    {
        $navigationItem->delete();

        NavigationMenu::clearCache();

        return response()->json(['message' => 'Navigation item deleted successfully']);
    }

    /**
     * Reorder navigation items
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:navigation_items,id',
            'items.*.position' => 'required|integer',
        ]);

        foreach ($validated['items'] as $itemData) {
            NavigationItem::where('id', $itemData['id'])->update([
                'position' => $itemData['position'],
            ]);
        }

        NavigationMenu::clearCache();

        return response()->json(['message' => 'Navigation items reordered successfully']);
    }
}
