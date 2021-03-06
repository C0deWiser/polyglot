<?php

use Codewiser\Polyglot\Http\Controllers\HomeController;
use Codewiser\Polyglot\Http\Controllers\i18nController;
use Codewiser\Polyglot\Http\Controllers\L10nController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::get('i18n', [i18nController::class, 'index']);
    Route::post('i18n/collect', [i18nController::class, 'collect']);
    Route::post('i18n/compile', [i18nController::class, 'compile']);

    Route::get('L10n/{path?}', [L10nController::class, 'get'])
        ->where('path', '(.*)');

    Route::post('L10n/{path?}', [L10nController::class, 'post'])
        ->where('path', '(.*)');
});

// Catch-all Route...
Route::get('/{view?}', [HomeController::class, 'index'])
    ->where('view', '(.*)')
    ->name('polyglot');
