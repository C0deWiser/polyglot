<?php

use Codewiser\Polyglot\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::get('i18n', function () {
        return true;
    });
});

// Catch-all Route...
Route::get('/{view?}', [HomeController::class, 'index'])
    ->where('view', '(.*)')
    ->name('polyglot');
