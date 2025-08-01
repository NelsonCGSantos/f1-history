<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Http\Resources\SeasonResource;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    /**
     * GET /api/v1/seasons
     * Return a list of all season years.
     */
    public function index()
    {
        $years = Meeting::select('season_year')
                        ->distinct()
                        ->orderByDesc('season_year')
                        ->pluck('season_year');

        return response()->json(['data' => $years]);
    }

    /**
     * GET /api/v1/seasons/{year}
     * Return all Grands Prix (meetings) for a given year,
     * wrapped in SeasonResource for JSON shaping.
     */
    public function show($year)
    {
        $meetings = Meeting::with('sessions')
                           ->where('season_year', $year)
                           ->orderBy('start_date')
                           ->get();

        // Wrap each Meeting in SeasonResource
        return SeasonResource::collection($meetings);
    }


}
