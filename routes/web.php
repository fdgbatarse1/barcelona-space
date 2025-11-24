<?php

use App\Http\Controllers\PlaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('places.index');
});

Route::middleware('auth')->group(function () {
    Route::resource('places', PlaceController::class)->except(['index', 'show']);

    Route::post('places/{place}/comments', [App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::put('comments/{comment}', [App\Http\Controllers\CommentController::class, 'update'])->name('comments.update');
    Route::delete('comments/{comment}', [App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('/profile', [App\Http\Controllers\Userzone\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\Userzone\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\Userzone\ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('places', PlaceController::class)->only(['index', 'show']);

require __DIR__ . '/auth.php';
