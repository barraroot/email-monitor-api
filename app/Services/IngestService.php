<?php

namespace App\Services;

use App\Models\AuthEvent;
use App\Models\MailEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IngestService
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function ingest(array $payload): array
    {
        $mailEvents = $payload['mail_events'] ?? [];
        $authEvents = $payload['auth_events'] ?? [];

        $mailResult = [
            'received' => count($mailEvents),
            'inserted' => 0,
            'duplicates' => 0,
            'invalid' => 0,
        ];

        $authResult = [
            'received' => count($authEvents),
            'inserted' => 0,
            'duplicates' => 0,
            'invalid' => 0,
        ];

        DB::transaction(function () use ($payload, $mailEvents, $authEvents, &$mailResult, &$authResult): void {
            $mailRows = $this->mapMailEvents($mailEvents, $payload);
            $authRows = $this->mapAuthEvents($authEvents, $payload);

            if (count($mailRows) > 0) {
                $mailResult['inserted'] = MailEvent::query()->insertOrIgnore($mailRows);
                $mailResult['duplicates'] = max(0, $mailResult['received'] - $mailResult['inserted']);
            }

            if (count($authRows) > 0) {
                $authResult['inserted'] = AuthEvent::query()->insertOrIgnore($authRows);
                $authResult['duplicates'] = max(0, $authResult['received'] - $authResult['inserted']);
            }
        });

        return [
            'source' => $payload['source'] ?? null,
            'mail_events' => $mailResult,
            'auth_events' => $authResult,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $events
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    private function mapMailEvents(array $events, array $payload): array
    {
        $rows = [];
        $now = now();

        foreach ($events as $event) {
            $rows[] = [
                'occurred_at' => Carbon::parse($event['occurred_at']),
                'direction' => $event['direction'],
                'event_type' => $event['event_type'],
                'exim_message_id' => $event['exim_message_id'] ?? null,
                'sender' => $event['sender'] ?? null,
                'recipient' => $event['recipient'] ?? null,
                'domain' => $event['domain'] ?? $payload['domain'],
                'whm_account' => $event['whm_account'] ?? $payload['whm_account'],
                'ip' => $event['ip'] ?? null,
                'remote_mta' => $event['remote_mta'] ?? null,
                'smtp_code' => $event['smtp_code'] ?? null,
                'smtp_response' => $event['smtp_response'] ?? null,
                'status' => $event['status'] ?? null,
                'error_category' => $event['error_category'] ?? null,
                'error_message' => $event['error_message'] ?? null,
                'meta' => $event['meta'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $events
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    private function mapAuthEvents(array $events, array $payload): array
    {
        $rows = [];
        $now = now();

        foreach ($events as $event) {
            $rows[] = [
                'occurred_at' => Carbon::parse($event['occurred_at']),
                'proto' => $event['proto'],
                'event_type' => $event['event_type'],
                'user_email' => $event['user_email'] ?? null,
                'domain' => $event['domain'] ?? $payload['domain'],
                'whm_account' => $event['whm_account'] ?? $payload['whm_account'],
                'ip' => $event['ip'] ?? null,
                'auth_result' => $event['auth_result'],
                'failure_reason' => $event['failure_reason'] ?? null,
                'meta' => $event['meta'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return $rows;
    }
}
