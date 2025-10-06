<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $driver = $this->driver;

        return [
            'id'                => $this->id,
            'stint_number'      => $this->stint_number,
            'start_lap'         => $this->start_lap,
            'end_lap'           => $this->end_lap,
            'tire_compound'     => $this->tire_compound,
            'tyre_age_at_start' => $this->tyre_age_at_start,
            'session_id'        => $this->session_id,
            'session_type'      => $this->session?->type,
            'driver'            => $driver ? [
                'id'            => $driver->id,
                'driver_number' => $driver->driver_number,
                'name'          => $driver->name,
                'team_name'     => $driver->team_name,
                'abbreviation'  => $driver->abbreviation,
            ] : null,
        ];
    }
}
