<?php

namespace App\Models;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = ['name', 'slug'];

    /**
     * The roles that belong to the permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * The users that belong to the permission.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
