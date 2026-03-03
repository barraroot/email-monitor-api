<?php

namespace App\Filament\Resources\CpanelAccountResource\Pages;

use App\Filament\Resources\CpanelAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCpanelAccounts extends ListRecords
{
    protected static string $resource = CpanelAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
