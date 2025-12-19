<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'schema',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'schema' => 'array',
        ];
    }

    /**
     * Validate the schema structure.
     */
    public static function validateSchema(array $schema): bool
    {
        foreach ($schema as $field) {
            if (! isset($field['name']) || ! isset($field['type']) || ! isset($field['label'])) {
                return false;
            }

            $validTypes = ['text', 'textarea', 'number', 'boolean', 'select', 'date', 'datetime', 'image', 'file', 'rich_text'];
            if (! in_array($field['type'], $validTypes)) {
                return false;
            }
        }

        return true;
    }
}
