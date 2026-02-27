<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class CpanelClientService
{
    public function validateToken(string $baseUrl, ?string $username, string $token, bool $verifySsl = true): bool
    {
        if (! $username) {
            return false;
        }

        $response = $this->request($baseUrl, $username, $token, $verifySsl, 'cpanel')
            ->get('/execute/ServerInformation/get_information');

        if ($response->ok()) {
            return true;
        }

        $response = $this->request($baseUrl, $username, $token, $verifySsl, 'whm')
            ->get('/json-api/version');

        return $response->ok();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listDomains(string $baseUrl, ?string $username, string $token, bool $verifySsl = true): array
    {
        if (! $username) {
            return [];
        }

        $response = $this->request($baseUrl, $username, $token, $verifySsl, 'cpanel')
            ->get('/execute/DomainInfo/domains_data');

        return $response->json('data.domains') ?? [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listMailboxes(string $baseUrl, ?string $username, string $token, string $domain, bool $verifySsl = true): array
    {
        if (! $username) {
            return [];
        }

        $response = $this->request($baseUrl, $username, $token, $verifySsl, 'cpanel')
            ->get('/execute/Email/list_pops', [
                'domain' => $domain,
            ]);

        return $response->json('data') ?? [];
    }

    private function request(string $baseUrl, string $username, string $token, bool $verifySsl, string $mode): PendingRequest
    {
        return Http::baseUrl(rtrim($baseUrl, '/'))
            ->withOptions([
                'verify' => $verifySsl,
            ])
            ->acceptJson()
            ->withHeaders([
                'Authorization' => sprintf('%s %s:%s', $mode, $username, $token),
            ]);
    }
}
