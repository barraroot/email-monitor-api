<?php

namespace App\Filament\Resources\IngestClientResource\Pages;

use App\Filament\Resources\IngestClientResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateIngestClient extends CreateRecord
{
    protected static string $resource = IngestClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $secret = Str::random(64);
        Log::info($secret);
        $this->generatedSecret = $secret;
        $data['shared_secret_hash'] = hash('sha256', $secret);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (isset($this->generatedSecret)) {
            Notification::make()
                ->title('Cliente criado — copie o shared secret')
                ->body("Shared secret: **{$this->generatedSecret}**\n\nEste valor não será exibido novamente.")
                ->success()
                ->persistent()
                ->send();
        }
    }

    private string $generatedSecret = '';
}
