<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MailEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'direction' => $this->direction,
            'event_type' => $this->event_type,
            'exim_message_id' => $this->exim_message_id,
            'sender' => $this->sender,
            'recipient' => $this->recipient,
            'domain' => $this->domain,
            'whm_account' => $this->whm_account,
            'ip' => $this->ip,
            'remote_mta' => $this->remote_mta,
            'smtp_code' => $this->smtp_code,
            'smtp_response' => $this->smtp_response,
            'status' => $this->status,
            'error_category' => $this->error_category,
            'error_message' => $this->error_message,
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
