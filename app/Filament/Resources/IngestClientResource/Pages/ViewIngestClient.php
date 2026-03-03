<?php

namespace App\Filament\Resources\IngestClientResource\Pages;

use App\Filament\Resources\IngestClientResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewIngestClient extends ViewRecord
{
    protected static string $resource = IngestClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
