<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CpanelAccountResource\Pages;
use App\Models\CpanelAccount;
use App\Services\CpanelAccountService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Crypt;

class CpanelAccountResource extends Resource
{
    protected static ?string $model = CpanelAccount::class;

    protected static ?string $navigationLabel = 'Contas cPanel';

    protected static ?string $modelLabel = 'Conta cPanel';

    protected static ?string $pluralModelLabel = 'Contas cPanel';

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-server';
    }

    public static function getNavigationGroup(): string
    {
        return 'Configuração';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\Section::make('Dados da Conta')
                ->columns(2)
                ->schema([
                    TextInput::make('whm_account')
                        ->label('Conta WHM')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('domain')
                        ->label('Domínio')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('cpanel_host')
                        ->label('Host cPanel')
                        ->required()
                        ->url()
                        ->maxLength(255),
                    TextInput::make('cpanel_username')
                        ->label('Usuário cPanel')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('api_token_plain')
                        ->label('API Token')
                        ->password()
                        ->revealable()
                        ->helperText('Preencha apenas para criar ou atualizar o token. O token salvo nunca é exibido.')
                        ->maxLength(500),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(CpanelAccount::query())
            ->defaultSort('domain')
            ->columns([
                TextColumn::make('whm_account')
                    ->label('Conta WHM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('domain')
                    ->label('Domínio')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('cpanel_username')
                    ->label('Usuário')
                    ->toggleable(),
                TextColumn::make('cpanel_host')
                    ->label('Host')
                    ->toggleable(),
                TextColumn::make('token_last_verified_at')
                    ->label('Token verificado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('test_credentials')
                    ->label('Testar Credenciais')
                    ->icon('heroicon-o-shield-check')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Testar Credenciais cPanel')
                    ->modalDescription('Será feita uma chamada ao cPanel para validar o token atual.')
                    ->action(function (CpanelAccount $record): void {
                        try {
                            $service = app(CpanelAccountService::class);
                            $token = Crypt::decryptString($record->api_token_encrypted);
                            $verifySsl = (bool) config('services.cpanel.verify_ssl', true);
                            $cpanelClient = app(\App\Services\CpanelClientService::class);

                            $valid = $cpanelClient->validateToken(
                                $record->cpanel_host,
                                $record->cpanel_username,
                                $token,
                                $verifySsl
                            );

                            if ($valid) {
                                $record->update(['token_last_verified_at' => now()]);
                                Notification::make()
                                    ->title('Credenciais válidas')
                                    ->body('Token verificado com sucesso em '.now()->format('d/m/Y H:i').'.')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Credenciais inválidas')
                                    ->body('O token não foi aceito pelo cPanel.')
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro ao testar credenciais')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('sync_mailboxes')
                    ->label('Sincronizar Mailboxes')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Sincronizar Mailboxes')
                    ->modalDescription('Isso importará ou atualizará os mailboxes do cPanel para o sistema.')
                    ->action(function (CpanelAccount $record): void {
                        try {
                            $service = app(CpanelAccountService::class);
                            $count = $service->syncMailboxes($record);
                            Notification::make()
                                ->title('Sincronização concluída')
                                ->body("{$count} mailbox(es) sincronizado(s) com sucesso.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Erro na sincronização')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
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
            'index' => Pages\ListCpanelAccounts::route('/'),
            'create' => Pages\CreateCpanelAccount::route('/create'),
            'view' => Pages\ViewCpanelAccount::route('/{record}'),
            'edit' => Pages\EditCpanelAccount::route('/{record}/edit'),
        ];
    }
}
