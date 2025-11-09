<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'content',
        'meta_title',
        'meta_description',
        'status',
        'template',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Get page by slug
     */
    public static function bySlug(string $slug)
    {
        return static::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();
    }

    /**
     * Scope to filter by status
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to filter system pages
     */
    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }
}
