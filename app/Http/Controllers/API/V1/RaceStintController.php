<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SessionResource;
use App\Models\Meeting;

class RaceStintController extends Controller
{
    /**
     * GET /api/v1/races/{meeting}/stints
     */
    public function show(Meeting $meeting)
    {
        $meeting->load([
            'sessions' => function ($query) {
                $query->whereIn('type', ['RACE', 'SPRINT'])
                      ->orderBy('start_time')
                      ->with([
                          'stints' => function ($stints) {
                              $stints->with('driver')
                                     ->orderBy('driver_id')
                                     ->orderBy('stint_number');
                          },
                      ])
                      ->withCount(['stints']);
            },
        ]);

        return response()->json([
            'meeting'  => [
                'id'          => $meeting->id,
                'name'        => $meeting->name,
                'season_year' => $meeting->season_year,
            ],
            'sessions' => SessionResource::collection($meeting->sessions),
        ]);
    }
}
