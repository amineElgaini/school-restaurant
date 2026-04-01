<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meal extends Model
{
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    protected $fillable = [
        'name',
        'meal_type_id',
        'description',
        'image',
    ];

    public function mealType(): BelongsTo
    {
        return $this->belongsTo(MealType::class);
    }

    public function menuMeals(): HasMany
    {
        return $this->hasMany(MenuMeal::class);
    }
}
