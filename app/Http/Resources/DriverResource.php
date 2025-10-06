<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'driver_number'  => $this->driver_number,
            'name'           => $this->name,
            'team_name'      => $this->team_name,
            'nationality'    => $this->nationality,
            'abbreviation'   => $this->abbreviation,
            'laps_count'     => $this->when(isset($this->laps_count), (int) $this->laps_count),
            'stints_count'   => $this->when(isset($this->stints_count), (int) $this->stints_count),
            'season_years'   => $this->season_years ?? [],
            'recent_results' => $this->recent_results ?? [],
        ];
    }
}
