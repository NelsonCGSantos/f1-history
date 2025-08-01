<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\SessionResource;

class SeasonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \\Illuminate\\Http\\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'location'   => $this->location,
            'country'    => $this->country,
            'start_date' => optional($this->start_date)->toDateString(),
            'end_date'   => optional($this->end_date)->toDateString(),

            // Eager‐loaded sessions (P1, P2, Qualify, Race)
            'sessions'   => SessionResource::collection(
                                $this->whenLoaded('sessions')
                            ),
        ];
    }
}
