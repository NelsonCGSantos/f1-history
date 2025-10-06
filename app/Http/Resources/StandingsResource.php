<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $driver = $this->resource['driver'] ?? null;

        return [
            'driver'  => $driver ? [
                'id'            => $driver->id,
                'driver_number' => $driver->driver_number,
                'name'          => $driver->name,
                'team_name'     => $driver->team_name,
                'abbreviation'  => $driver->abbreviation,
            ] : null,
            'points'  => $this->resource['points'] ?? 0,
            'wins'    => $this->resource['wins'] ?? 0,
            'podiums' => $this->resource['podiums'] ?? 0,
            'races'   => $this->resource['races'] ?? 0,
            'results' => collect($this->resource['results'] ?? [])->map(function ($result) {
                return [
                    'meeting_id'   => $result['meeting_id'] ?? null,
                    'meeting_name' => $result['meeting_name'] ?? null,
                    'position'     => $result['position'] ?? null,
                    'points'       => $result['points'] ?? 0,
                    'recorded_at'  => $result['recorded_at'] ?? null,
                ];
            })->values(),
        ];
    }
}
