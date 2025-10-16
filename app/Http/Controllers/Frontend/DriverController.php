<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Meeting;
use App\Models\Position;
use App\Models\Session;
use Illuminate\Support\Facades\Cache;

class DriverController extends Controller
{
    /**
     * Show a driver dashboard.
     */
    public function show(int $number)
    {
        $cacheKey = "front:drivers:{$number}";

        $driver = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($number) {
            $driver = Driver::where('driver_number', $number)->first();
            if (!$driver) {
                return null;
            }

            $driver->loadCount(['laps', 'stints']);

            $seasonYears = Meeting::select('season_year')
                ->whereHas('sessions.laps', fn ($query) => $query->where('driver_id', $driver->id))
                ->orderBy('season_year')
                ->distinct()
                ->pluck('season_year');

            $recentResults = $this->recentRaceResults($driver);

            $driver->setAttribute('season_years', $seasonYears);
            $driver->setAttribute('recent_results', $recentResults);

            return $driver;
        });

        if (!$driver) {
            abort(404);
        }

        return view('front.drivers.show', [
            'driver' => $driver,
        ]);
    }

    /**
     * Build a summary of the driver's most recent race results.
     */
    protected function recentRaceResults(Driver $driver, int $limit = 5)
    {
        $sessions = Session::where('type', 'RACE')
            ->whereHas('positions', fn ($query) => $query->where('driver_id', $driver->id))
            ->with('meeting')
            ->orderByDesc('start_time')
            ->take($limit)
            ->get();

        return $sessions->map(function ($session) use ($driver) {
            $classification = Position::finalClassificationForSession($session->id);
            $entry = $classification->get($driver->id);

            if (!$entry) {
                return null;
            }

            return [
                'meeting_id'   => $session->meeting_id,
                'meeting_name' => $session->meeting?->name,
                'season_year'  => $session->meeting?->season_year,
                'position'     => $entry['position'],
                'recorded_at'  => optional($entry['recorded_at'])->toIso8601String(),
            ];
        })->filter()->values();
    }
}

