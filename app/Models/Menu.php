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

    /**
     * The meals that belong to the menu.
     */
    public function meals()
    {
        return $this->belongsToMany(Meal::class, 'menu_meals')
                    ->withPivot('id', 'reservation_date')
                    ->withTimestamps();
    }

    /**
     * The menu meals (pivot) for this menu.
     */
    public function menuMeals()
    {
        return $this->hasMany(MenuMeal::class);
    }
}
