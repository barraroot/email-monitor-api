<?php

namespace App\Filament\Resources\IngestClientResource\Pages;

use App\Filament\Resources\IngestClientResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIngestClient extends EditRecord
{
    protected static string $resource = IngestClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
