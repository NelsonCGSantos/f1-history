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
        ];
    }
}
