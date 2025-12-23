<?php

namespace Modules\Content\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Content\Database\Factories\ContentFactory;

class Content extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass-assignable.
     */
    protected $fillable = [
        'content_type_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'is_featured',
        'attributes'
    ];

    protected $casts = [
        'attributes' => AsArrayObject::class,
    ];

    protected static function newFactory(): ContentFactory
    {
        return ContentFactory::new();
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ContentType::class);
    }
}
