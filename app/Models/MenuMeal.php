<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuMeal extends Model
{
    use HasFactory;

    protected $table = 'menu_meals';

    protected $fillable = [
        'menu_id',
        'meal_id',
        'reservation_date',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
