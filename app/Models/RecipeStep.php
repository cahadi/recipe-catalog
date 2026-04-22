<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeStep extends Model
{
    use HasFactory;
    protected $table = 'recipe_steps';
    protected $fillable = [
        'recipe_id',
        'step_number',
        'image',
        'description',
    ];
    public function recipe() : BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }
}
