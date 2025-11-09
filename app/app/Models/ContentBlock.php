<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ContentBlock extends Model
{
    protected $fillable = [
        'key',
        'type',
        'title',
        'content',
        'page',
        'status',
        'position',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    /**
     * Get blocks for a specific page
     */
    public static function forPage(string $page)
    {
        return Cache::remember("content:page:{$page}", 3600, function () use ($page) {
            return static::where('page', $page)
                ->where('status', 'published')
                ->orderBy('position')
                ->get();
        });
    }

    /**
     * Clear content cache for a page
     */
    public static function clearPageCache(string $page): void
    {
        Cache::forget("content:page:{$page}");
    }

    /**
     * Clear all content caches
     */
    public static function clearCache(): void
    {
        $pages = static::distinct('page')->pluck('page');
        foreach ($pages as $page) {
            Cache::forget("content:page:{$page}");
        }
    }

    /**
     * Scope to filter by page
     */
    public function scopePage($query, string $page)
    {
        return $query->where('page', $page);
    }

    /**
     * Scope to filter by type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter published blocks
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
