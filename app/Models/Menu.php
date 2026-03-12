<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'week_start_date',
        'week_end_date',
    ];

    public function meals()
    {
        return $this->belongsToMany(Meal::class, 'menu_meals')
                    ->withPivot('id', 'reservation_date')
                    ->withTimestamps();
    }

    public function menuMeals()
    {
        return $this->hasMany(MenuMeal::class);
    }
}
