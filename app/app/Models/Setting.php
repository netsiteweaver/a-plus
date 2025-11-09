<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'is_public',
        'description',
    ];

    protected $casts = [
        'value' => 'array',
        'is_public' => 'boolean',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("setting:{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            // Return raw value for non-array types
            if ($setting->type !== 'json' && is_array($setting->value) && count($setting->value) === 1) {
                return $setting->value[0] ?? $default;
            }

            return $setting->value ?? $default;
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, $value, string $group = 'general', string $type = 'string', bool $isPublic = false): void
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? $value : [$value],
                'group' => $group,
                'type' => $type,
                'is_public' => $isPublic,
            ]
        );

        Cache::forget("setting:{$key}");
        Cache::forget("settings:group:{$group}");
        Cache::forget('settings:public');
    }

    /**
     * Get all settings in a group
     */
    public static function group(string $group): array
    {
        return Cache::rememberForever("settings:group:{$group}", function () use ($group) {
            return static::where('group', $group)
                ->get()
                ->mapWithKeys(function ($setting) {
                    $value = $setting->value;
                    
                    // Unwrap single-value arrays
                    if ($setting->type !== 'json' && is_array($value) && count($value) === 1) {
                        $value = $value[0] ?? null;
                    }
                    
                    return [$setting->key => $value];
                })
                ->toArray();
        });
    }

    /**
     * Get all public settings
     */
    public static function getPublic(): array
    {
        return Cache::rememberForever('settings:public', function () {
            return static::where('is_public', true)
                ->get()
                ->groupBy('group')
                ->map(function ($settings) {
                    return $settings->mapWithKeys(function ($setting) {
                        $value = $setting->value;
                        
                        // Unwrap single-value arrays
                        if ($setting->type !== 'json' && is_array($value) && count($value) === 1) {
                            $value = $value[0] ?? null;
                        }
                        
                        return [$setting->key => $value];
                    });
                })
                ->toArray();
        });
    }

    /**
     * Clear all setting caches
     */
    public static function clearCache(): void
    {
        Cache::forget('settings:public');
        
        $groups = static::distinct('group')->pluck('group');
        foreach ($groups as $group) {
            Cache::forget("settings:group:{$group}");
        }
        
        static::all()->each(function ($setting) {
            Cache::forget("setting:{$setting->key}");
        });
    }

    /**
     * Scope to filter by group
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Scope to filter public settings
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }
}
