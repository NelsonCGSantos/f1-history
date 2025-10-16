<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\StandingsTableBuilder;
use Illuminate\Support\Facades\Cache;

class StandingsController extends Controller
{
    /**
     * Show championship standings for a season.
     */
    public function show(int $year, StandingsTableBuilder $builder)
    {
        $cacheKey  = "front:standings:{$year}";
        $standings = Cache::remember($cacheKey, now()->addMinutes(5), fn () => $builder->build($year));

        if ($standings->isEmpty()) {
            abort(404);
        }

        return view('front.seasons.standings', [
            'year'       => $year,
            'standings'  => $standings,
        ]);
    }
}

