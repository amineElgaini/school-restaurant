<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')
            ->withTimestamps();
    }

/*************  ✨ Windsurf Command ⭐  *************/
/*******  36cbac1e-700b-4564-be2d-ce700802afe8  *******/
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permission_user')
            ->withTimestamps();
    }

    public function assignableRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'assignable_role_permissions')
            ->withTimestamps();
    }
}