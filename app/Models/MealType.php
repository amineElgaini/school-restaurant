<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MealType extends Model
{
    protected $fillable = ['name', 'slug'];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
