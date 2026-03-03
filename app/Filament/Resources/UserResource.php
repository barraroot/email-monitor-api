<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Usuários';

    protected static ?string $modelLabel = 'Usuário';

    protected static ?string $pluralModelLabel = 'Usuários';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-users';
    }

    public static function getNavigationGroup(): string
    {
        return 'Administração';
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
                    TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    TextInput::make('password')
                        ->label('Senha')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                        ->dehydrated(fn ($state) => filled($state))
                        ->maxLength(255),
                    Select::make('roles')
                        ->label('Papel (Role)')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(User::query()->with('roles'))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Papéis')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Papel')
                    ->relationship('roles', 'name'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->before(function (DeleteAction $action, User $record): void {
                        if ($record->id === auth()->id()) {
                            $action->cancel();
                        }
                    }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
