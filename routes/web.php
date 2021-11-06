<?php

use Codewiser\Polyglot\Http\Controllers\HomeController;
use Codewiser\Polyglot\Http\Controllers\i18nController;
use Codewiser\Polyglot\Http\Controllers\L10nController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::get('i18n', [i18nController::class, 'index']);
    Route::post('i18n/collect', [i18nController::class, 'collect']);
    Route::post('i18n/compile', [i18nController::class, 'compile']);

    // Get translations catalog
    Route::get('L10n', [L10nController::class, 'index']);

    // Get and post json values
    Route::get('L10n/{json}', [L10nController::class, 'getJson']);
    Route::post('L10n/{json}', [L10nController::class, 'postJson']);

    // Get and post php array values
    Route::get('L10n/{locale}/{filename}', [L10nController::class, 'getPhp']);
    Route::post('L10n/{locale}/{filename}', [L10nController::class, 'postPhp']);

    // Get and post gettext values
    Route::get('L10n/{locale}/{category}/{filename}', [L10nController::class, 'getPo']);
    Route::post('L10n/{locale}/{category}/{filename}', [L10nController::class, 'postPo']);
});

// Catch-all Route...
Route::get('/{view?}', [HomeController::class, 'index'])
    ->where('view', '(.*)')
    ->name('polyglot');
