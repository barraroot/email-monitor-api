<?php

namespace App\Filament\Resources\CpanelAccountResource\Pages;

use App\Filament\Resources\CpanelAccountResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCpanelAccount extends ViewRecord
{
    protected static string $resource = CpanelAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
