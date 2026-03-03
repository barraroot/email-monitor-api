<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailboxResource\Pages;
use App\Models\Mailbox;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MailboxResource extends Resource
{
    protected static ?string $model = Mailbox::class;

    protected static ?string $navigationLabel = 'Mailboxes';

    protected static ?string $modelLabel = 'Mailbox';

    protected static ?string $pluralModelLabel = 'Mailboxes';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-inbox';
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
                    TextInput::make('whm_account')
                        ->label('Conta WHM')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('domain')
                        ->label('Domínio')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255)
                        ->rules([
                            fn ($get): \Closure => function (string $attribute, string $value, \Closure $fail) use ($get) {
                                $domain = $get('domain');
                                if ($domain && ! str_ends_with($value, '@'.$domain)) {
                                    $fail("O e-mail deve pertencer ao domínio {$domain}.");
                                }
                            },
                        ]),
                    Toggle::make('is_active')
                        ->label('Ativo')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Mailbox::query())
            ->defaultSort('email')
            ->columns([
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('domain')
                    ->label('Domínio')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('whm_account')
                    ->label('Conta WHM')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('domain')
                    ->form([
                        TextInput::make('domain')->label('Domínio'),
                        TextInput::make('whm_account')->label('Conta WHM'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['domain'], fn ($q, $val) => $q->where('domain', $val))
                            ->when($data['whm_account'], fn ($q, $val) => $q->where('whm_account', $val));
                    }),
                TernaryFilter::make('is_active')->label('Ativo'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
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
            'index' => Pages\ListMailboxes::route('/'),
            'create' => Pages\CreateMailbox::route('/create'),
            'view' => Pages\ViewMailbox::route('/{record}'),
            'edit' => Pages\EditMailbox::route('/{record}/edit'),
        ];
    }
}
