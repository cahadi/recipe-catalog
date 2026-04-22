<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Category;
use App\Models\RecipeStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AuthorRecipeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:be-author')->except(['index', 'show']);
    }

    public function index()
    {
        $recipes = Auth::user()->recipes()->with('category')->paginate(10);
        return view('author.recipes.index', compact('recipes'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('author.recipes.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'category_id' => 'required|exists:categories,id',
            'cooking_time' => 'required|integer|min:1',
            'difficulty' => 'required|in:easy,medium,hard',
            'steps' => 'required|array|min:1',
            'steps.*.description' => 'required|string',
            'steps.*.image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('recipes', 'public');
        }

        $recipe = Recipe::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title'], '-'),
            'description' => $validated['description'],
            'image' => $imagePath,
            'category_id' => $validated['category_id'],
            'author_id' => Auth::id(),
            'cooking_time' => $validated['cooking_time'],
            'difficulty' => $validated['difficulty'],
            'is_published' => $request->has('is_published'),
        ]);

        foreach ($validated['steps'] as $index => $stepData) {
            $stepImage = null;
            if (isset($stepData['image']) && $stepData['image'] instanceof \Illuminate\Http\UploadedFile) {
                $stepImage = $stepData['image']->store('recipe_steps', 'public');
            }
            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'step_number' => $index + 1,
                'image' => $stepImage,
                'description' => $stepData['description'],
            ]);
        }

        return redirect()->route('my-recipes.recipes.index')
            ->with('success', 'Рецепт успешно создан!');
    }

    public function show(Recipe $recipe)
    {
        if (!$recipe->is_published && Gate::denies('view-unpublished', $recipe)) {
            abort(403);
        }
        return view('author.recipes.show', compact('recipe'));
    }

    public function edit(Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        $categories = Category::all();
        return view('author.recipes.edit', compact('recipe', 'categories'));
    }

    public function update(Request $request, Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'category_id' => 'required|exists:categories,id',
            'cooking_time' => 'required|integer|min:1',
            'difficulty' => 'required|in:easy,medium,hard',
            'steps' => 'array',
            'steps.*.id' => 'nullable|exists:recipe_steps,id',
            'steps.*.description' => 'required|string',
            'steps.*.image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($recipe->image) {
                Storage::disk('public')->delete($recipe->image);
            }
            $recipe->image = $request->file('image')->store('recipes', 'public');
        }

        $recipe->update([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title'], '-'),
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'cooking_time' => $validated['cooking_time'],
            'difficulty' => $validated['difficulty'],
            'is_published' => $request->has('is_published'),
        ]);

        if (isset($validated['steps'])) {
            $existingStepIds = $recipe->steps->pluck('id')->toArray();
            $newStepIds = [];

            foreach ($validated['steps'] as $index => $stepData) {
                $stepImage = null;
                if (isset($stepData['image']) && $stepData['image'] instanceof \Illuminate\Http\UploadedFile) {
                    $stepImage = $stepData['image']->store('recipe_steps', 'public');
                    if (isset($stepData['id']) && $step = RecipeStep::find($stepData['id'])) {
                        if ($step->image) Storage::disk('public')->delete($step->image);
                    }
                }

                if (!empty($stepData['id']) && in_array($stepData['id'], $existingStepIds)) {
                    $step = RecipeStep::find($stepData['id']);
                    $step->update([
                        'step_number' => $index + 1,
                        'description' => $stepData['description'],
                        'image' => $stepImage ?? $step->image,
                    ]);
                    $newStepIds[] = $step->id;
                } else {
                    $step = RecipeStep::create([
                        'recipe_id' => $recipe->id,
                        'step_number' => $index + 1,
                        'description' => $stepData['description'],
                        'image' => $stepImage,
                    ]);
                    $newStepIds[] = $step->id;
                }
            }

            $toDelete = array_diff($existingStepIds, $newStepIds);
            RecipeStep::whereIn('id', $toDelete)->get()->each(function ($step) {
                if ($step->image) Storage::disk('public')->delete($step->image);
                $step->delete();
            });
        }

        return redirect()->route('my-recipes.recipes.index')
            ->with('success', 'Рецепт обновлён!');
    }

    public function destroy(Recipe $recipe)
    {
        $this->authorize('delete', $recipe);

        if ($recipe->image) {
            Storage::disk('public')->delete($recipe->image);
        }
        foreach ($recipe->steps as $step) {
            if ($step->image) Storage::disk('public')->delete($step->image);
        }
        $recipe->delete();

        return redirect()->route('my-recipes.recipes.index')
            ->with('success', 'Рецепт удалён.');
    }
}
