<?php

namespace App\Services;

use App\Http\Requests\Api\V1\ListAuthEventsRequest;
use App\Models\AuthEvent;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AuthEventService
{
    public function paginate(ListAuthEventsRequest $request): LengthAwarePaginator
    {
        $filters = $request->validated();

        $query = AuthEvent::query();

        $this->applyDateFilters($query, $filters['start_at'] ?? null, $filters['end_at'] ?? null);

        if (! empty($filters['domain'])) {
            $query->where('domain', $filters['domain']);
        }

        if (! empty($filters['whm_account'])) {
            $query->where('whm_account', $filters['whm_account']);
        }

        if (! empty($filters['proto'])) {
            $query->where('proto', $filters['proto']);
        }

        if (! empty($filters['auth_result'])) {
            $query->where('auth_result', $filters['auth_result']);
        }

        if (! empty($filters['user_email'])) {
            $query->where('user_email', $filters['user_email']);
        }

        if (! empty($filters['ip'])) {
            $query->where('ip', $filters['ip']);
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
