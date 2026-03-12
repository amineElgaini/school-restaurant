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
        return $this->belongsToMany(Menu::class, 'menu_meals')
                    ->withPivot('reservation_date')
                    ->withTimestamps();
    }
}
