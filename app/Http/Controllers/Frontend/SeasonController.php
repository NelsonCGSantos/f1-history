<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use Illuminate\Support\Facades\Cache;

class SeasonController extends Controller
{
    /**
     * Show a season overview with sessions and activity counts.
     */
    public function show(int $year)
    {
        $season = Cache::remember("front:season:{$year}", now()->addMinutes(10), function () use ($year) {
            return Meeting::with(['sessions' => function ($query) {
                    $query->orderBy('start_time')
                          ->withCount(['laps', 'positions', 'stints']);
                }])
                ->where('season_year', $year)
                ->orderBy('start_date')
                ->get();
        });

        if ($season->isEmpty()) {
            abort(404);
        }

        $meetingCount   = $season->count();
        $totalLaps      = $season->sum(fn ($meeting) => $meeting->sessions->sum('laps_count'));
        $totalPositions = $season->sum(fn ($meeting) => $meeting->sessions->sum('positions_count'));
        $totalStints    = $season->sum(fn ($meeting) => $meeting->sessions->sum('stints_count'));

        return view('front.seasons.show', [
            'year'           => $year,
            'meetings'       => $season,
            'meetingCount'   => $meetingCount,
            'totalLaps'      => $totalLaps,
            'totalPositions' => $totalPositions,
            'totalStints'    => $totalStints,
        ]);
    }
}
