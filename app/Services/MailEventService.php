<?php

namespace App\Services;

use App\Http\Requests\Api\V1\ListMailEventsRequest;
use App\Models\MailEvent;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class MailEventService
{
    public function paginate(ListMailEventsRequest $request): LengthAwarePaginator
    {
        $filters = $request->validated();

        $query = MailEvent::query();

        $this->applyDateFilters($query, $filters['start_at'] ?? null, $filters['end_at'] ?? null);

        if (! empty($filters['domain'])) {
            $query->where('domain', $filters['domain']);
        }

        if (! empty($filters['whm_account'])) {
            $query->where('whm_account', $filters['whm_account']);
        }

        if (! empty($filters['direction'])) {
            $query->where('direction', $filters['direction']);
        }

        if (! empty($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['error_category'])) {
            $query->where('error_category', $filters['error_category']);
        }

        if (! empty($filters['sender'])) {
            $query->where('sender', $filters['sender']);
        }

        if (! empty($filters['recipient'])) {
            $query->where('recipient', $filters['recipient']);
        }

        $perPage = min((int) ($filters['per_page'] ?? 50), 200);

        return $query
            ->orderByDesc('occurred_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    private function applyDateFilters(Builder $query, ?string $startAt, ?string $endAt): void
    {
        if ($startAt) {
            $query->where('occurred_at', '>=', Carbon::parse($startAt));
        }

        if ($endAt) {
            $query->where('occurred_at', '<=', Carbon::parse($endAt));
        }
    }
}
