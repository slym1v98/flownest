<?php

namespace Modules\Authorization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Authorization\Database\Factories\RoleFactory;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }
}
