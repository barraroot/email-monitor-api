<?php

namespace App\Providers\Filament;

use App\Filament\Resources\AuthEventResource;
use App\Filament\Resources\CpanelAccountResource;
use App\Filament\Resources\IngestClientResource;
use App\Filament\Resources\MailboxResource;
use App\Filament\Resources\MailEventResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\AuthEventsChartWidget;
use App\Filament\Widgets\MailEventsChartWidget;
use App\Filament\Widgets\QueueDepthChartWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->colors([
                'primary' => Color::Indigo,
                'danger' => Color::Rose,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'info' => Color::Sky,
            ])
            ->maxContentWidth(Width::Full)
            ->darkMode(true, true)
            ->brandName('Mail Monitor')
            ->navigationGroups([
                NavigationGroup::make('Monitoramento'),
                NavigationGroup::make('Configuração'),
                NavigationGroup::make('Administração'),
            ])
            ->resources([
                MailEventResource::class,
                AuthEventResource::class,
                MailboxResource::class,
                CpanelAccountResource::class,
                IngestClientResource::class,
                UserResource::class,
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                StatsOverviewWidget::class,
                MailEventsChartWidget::class,
                AuthEventsChartWidget::class,
                QueueDepthChartWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
