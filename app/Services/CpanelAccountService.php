<?php

namespace App\Services;

use App\Models\CpanelAccount;
use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class CpanelAccountService
{
    public function __construct(private readonly CpanelClientService $cpanelClient)
    {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function registerForUser(User $user, array $data): CpanelAccount
    {
        $host = $data['cpanel_host'];
        $username = $data['cpanel_username'] ?? config('services.cpanel.username');
        $token = $data['cpanel_api_token'];
        $verifySsl = (bool) config('services.cpanel.verify_ssl', true);

        if (! $username) {
            throw ValidationException::withMessages([
                'cpanel_username' => ['Informe o usuário do cPanel.'],
            ]);
        }

        if (! $this->cpanelClient->validateToken($host, $username, $token, $verifySsl)) {
            throw ValidationException::withMessages([
                'cpanel_api_token' => ['Credenciais do cPanel inválidas.'],
            ]);
        }

        return CpanelAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'domain' => $data['domain'],
            ],
            [
                'whm_account' => $data['whm_account'],
                'cpanel_username' => $username,
                'cpanel_host' => $host,
                'api_token_encrypted' => Crypt::encryptString($token),
                'token_last_verified_at' => now(),
                'meta' => $data['meta'] ?? null,
            ]
        );
    }

    /**
     * @return int
     */
    public function syncMailboxes(CpanelAccount $account): int
    {
        $token = Crypt::decryptString($account->api_token_encrypted);
        $verifySsl = (bool) config('services.cpanel.verify_ssl', true);

        $mailboxes = $this->cpanelClient->listMailboxes(
            $account->cpanel_host,
            $account->cpanel_username,
            $token,
            $account->domain,
            $verifySsl
        );

        $count = 0;

        foreach ($mailboxes as $mailbox) {
            $email = $mailbox['email'] ?? null;

            if (! $email) {
                continue;
            }

            Mailbox::updateOrCreate(
                ['email' => $email],
                [
                    'whm_account' => $account->whm_account,
                    'domain' => $account->domain,
                    'is_active' => true,
                ]
            );

            $count++;
        }

        return $count;
    }
}
