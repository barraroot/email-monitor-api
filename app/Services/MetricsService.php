<?php

namespace App\Services;

use App\Http\Requests\Api\V1\MetricsOverviewRequest;
use App\Http\Requests\Api\V1\MetricsQueueRequest;
use App\Http\Requests\Api\V1\MetricsSeriesRequest;
use App\Models\AuthEvent;
use App\Models\MailEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MetricsService
{
    private const MAIL_EVENT_MAP = [
        'inbound_accepted' => 'mail_in_accepted',
        'inbound_rejected' => 'mail_in_rejected',
        'outbound_sent' => 'mail_out_sent',
        'outbound_deferred' => 'mail_out_deferred',
        'outbound_bounced' => 'mail_bounced',
    ];

    /**
     * @return array<string, mixed>
     */
    public function overview(MetricsOverviewRequest $request): array
    {
        $filters = $request->validated();

        $mailQuery = MailEvent::query();
        $authQuery = AuthEvent::query();

        $this->applyCommonFilters($mailQuery, $filters);
        $this->applyCommonFilters($authQuery, $filters);

        $data = [];

        foreach (self::MAIL_EVENT_MAP as $label => $eventType) {
            $data[$label] = (clone $mailQuery)->where('event_type', $eventType)->count();
        }

        $data['login_success'] = (clone $authQuery)->where('event_type', 'login_success')->count();
        $data['login_failed'] = (clone $authQuery)->where('event_type', 'login_failed')->count();

        $data['top_error_categories'] = (clone $mailQuery)
            ->whereNotNull('error_category')
            ->select('error_category', DB::raw('count(*) as total'))
            ->groupBy('error_category')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->error_category,
                'value' => (int) $row->total,
            ])
            ->values()
            ->all();

        $data['top_failure_reasons'] = (clone $authQuery)
            ->whereNotNull('failure_reason')
            ->select('failure_reason', DB::raw('count(*) as total'))
            ->groupBy('failure_reason')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->failure_reason,
                'value' => (int) $row->total,
            ])
            ->values()
            ->all();

        return $data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mailSeries(MetricsSeriesRequest $request): array
    {
        $filters = $request->validated();
        $interval = $filters['interval'] ?? 'hour';

        $query = MailEvent::query()->whereIn('event_type', array_values(self::MAIL_EVENT_MAP));
        $this->applyCommonFilters($query, $filters);

        $bucketExpr = $this->dateTruncExpr($interval, 'occurred_at');

        $rows = $query
            ->selectRaw("{$bucketExpr} as bucket, event_type, count(*) as total")
            ->groupBy('bucket', 'event_type')
            ->orderBy('bucket')
            ->get();

        $series = [];
        $eventToLabel = array_flip(self::MAIL_EVENT_MAP);

        foreach (self::MAIL_EVENT_MAP as $label => $eventType) {
            $series[$label] = [
                'label' => $label,
                'points' => [],
            ];
        }

        foreach ($rows as $row) {
            $label = $eventToLabel[$row->event_type] ?? $row->event_type;
            $series[$label]['points'][] = [
                't' => Carbon::parse($row->bucket)->toIso8601String(),
                'value' => (int) $row->total,
            ];
        }

        return array_values($series);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function authSeries(MetricsSeriesRequest $request): array
    {
        $filters = $request->validated();
        $interval = $filters['interval'] ?? 'hour';

        $query = AuthEvent::query()->whereIn('event_type', ['login_success', 'login_failed']);
        $this->applyCommonFilters($query, $filters);

        $bucketExpr = $this->dateTruncExpr($interval, 'occurred_at');

        $rows = $query
            ->selectRaw("{$bucketExpr} as bucket, event_type, proto, count(*) as total")
            ->groupBy('bucket', 'event_type', 'proto')
            ->orderBy('bucket')
            ->get();

        $series = [];

        foreach (['login_success', 'login_failed'] as $eventType) {
            foreach (['imap', 'pop3', 'smtp'] as $proto) {
                $label = $eventType.'_'.$proto;
                $series[$label] = [
                    'label' => $label,
                    'points' => [],
                ];
            }
        }

        foreach ($rows as $row) {
            $label = $row->event_type.'_'.$row->proto;
            $series[$label]['points'][] = [
                't' => Carbon::parse($row->bucket)->toIso8601String(),
                'value' => (int) $row->total,
            ];
        }

        return array_values($series);
    }

    /**
     * @return array<string, mixed>
     */
    public function queue(MetricsQueueRequest $request): array
    {
        $filters = $request->validated();
        $interval = $filters['interval'] ?? 'hour';

        $query = MailEvent::query()->where('event_type', 'queue_depth');
        $this->applyCommonFilters($query, $filters);

        $valueExpression = $this->queueValueExpression();

        $lastDepth = (clone $query)
            ->orderByDesc('occurred_at')
            ->selectRaw("{$valueExpression} as value")
            ->value('value');

        $bucketExpr = $this->dateTruncExpr($interval, 'occurred_at');

        $rows = $query
            ->selectRaw("{$bucketExpr} as bucket, max({$valueExpression}) as value")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        $series = [
            [
                'label' => 'queue_depth',
                'points' => $rows->map(fn ($row) => [
                    't' => Carbon::parse($row->bucket)->toIso8601String(),
                    'value' => $row->value !== null ? (int) $row->value : null,
                ])->all(),
            ],
        ];

        return [
            'last_queue_depth' => $lastDepth !== null ? (int) $lastDepth : null,
            'series' => $series,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function applyCommonFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['start_at'])) {
            $query->where('occurred_at', '>=', Carbon::parse($filters['start_at']));
        }

        if (! empty($filters['end_at'])) {
            $query->where('occurred_at', '<=', Carbon::parse($filters['end_at']));
        }

        if (! empty($filters['domain'])) {
            $query->where('domain', $filters['domain']);
        }

        if (! empty($filters['whm_account'])) {
            $query->where('whm_account', $filters['whm_account']);
        }
    }

    private function dateTruncExpr(string $interval, string $column): string
    {
        return match ($interval) {
            'day' => "DATE_FORMAT({$column}, '%Y-%m-%d 00:00:00')",
            default => "DATE_FORMAT({$column}, '%Y-%m-%d %H:00:00')",
        };
    }

    private function queueValueExpression(): string
    {
        return "COALESCE(
            CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.queue_depth')), '') AS SIGNED),
            CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.depth')), '') AS SIGNED),
            CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.value')), '') AS SIGNED)
        )";
    }
}
