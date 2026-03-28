<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MealType extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class);
    }
}