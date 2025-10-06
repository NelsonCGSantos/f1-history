<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'type'         => $this->type,
            'session_key'  => $this->session_key,
            'start_time'   => optional($this->start_time)->toIso8601String(),
            'end_time'     => optional($this->end_time)->toIso8601String(),
            'laps_count'   => $this->when(isset($this->laps_count), (int) $this->laps_count),
            'positions_count' => $this->when(isset($this->positions_count), (int) $this->positions_count),
            'stints_count' => $this->when(isset($this->stints_count), (int) $this->stints_count),
            'stints'       => StintResource::collection($this->whenLoaded('stints')),
        ];
    }
}
