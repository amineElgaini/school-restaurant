<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MealType;

class MealTypeController extends Controller
{
    public function index()
    {
        return MealType::all();
    }
}