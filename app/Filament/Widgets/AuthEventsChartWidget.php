<?php

namespace App\Filament\Widgets;

use App\Models\AuthEvent;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AuthEventsChartWidget extends ChartWidget
{
    protected ?string $heading = 'Autenticações por Protocolo (últimas 24h)';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $start = now()->subHours(24)->startOfHour();
        $end = now()->endOfHour();

        $series = [];
        foreach (['login_success', 'login_failed'] as $eventType) {
            foreach (['imap', 'pop3', 'smtp'] as $proto) {
                $series["{$eventType}_{$proto}"] = [
                    'label' => ($eventType === 'login_success' ? '✓' : '✗').' '.strtoupper($proto),
                ];
            }
        }

        $colors = [
            'login_success_imap' => '#10b981',
            'login_success_pop3' => '#3b82f6',
            'login_success_smtp' => '#8b5cf6',
            'login_failed_imap' => '#ef4444',
            'login_failed_pop3' => '#f97316',
            'login_failed_smtp' => '#ec4899',
        ];

        $rows = AuthEvent::query()
            ->whereIn('event_type', ['login_success', 'login_failed'])
            ->whereBetween('occurred_at', [$start, $end])
            ->selectRaw("DATE_FORMAT(occurred_at, '%Y-%m-%d %H:00:00') as bucket, event_type, proto, count(*) as total")
            ->groupBy('bucket', 'event_type', 'proto')
            ->orderBy('bucket')
            ->get();

        $hours = [];
        $current = $start->copy();
        while ($current <= $end) {
            $hours[] = $current->format('H:i');
            $current->addHour();
        }

        $datasets = [];
        foreach ($series as $key => $meta) {
            [$eventType, $proto] = explode('_', $key, 2);
            $hourlyData = array_fill_keys($hours, 0);

            foreach ($rows as $row) {
                if ($row->event_type === $eventType && $row->proto === $proto) {
                    $hour = Carbon::parse($row->bucket)->format('H:i');
                    if (isset($hourlyData[$hour])) {
                        $hourlyData[$hour] = (int) $row->total;
                    }
                }
            }

            $datasets[] = [
                'label' => $meta['label'],
                'data' => array_values($hourlyData),
                'borderColor' => $colors[$key] ?? '#6b7280',
                'backgroundColor' => ($colors[$key] ?? '#6b7280').'33',
                'fill' => false,
                'tension' => 0.3,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $hours,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
