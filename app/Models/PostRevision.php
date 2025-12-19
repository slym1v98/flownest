<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostRevision extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'is_featured',
        'seo_data',
        'reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'title' => 'array',
            'excerpt' => 'array',
            'content' => 'array',
            'seo_data' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Get the post that owns the revision.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who created the revision.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
