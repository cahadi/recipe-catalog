<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use HasFactory;
    protected $table = 'recipes';
    protected $fillable = [
        'name',
        'description',
        'image',
        'category_id',
        'user_id',
        'cooking_time',
        'difficulty',
        'rating',
        'rating_count',
        'is_published',
    ];
}
