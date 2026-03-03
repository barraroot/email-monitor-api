<?php

namespace App\Filament\Resources\CpanelAccountResource\Pages;

use App\Filament\Resources\CpanelAccountResource;
use App\Services\CpanelClientService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Crypt;

class CreateCpanelAccount extends CreateRecord
{
    protected static string $resource = CpanelAccountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $token = $data['api_token_plain'] ?? null;

        if ($token) {
            $cpanelClient = app(CpanelClientService::class);
            $verifySsl = (bool) config('services.cpanel.verify_ssl', true);

            if (! $cpanelClient->validateToken($data['cpanel_host'], $data['cpanel_username'], $token, $verifySsl)) {
                Notification::make()
                    ->title('Token inválido')
                    ->body('As credenciais informadas não foram aceitas pelo cPanel.')
                    ->danger()
                    ->send();

                $this->halt();
            }

            $data['api_token_encrypted'] = Crypt::encryptString($token);
            $data['token_last_verified_at'] = now();
        }
        $data['user_id'] = auth()->id();
        unset($data['api_token_plain']);

        return $data;
    }
}
