<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngestResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'source' => $this->resource['source'] ?? null,
            'mail_events' => $this->resource['mail_events'] ?? [],
            'auth_events' => $this->resource['auth_events'] ?? [],
        ];
    }
}
