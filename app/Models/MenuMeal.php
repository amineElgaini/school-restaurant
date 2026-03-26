<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuMeal extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $table = 'menu_meals';

    protected $fillable = [
        'meal_id',
        'reservation_date',
    ];



    public function meal()
    {
        return $this->belongsTo(Meal::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
