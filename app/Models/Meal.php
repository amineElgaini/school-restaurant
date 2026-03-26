<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;
    
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

    public function type()
    {
        return $this->belongsTo(MealType::class, 'meal_type_id');
    }

    /**
     * The reservations for this meal via menu_meals.
     */
    public function menuMeals()
    {
        return $this->hasMany(MenuMeal::class);
    }
}
