<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
      protected $hidden = [
        'created_at',
        'updated_at',
    ];
    protected $fillable = [
        'user_id',
        'menu_meal_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menuMeal(): BelongsTo
    {
        return $this->belongsTo(MenuMeal::class);
    }
}