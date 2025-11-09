<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\NavigationMenu;
use App\Models\ContentBlock;
use Illuminate\Http\JsonResponse;

class ConfigController extends Controller
{
    /**
     * Get public settings
     */
    public function settings(): JsonResponse
    {
        $settings = Setting::getPublic();

        return response()->json($settings);
    }

    /**
     * Get navigation by location
     */
    public function navigation(string $location): JsonResponse
    {
        $menu = NavigationMenu::byLocation($location);

        if (!$menu) {
            return response()->json([
                'message' => 'Navigation menu not found'
            ], 404);
        }

        return response()->json([
            'name' => $menu->name,
            'location' => $menu->location,
            'items' => $this->formatNavigationItems($menu->items),
        ]);
    }

    /**
     * Get content blocks for a page
     */
    public function content(string $page): JsonResponse
    {
        $blocks = ContentBlock::forPage($page);

        return response()->json($blocks);
    }

    /**
     * Format navigation items for response
     */
    private function formatNavigationItems($items)
    {
        return $items->filter(function ($item) {
            return $item->is_active;
        })->map(function ($item) {
            $formatted = [
                'label' => $item->label,
                'to' => $item->url,  // Use 'to' for Vue Router compatibility
                'icon' => $item->icon,
                'description' => $item->description,
                'is_mega' => $item->is_mega,
                'is_external' => $item->is_external,
            ];

            if ($item->is_mega && $item->megaColumns->count() > 0) {
                $formatted['columns'] = $item->megaColumns->map(function ($column) {
                    return [
                        'heading' => $column->heading,
                        'items' => $column->items->map(function ($megaItem) {
                            return [
                                'label' => $megaItem->label,
                                'to' => $megaItem->url,  // Use 'to' for Vue Router compatibility
                            ];
                        }),
                    ];
                });

                if ($item->hero) {
                    $formatted['hero'] = $item->hero;
                }
            }

            if ($item->children->count() > 0) {
                $formatted['children'] = $this->formatNavigationItems($item->children);
            }

            return $formatted;
        })->values();
    }
}
