<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services, including your routes.
     */
    public function boot(): void
    {
        $this->routes(function () {
            // API routes (loaded from routes/api.php, prefixed with /api)
            Route::middleware('api')
                 ->prefix('api')
                 ->group(base_path('routes/api.php'));

            // Web routes (loaded from routes/web.php)
            Route::middleware('web')
                 ->group(base_path('routes/web.php'));
        });
    }
}
