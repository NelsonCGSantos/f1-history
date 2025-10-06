<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RaceResource;
use App\Models\Driver;
use App\Models\Meeting;
use App\Models\Position;

class RaceController extends Controller
{
    /**
     * GET /api/v1/races/{meeting}
     */
    public function show(Meeting $meeting)
    {
        $meeting->load([
            'sessions' => function ($query) {
                $query->orderBy('start_time')
                      ->withCount(['laps', 'positions', 'stints']);
            },
        ]);

        $raceSession = $meeting->sessions->firstWhere('type', 'RACE');
        $classification = collect();

        if ($raceSession) {
            $finalClassification = Position::finalClassificationForSession($raceSession->id);
            $drivers = Driver::whereIn('id', $finalClassification->keys())->get()->keyBy('id');

            $classification = $finalClassification
                ->map(function ($entry, $driverId) use ($drivers) {
                    $driver = $drivers->get($driverId);

                    return [
                        'driver'      => $driver,
                        'position'    => $entry['position'],
                        'recorded_at' => $entry['recorded_at'],
                    ];
                })
                ->sortBy('position')
                ->values();
        }

        $meeting->setAttribute('race_classification', $classification);

        return new RaceResource($meeting);
    }
}
