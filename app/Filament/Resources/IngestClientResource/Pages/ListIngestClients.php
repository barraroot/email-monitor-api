<?php

namespace App\Filament\Resources\IngestClientResource\Pages;

use App\Filament\Resources\IngestClientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIngestClients extends ListRecords
{
    protected static string $resource = IngestClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
