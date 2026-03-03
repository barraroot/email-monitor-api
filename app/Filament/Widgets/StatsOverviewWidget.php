<?php

namespace App\Filament\Widgets;

use App\Models\AuthEvent;
use App\Models\MailEvent;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    private function defaultStart(): Carbon
    {
        return now()->subHours(24);
    }

    private function mailCount(string $eventType): int
    {
        return MailEvent::query()
            ->where('event_type', $eventType)
            ->where('occurred_at', '>=', $this->defaultStart())
            ->count();
    }

    private function authCount(string $eventType): int
    {
        return AuthEvent::query()
            ->where('event_type', $eventType)
            ->where('occurred_at', '>=', $this->defaultStart())
            ->count();
    }

    private function queueDepth(): ?int
    {
        $expr = "COALESCE(NULLIF(meta->>'queue_depth', '')::int, NULLIF(meta->>'depth', '')::int, NULLIF(meta->>'value', '')::int)";

        return MailEvent::query()
            ->where('event_type', 'queue_depth')
            ->orderByDesc('occurred_at')
            ->selectRaw("{$expr} as value")
            ->value('value');
    }

    protected function getStats(): array
    {
        $inboundAccepted = $this->mailCount('mail_in_accepted');
        $inboundRejected = $this->mailCount('mail_in_rejected');
        $outboundSent = $this->mailCount('mail_out_sent');
        $outboundDeferred = $this->mailCount('mail_out_deferred');
        $outboundBounced = $this->mailCount('mail_bounced');
        $loginSuccess = $this->authCount('login_success');
        $loginFailed = $this->authCount('login_failed');
        $queueDepth = $this->queueDepth();

        return [
            Stat::make('Inbound Aceitos', number_format($inboundAccepted))
                ->description('Últimas 24h')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('success'),

            Stat::make('Inbound Rejeitados', number_format($inboundRejected))
                ->description('Últimas 24h')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Outbound Enviados', number_format($outboundSent))
                ->description('Últimas 24h')
                ->descriptionIcon('heroicon-m-arrow-up-tray')
                ->color('info'),

            Stat::make('Outbound Adiados', number_format($outboundDeferred))
                ->description('Últimas 24h')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Bounces', number_format($outboundBounced))
                ->description('Últimas 24h')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color('danger'),

            Stat::make('Logins com Sucesso', number_format($loginSuccess))
                ->description('Últimas 24h')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('success'),

            Stat::make('Logins com Falha', number_format($loginFailed))
                ->description('Últimas 24h')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color('danger'),

            Stat::make('Fila de Mensagens', $queueDepth !== null ? number_format($queueDepth) : 'N/A')
                ->description('Último valor registrado')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color($queueDepth > 100 ? 'danger' : ($queueDepth > 20 ? 'warning' : 'success')),
        ];
    }
}
