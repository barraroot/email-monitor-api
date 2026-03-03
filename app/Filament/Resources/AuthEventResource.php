<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthEventResource\Pages;
use App\Models\AuthEvent;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuthEventResource extends Resource
{
    protected static ?string $model = AuthEvent::class;

    protected static ?string $navigationLabel = 'Eventos de Autenticação';

    protected static ?string $modelLabel = 'Evento de Autenticação';

    protected static ?string $pluralModelLabel = 'Eventos de Autenticação';

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-lock-closed';
    }

    public static function getNavigationGroup(): string
    {
        return 'Monitoramento';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Informações do Evento')
                ->columns(2)
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('occurred_at')
                        ->label('Data/Hora')
                        ->dateTime('d/m/Y H:i:s'),
                    \Filament\Infolists\Components\TextEntry::make('proto')
                        ->label('Protocolo')
                        ->badge(),
                    \Filament\Infolists\Components\TextEntry::make('event_type')
                        ->label('Tipo'),
                    \Filament\Infolists\Components\TextEntry::make('auth_result')
                        ->label('Resultado')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'success' => 'success',
                            'failed' => 'danger',
                            default => 'gray',
                        }),
                    \Filament\Infolists\Components\TextEntry::make('user_email')
                        ->label('E-mail do Usuário'),
                    \Filament\Infolists\Components\TextEntry::make('ip')
                        ->label('IP'),
                    \Filament\Infolists\Components\TextEntry::make('domain')
                        ->label('Domínio'),
                    \Filament\Infolists\Components\TextEntry::make('whm_account')
                        ->label('Conta WHM'),
                    \Filament\Infolists\Components\TextEntry::make('failure_reason')
                        ->label('Motivo da Falha')
                        ->columnSpanFull(),
                ]),
            \Filament\Schemas\Components\Section::make('Metadados')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('meta')
                        ->label('')
                        ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(AuthEvent::query())
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('proto')
                    ->label('Protocolo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'imap' => 'info',
                        'pop3' => 'primary',
                        'smtp' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('auth_result')
                    ->label('Resultado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('user_email')
                    ->label('E-mail')
                    ->searchable(),
                TextColumn::make('ip')
                    ->label('IP')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('failure_reason')
                    ->label('Motivo')
                    ->toggleable(),
            ])
            ->filters([
                Filter::make('period')
                    ->label('Período')
                    ->form([
                        \Filament\Forms\Components\DateTimePicker::make('occurred_from')->label('De'),
                        \Filament\Forms\Components\DateTimePicker::make('occurred_until')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['occurred_from'], fn ($q, $val) => $q->where('occurred_at', '>=', $val))
                            ->when($data['occurred_until'], fn ($q, $val) => $q->where('occurred_at', '<=', $val));
                    }),
                Filter::make('domain_account')
                    ->label('Domínio / Conta')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('domain')->label('Domínio'),
                        \Filament\Forms\Components\TextInput::make('whm_account')->label('Conta WHM'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['domain'], fn ($q, $val) => $q->where('domain', $val))
                            ->when($data['whm_account'], fn ($q, $val) => $q->where('whm_account', $val));
                    }),
                SelectFilter::make('proto')
                    ->label('Protocolo')
                    ->options(['imap' => 'IMAP', 'pop3' => 'POP3', 'smtp' => 'SMTP']),
                SelectFilter::make('auth_result')
                    ->label('Resultado')
                    ->options(['success' => 'Sucesso', 'failed' => 'Falha']),
                Filter::make('user_email')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('user_email')->label('E-mail do Usuário'),
                        \Filament\Forms\Components\TextInput::make('ip')->label('IP'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['user_email'], fn ($q, $val) => $q->where('user_email', 'ilike', "%{$val}%"))
                            ->when($data['ip'], fn ($q, $val) => $q->where('ip', 'ilike', "%{$val}%"));
                    }),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuthEvents::route('/'),
            'view' => Pages\ViewAuthEvent::route('/{record}'),
        ];
    }
}
