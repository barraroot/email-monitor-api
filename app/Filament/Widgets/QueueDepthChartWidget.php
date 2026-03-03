<?php

namespace App\Filament\Widgets;

use App\Models\MailEvent;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class QueueDepthChartWidget extends ChartWidget
{
    protected ?string $heading = 'Profundidade da Fila (últimas 24h)';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $start = now()->subHours(24)->startOfHour();
        $end = now()->endOfHour();

        $valueExpr = "COALESCE(NULLIF(meta->>'queue_depth', '')::int, NULLIF(meta->>'depth', '')::int, NULLIF(meta->>'value', '')::int)";

        $rows = MailEvent::query()
            ->where('event_type', 'queue_depth')
            ->whereBetween('occurred_at', [$start, $end])
            ->selectRaw("date_trunc('hour', occurred_at) as bucket, max({$valueExpr}) as value")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        $hours = [];
        $current = $start->copy();
        while ($current <= $end) {
            $hours[] = $current->format('H:i');
            $current->addHour();
        }

        $hourlyData = array_fill_keys($hours, null);
        foreach ($rows as $row) {
            $hour = Carbon::parse($row->bucket)->format('H:i');
            if (array_key_exists($hour, $hourlyData)) {
                $hourlyData[$hour] = $row->value !== null ? (int) $row->value : null;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Queue Depth',
                    'data' => array_values($hourlyData),
                    'borderColor' => '#6366f1',
                    'backgroundColor' => '#6366f133',
                    'fill' => true,
                    'tension' => 0.4,
                    'spanGaps' => true,
                ],
            ],
            'labels' => $hours,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
