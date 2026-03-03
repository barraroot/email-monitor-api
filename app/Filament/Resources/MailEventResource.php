<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailEventResource\Pages;
use App\Models\MailEvent;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MailEventResource extends Resource
{
    protected static ?string $model = MailEvent::class;

    protected static ?string $navigationLabel = 'Eventos de E-mail';

    protected static ?string $modelLabel = 'Evento de E-mail';

    protected static ?string $pluralModelLabel = 'Eventos de E-mail';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-envelope';
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
                    \Filament\Infolists\Components\TextEntry::make('direction')
                        ->label('Direção')
                        ->badge(),
                    \Filament\Infolists\Components\TextEntry::make('event_type')
                        ->label('Tipo'),
                    \Filament\Infolists\Components\TextEntry::make('status')
                        ->label('Status')
                        ->badge(),
                    \Filament\Infolists\Components\TextEntry::make('sender')
                        ->label('Remetente'),
                    \Filament\Infolists\Components\TextEntry::make('recipient')
                        ->label('Destinatário'),
                    \Filament\Infolists\Components\TextEntry::make('domain')
                        ->label('Domínio'),
                    \Filament\Infolists\Components\TextEntry::make('whm_account')
                        ->label('Conta WHM'),
                    \Filament\Infolists\Components\TextEntry::make('ip')
                        ->label('IP'),
                    \Filament\Infolists\Components\TextEntry::make('remote_mta')
                        ->label('MTA Remoto'),
                    \Filament\Infolists\Components\TextEntry::make('smtp_code')
                        ->label('Código SMTP'),
                    \Filament\Infolists\Components\TextEntry::make('smtp_response')
                        ->label('Resposta SMTP')
                        ->columnSpanFull(),
                    \Filament\Infolists\Components\TextEntry::make('error_category')
                        ->label('Categoria do Erro'),
                    \Filament\Infolists\Components\TextEntry::make('error_message')
                        ->label('Mensagem do Erro')
                        ->columnSpanFull(),
                    \Filament\Infolists\Components\TextEntry::make('exim_message_id')
                        ->label('Exim Message ID'),
                ]),
            \Filament\Schemas\Components\Section::make('Metadados')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('meta')
                        ->label('')
                        ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                        ->html(false)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(MailEvent::query())
            ->defaultSort('occurred_at', 'desc')
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('direction')
                    ->label('Direção')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'inbound' => 'info',
                        'outbound' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('event_type')
                    ->label('Tipo')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'accepted', 'sent' => 'success',
                        'rejected', 'bounced' => 'danger',
                        'deferred' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('sender')
                    ->label('Remetente')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('recipient')
                    ->label('Destinatário')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('error_category')
                    ->label('Erro')
                    ->badge()
                    ->color('danger')
                    ->toggleable(),
                TextColumn::make('smtp_code')
                    ->label('SMTP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ip')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('remote_mta')
                    ->label('MTA Remoto')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('period')
                    ->label('Período')
                    ->form([
                        \Filament\Forms\Components\DateTimePicker::make('occurred_from')
                            ->label('De'),
                        \Filament\Forms\Components\DateTimePicker::make('occurred_until')
                            ->label('Até'),
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
                SelectFilter::make('direction')
                    ->label('Direção')
                    ->options(['inbound' => 'Inbound', 'outbound' => 'Outbound']),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'accepted' => 'Accepted',
                        'rejected' => 'Rejected',
                        'sent' => 'Sent',
                        'deferred' => 'Deferred',
                        'bounced' => 'Bounced',
                    ]),
                SelectFilter::make('error_category')
                    ->label('Categoria de Erro')
                    ->options(fn () => MailEvent::query()
                        ->whereNotNull('error_category')
                        ->distinct()
                        ->pluck('error_category', 'error_category')
                        ->toArray()),
                Filter::make('sender')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('sender')->label('Remetente'),
                        \Filament\Forms\Components\TextInput::make('recipient')->label('Destinatário'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['sender'], fn ($q, $val) => $q->where('sender', 'ilike', "%{$val}%"))
                            ->when($data['recipient'], fn ($q, $val) => $q->where('recipient', 'ilike', "%{$val}%"));
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
            'index' => Pages\ListMailEvents::route('/'),
            'view' => Pages\ViewMailEvent::route('/{record}'),
        ];
    }
}
