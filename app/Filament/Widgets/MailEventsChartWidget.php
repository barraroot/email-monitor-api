<?php

namespace App\Filament\Widgets;

use App\Models\MailEvent;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MailEventsChartWidget extends ChartWidget
{
    protected ?string $heading = 'Eventos de E-mail (últimas 24h por hora)';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $start = now()->subHours(24)->startOfHour();
        $end = now()->endOfHour();

        $eventTypes = [
            'mail_in_accepted' => 'Inbound Aceitos',
            'mail_in_rejected' => 'Inbound Rejeitados',
            'mail_out_sent' => 'Outbound Enviados',
            'mail_out_deferred' => 'Adiados',
            'mail_bounced' => 'Bounces',
        ];

        $colors = [
            'mail_in_accepted' => '#10b981',
            'mail_in_rejected' => '#ef4444',
            'mail_out_sent' => '#3b82f6',
            'mail_out_deferred' => '#f59e0b',
            'mail_bounced' => '#ec4899',
        ];

        $rows = MailEvent::query()
            ->whereIn('event_type', array_keys($eventTypes))
            ->whereBetween('occurred_at', [$start, $end])
            ->selectRaw("DATE_FORMAT(occurred_at, '%Y-%m-%d %H:00:00') as bucket, event_type, count(*) as total")
            ->groupBy('bucket', 'event_type')
            ->orderBy('bucket')
            ->get();

        $hours = [];
        $current = $start->copy();
        while ($current <= $end) {
            $hours[] = $current->format('H:i');
            $current->addHour();
        }

        $datasets = [];
        foreach ($eventTypes as $type => $label) {
            $hourlyData = array_fill_keys($hours, 0);

            foreach ($rows as $row) {
                if ($row->event_type === $type) {
                    $hour = Carbon::parse($row->bucket)->format('H:i');
                    if (isset($hourlyData[$hour])) {
                        $hourlyData[$hour] = (int) $row->total;
                    }
                }
            }

            $datasets[] = [
                'label' => $label,
                'data' => array_values($hourlyData),
                'borderColor' => $colors[$type],
                'backgroundColor' => $colors[$type].'33',
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
        return 'line';
    }
}
