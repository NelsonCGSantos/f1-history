<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\StandingsResource;
use App\Services\StandingsTableBuilder;

class StandingsController extends Controller
{
    /**
     * GET /api/v1/seasons/{year}/standings
     */
    public function show(int $year, StandingsTableBuilder $builder)
    {
        $standings = $builder->build($year);
        if ($standings->isEmpty()) {
            abort(404, 'Season not found');
        }

        return StandingsResource::collection($standings);
    }
}
