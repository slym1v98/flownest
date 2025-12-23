<?php

namespace Modules\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Content\Database\Factories\ContentFieldFactory;

class ContentField extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass-assignable.
     */
    protected $fillable = [
        'content_type_id',
        'label',
        'key',
        'type',
        'options',
        'rules',
        'order',
    ];

    protected static function newFactory(): ContentFieldFactory
    {
        return ContentFieldFactory::new();
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ContentType::class);
    }
}
