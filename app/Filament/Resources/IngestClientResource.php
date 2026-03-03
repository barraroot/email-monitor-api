<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IngestClientResource\Pages;
use App\Models\IngestClient;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IngestClientResource extends Resource
{
    protected static ?string $model = IngestClient::class;

    protected static ?string $navigationLabel = 'Clientes Ingest';

    protected static ?string $modelLabel = 'Cliente Ingest';

    protected static ?string $pluralModelLabel = 'Clientes Ingest';

    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-cpu-chip';
    }

    public static function getNavigationGroup(): string
    {
        return 'Configuração';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make()
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),
                    Toggle::make('is_active')
                        ->label('Ativo')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(IngestClient::query())
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                TextColumn::make('last_seen_at')
                    ->label('Último contato')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Nunca'),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Ativo'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('generate_secret')
                    ->label('Gerar Novo Segredo')
                    ->icon('heroicon-o-key')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Gerar Novo Shared Secret')
                    ->modalDescription('O segredo atual será invalidado. Copie o novo segredo — ele não será exibido novamente.')
                    ->action(function (IngestClient $record): void {
                        $secret = Str::random(64);
                        Log::info($secret);
                        $record->update(['shared_secret_hash' => hash('sha256', $secret)]);

                        Notification::make()
                            ->title('Novo segredo gerado')
                            ->body("Shared secret: **{$secret}**\n\nCopie agora — não será exibido novamente.")
                            ->success()
                            ->persistent()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIngestClients::route('/'),
            'create' => Pages\CreateIngestClient::route('/create'),
            'view' => Pages\ViewIngestClient::route('/{record}'),
            'edit' => Pages\EditIngestClient::route('/{record}/edit'),
        ];
    }
}
