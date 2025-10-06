<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StandingsResource;
use App\Models\Driver;
use App\Models\Meeting;
use App\Models\Position;

class StandingsController extends Controller
{
    /**
     * GET /api/v1/seasons/{year}/standings
     */
    public function show(int $year)
    {
        $meetings = Meeting::where('season_year', $year)
            ->with(['sessions' => fn ($query) => $query->where('type', 'RACE')])
            ->orderBy('start_date')
            ->get();

        if ($meetings->isEmpty()) {
            abort(404, 'Season not found');
        }

        $pointsTable = [
            1 => 25,
            2 => 18,
            3 => 15,
            4 => 12,
            5 => 10,
            6 => 8,
            7 => 6,
            8 => 4,
            9 => 2,
            10 => 1,
        ];

        $totals = [];

        foreach ($meetings as $meeting) {
            $raceSession = $meeting->sessions->first();
            if (!$raceSession) {
                continue;
            }

            $classification = Position::finalClassificationForSession($raceSession->id);

            foreach ($classification as $driverId => $meta) {
                $position = $meta['position'];
                $points = $pointsTable[$position] ?? 0;

                if (!isset($totals[$driverId])) {
                    $totals[$driverId] = [
                        'driver_id' => $driverId,
                        'points'    => 0,
                        'wins'      => 0,
                        'podiums'   => 0,
                        'races'     => 0,
                        'results'   => [],
                    ];
                }

                $totals[$driverId]['points'] += $points;
                $totals[$driverId]['races']++;
                if ($position === 1) {
                    $totals[$driverId]['wins']++;
                }
                if ($position <= 3) {
                    $totals[$driverId]['podiums']++;
                }

                $totals[$driverId]['results'][] = [
                    'meeting_id'   => $meeting->id,
                    'meeting_name' => $meeting->name,
                    'position'     => $position,
                    'points'       => $points,
                    'recorded_at'  => optional($meta['recorded_at'])->toIso8601String(),
                ];
            }
        }

        $driverModels = Driver::whereIn('id', array_keys($totals))->get()->keyBy('id');

        $standings = collect($totals)
            ->map(function ($row) use ($driverModels) {
                $driver = $driverModels->get($row['driver_id']);
                if (!$driver) {
                    return null;
                }

                return [
                    'driver'  => $driver,
                    'points'  => $row['points'],
                    'wins'    => $row['wins'],
                    'podiums' => $row['podiums'],
                    'races'   => $row['races'],
                    'results' => $row['results'],
                ];
            })
            ->filter()
            ->sort(function ($a, $b) {
                return [$b['points'], $b['wins'], $b['podiums']] <=> [$a['points'], $a['wins'], $a['podiums']];
            })
            ->values();

        return StandingsResource::collection($standings);
    }
}
