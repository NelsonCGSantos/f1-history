<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Meeting;
use App\Models\Position;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RaceController extends Controller
{
    /**
     * Show a meeting dashboard with race classification and stint data.
     */
    public function show(Meeting $meeting)
    {
        $cacheKey = "front:races:{$meeting->id}";

        $meeting = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($meeting) {
            return $meeting->fresh([
                'sessions' => function ($query) {
                    $query->orderBy('start_time')
                          ->with([
                              'stints' => fn ($stints) => $stints->with('driver')
                                  ->orderBy('driver_id')
                                  ->orderBy('stint_number'),
                          ])
                          ->withCount(['laps', 'positions', 'stints']);
                },
            ]);
        });

        if (!$meeting) {
            abort(404);
        }

        $raceSession = $meeting->sessions->firstWhere('type', 'RACE');
        $classification = $this->buildClassification($raceSession);
        $stintsByDriver = $this->groupStintsByDriver($raceSession?->stints ?? collect());

        return view('front.races.show', [
            'meeting'        => $meeting,
            'raceSession'    => $raceSession,
            'classification' => $classification,
            'stintsByDriver' => $stintsByDriver,
        ]);
    }

    /**
     * Build an ordered race classification table.
     */
    protected function buildClassification($raceSession): Collection
    {
        if (!$raceSession) {
            return collect();
        }

        $finalClassification = Position::finalClassificationForSession($raceSession->id);
        if ($finalClassification->isEmpty()) {
            return collect();
        }

        $drivers = Driver::whereIn('id', $finalClassification->keys())->get()->keyBy('id');

        return $finalClassification
            ->map(function ($entry, $driverId) use ($drivers) {
                $driver = $drivers->get($driverId);

                return [
                    'driver'      => $driver,
                    'position'    => $entry['position'],
                    'recorded_at' => optional($entry['recorded_at'])->toIso8601String(),
                ];
            })
            ->filter()
            ->sortBy('position')
            ->values();
    }

    /**
     * Organise stint data per driver for quick lookup in the view.
     */
    protected function groupStintsByDriver(Collection $stints): Collection
    {
        return $stints->groupBy('driver_id')->map(function ($driverStints) {
            $driver = optional($driverStints->first())->driver;

            return [
                'driver' => $driver,
                'stints' => $driverStints->map(function ($stint) {
                    return [
                        'stint_number'      => $stint->stint_number,
                        'start_lap'         => $stint->start_lap,
                        'end_lap'           => $stint->end_lap,
                        'tire_compound'     => $stint->tire_compound,
                        'tyre_age_at_start' => $stint->tyre_age_at_start,
                    ];
                }),
            ];
        })->values();
    }
}

