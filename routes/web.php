<?php

use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::prefix('my-recipes')->name('my-recipes.')->middleware('can:be-author')->group(function () {
        Route::resource('recipes', AuthorRecipeController::class);
        Route::get('dashboard', [AuthorDashboardController::class, 'index'])->name('dashboard');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
       Route::resource('categories', AdminCategoryController::class);
    });


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
