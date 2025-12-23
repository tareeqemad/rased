<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/complaints-suggestions', [\App\Http\Controllers\ComplaintSuggestionController::class, 'index'])->name('complaints-suggestions.index');
Route::get('/complaints-suggestions/create', [\App\Http\Controllers\ComplaintSuggestionController::class, 'create'])->name('complaints-suggestions.create');
Route::post('/complaints-suggestions', [\App\Http\Controllers\ComplaintSuggestionController::class, 'store'])->name('complaints-suggestions.store');
Route::get('/complaints-suggestions/track', [\App\Http\Controllers\ComplaintSuggestionController::class, 'track'])->name('complaints-suggestions.track');
Route::post('/complaints-suggestions/search', [\App\Http\Controllers\ComplaintSuggestionController::class, 'search'])->name('complaints-suggestions.search');
Route::get('/complaints-suggestions/generators', [\App\Http\Controllers\ComplaintSuggestionController::class, 'getGeneratorsByLocation'])->name('complaints-suggestions.generators');
