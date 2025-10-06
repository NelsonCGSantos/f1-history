<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $classification = collect($this->race_classification ?? [])
            ->map(function ($entry) {
                $driver = $entry['driver'] ?? null;

                return [
                    'position'    => $entry['position'] ?? null,
                    'recorded_at' => optional($entry['recorded_at'])->toIso8601String(),
                    'driver'      => $driver ? [
                        'id'            => $driver->id,
                        'driver_number' => $driver->driver_number,
                        'name'          => $driver->name,
                        'team_name'     => $driver->team_name,
                        'abbreviation'  => $driver->abbreviation,
                    ] : null,
                ];
            })
            ->filter()
            ->values();

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'season_year'     => $this->season_year,
            'location'        => $this->location,
            'country'         => $this->country,
            'start_date'      => optional($this->start_date)->toDateString(),
            'end_date'        => optional($this->end_date)->toDateString(),
            'sessions'        => SessionResource::collection($this->whenLoaded('sessions')),
            'classification'  => $classification,
        ];
    }
}
