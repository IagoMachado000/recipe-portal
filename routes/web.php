<?php

use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource('recipes', RecipeController::class)->only(['index', 'show']);
Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('recipes', [RecipeController::class, 'dashboard'])->name('recipes.dashboard');
    Route::resource('recipes', RecipeController::class)->except(['index', 'show']);
});
