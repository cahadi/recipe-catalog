<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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
    protected $casts = [
        'rating' => 'decimal:2',
        'is_published' => 'boolean',
        'cooking_time' => 'integer',
    ];
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($recipe) {
            $recipe->slug = Str::slug($recipe->title);
        });
        static::updating(function ($recipe) {
            if ($recipe->isDirty('title')) {
                $recipe->slug = Str::slug($recipe->title);
            }
        });
    }
    public function category() : BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function steps() : HasMany
    {
        return $this->hasMany(RecipeStep::class)->orderBy('step_number');
    }

    public function updateRating()
    {
        $this->rating = $this->ratings()->avg('rating');
        $this->rating_count = $this->ratings()->count();
        $this->saveQuietly();
    }
}
