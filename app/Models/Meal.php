<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'image',
    ];

    /**
     * The menus that contain the meal.
     */
    public function menus()
    {
        // I need it if they wanna remove a meal that's already in a menu
        return $this->belongsToMany(Menu::class, 'menu_meals')
                    ->withPivot('reservation_date')
                    ->withTimestamps();
    }
}
