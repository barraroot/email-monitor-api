<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'proto' => $this->proto,
            'event_type' => $this->event_type,
            'user_email' => $this->user_email,
            'domain' => $this->domain,
            'whm_account' => $this->whm_account,
            'ip' => $this->ip,
            'auth_result' => $this->auth_result,
            'failure_reason' => $this->failure_reason,
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
