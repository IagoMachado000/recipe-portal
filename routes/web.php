<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RecipeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::redirect('/', '/recipes');
Route::resource('recipes', RecipeController::class)->only(['index', 'show']);

Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('recipes', [RecipeController::class, 'dashboard'])->name('recipes.dashboard');
    Route::resource('recipes', RecipeController::class)->except(['index', 'show']);

    Route::post('ratings', [RatingController::class, 'store'])->name('ratings.store');

    Route::post('comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::post('notifications/{notification}/read', function ($notification) {
        $notification->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.read');

    Route::post('notifications/read-all', function () {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.read-all');
});
