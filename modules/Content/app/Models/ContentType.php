<?php

namespace Modules\Content\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Content\Database\Factories\ContentTypeFactory;

class ContentType extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * The attributes that are mass-assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'editor',
        'is_system'
    ];

    protected static function newFactory(): ContentTypeFactory
    {
        return ContentTypeFactory::new();
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ContentField::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }
}
