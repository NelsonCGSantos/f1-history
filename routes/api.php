<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\SeasonController;
use App\Http\Controllers\API\V1\StandingsController;
use App\Http\Controllers\API\V1\RaceController;
use App\Http\Controllers\API\V1\RaceStintController;
use App\Http\Controllers\API\V1\DriverController;

Route::prefix('v1')->group(function () {
    // Seasons
    Route::get('seasons',                  [SeasonController::class,    'index']);
    Route::get('seasons/{year}',           [SeasonController::class,    'show']);
    Route::get('seasons/{year}/standings', [StandingsController::class, 'show']);

    // Races
    Route::get('races/{meeting}',          [RaceController::class,      'show']);
    Route::get('races/{meeting}/stints',   [RaceStintController::class, 'show']);

    // Drivers
    Route::get('drivers/{number}',         [DriverController::class,    'show']);
});
