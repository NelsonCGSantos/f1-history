<?php

use App\Http\Controllers\Frontend\DriverController as FrontendDriverController;
use App\Http\Controllers\Frontend\RaceController as FrontendRaceController;
use App\Http\Controllers\Frontend\SeasonBrowserController;
use App\Http\Controllers\Frontend\SeasonController as FrontendSeasonController;
use App\Http\Controllers\Frontend\StandingsController as FrontendStandingsController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SeasonBrowserController::class, 'index'])->name('home');

Route::prefix('seasons')->name('seasons.')->group(function () {
    Route::get('{year}', [FrontendSeasonController::class, 'show'])
        ->whereNumber('year')
        ->name('show');

    Route::get('{year}/standings', [FrontendStandingsController::class, 'show'])
        ->whereNumber('year')
        ->name('standings');
});

Route::get('/races/{meeting}', [FrontendRaceController::class, 'show'])
    ->whereNumber('meeting')
    ->name('races.show');

Route::get('/drivers/{number}', [FrontendDriverController::class, 'show'])
    ->whereNumber('number')
    ->name('drivers.show');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
