<?php

namespace Modules\Authorization\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Authorization\Database\Factories\PermissionFactory;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass-assignable.
     */
    protected $fillable = [];

    protected static function newFactory(): PermissionFactory
    {
        return PermissionFactory::new();
    }
}
