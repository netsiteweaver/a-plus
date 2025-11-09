<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    /**
     * Display a listing of all settings grouped
     */
    public function index(): JsonResponse
    {
        $settings = Setting::all()->groupBy('group');

        return response()->json($settings);
    }

    /**
     * Store a newly created setting
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:settings,key',
            'value' => 'required',
            'group' => 'required|string',
            'type' => 'required|string|in:string,number,boolean,json,text',
            'is_public' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $value = $validated['value'];
        if (!is_array($value)) {
            $value = [$value];
        }

        $setting = Setting::create([
            'key' => $validated['key'],
            'value' => $value,
            'group' => $validated['group'],
            'type' => $validated['type'],
            'is_public' => $validated['is_public'] ?? false,
            'description' => $validated['description'] ?? null,
        ]);

        Setting::clearCache();

        return response()->json($setting, 201);
    }

    /**
     * Display the specified setting
     */
    public function show(Setting $setting): JsonResponse
    {
        return response()->json($setting);
    }

    /**
     * Update the specified setting
     */
    public function update(Request $request, Setting $setting): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required',
            'group' => 'sometimes|string',
            'type' => 'sometimes|string|in:string,number,boolean,json,text',
            'is_public' => 'sometimes|boolean',
            'description' => 'nullable|string',
        ]);

        $value = $validated['value'];
        if (!is_array($value)) {
            $value = [$value];
        }

        $setting->update([
            'value' => $value,
            'group' => $validated['group'] ?? $setting->group,
            'type' => $validated['type'] ?? $setting->type,
            'is_public' => $validated['is_public'] ?? $setting->is_public,
            'description' => $validated['description'] ?? $setting->description,
        ]);

        Setting::clearCache();

        return response()->json($setting);
    }

    /**
     * Remove the specified setting
     */
    public function destroy(Setting $setting): JsonResponse
    {
        $setting->delete();
        
        Setting::clearCache();

        return response()->json(['message' => 'Setting deleted successfully']);
    }

    /**
     * Bulk update settings
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        foreach ($validated['settings'] as $settingData) {
            $value = $settingData['value'];
            if (!is_array($value)) {
                $value = [$value];
            }

            Setting::where('key', $settingData['key'])->update([
                'value' => $value,
            ]);
        }

        Setting::clearCache();

        return response()->json(['message' => 'Settings updated successfully']);
    }

    /**
     * Get settings by group
     */
    public function byGroup(string $group): JsonResponse
    {
        $settings = Setting::where('group', $group)->get();

        return response()->json($settings);
    }
}
