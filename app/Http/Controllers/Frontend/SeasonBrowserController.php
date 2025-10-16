<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Support\Facades\Cache;

class SeasonBrowserController extends Controller
{
    /**
     * Display a list of available seasons with lightweight metadata.
     */
    public function index()
    {
        $seasons = Cache::remember('front:seasons:index', now()->addMinutes(10), function () {
            return Meeting::select('season_year')
                ->selectRaw('COUNT(*) as race_count')
                ->groupBy('season_year')
                ->orderByDesc('season_year')
                ->get();
        });

        $latestSeasonYear = optional($seasons->first())->season_year;

        $recentMeetings = Cache::remember('front:seasons:recent-meetings', now()->addMinutes(5), function () {
            return Meeting::with(['sessions' => function ($query) {
                    $query->whereIn('type', ['RACE', 'SPRINT'])
                          ->withCount('laps')
                          ->orderByDesc('start_time');
                }])
                ->orderByDesc('start_date')
                ->take(3)
                ->get();
        });

        return view('front.home', [
            'seasons'           => $seasons,
            'latestSeasonYear'  => $latestSeasonYear,
            'recentMeetings'    => $recentMeetings,
        ]);
    }
}

