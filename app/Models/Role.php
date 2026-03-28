<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = ['name'];

    /**
     * The users that belong to the role.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
            ->withTimestamps();
    }

    public function assignablePermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'assignable_role_permissions')
            ->withTimestamps();
    }
}
