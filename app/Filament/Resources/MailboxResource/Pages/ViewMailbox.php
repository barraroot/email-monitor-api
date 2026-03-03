<?php

namespace App\Filament\Resources\MailboxResource\Pages;

use App\Filament\Resources\MailboxResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMailbox extends ViewRecord
{
    protected static string $resource = MailboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
