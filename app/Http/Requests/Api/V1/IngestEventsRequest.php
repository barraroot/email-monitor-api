<?php

namespace App\Http\Requests\Api\V1;

use App\Models\IngestClient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class IngestEventsRequest extends FormRequest
{
    private const ISO8601_REGEX = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/';

    public function authorize(): bool
    {
        $secret = $this->header('X-Ingest-Secret');

        if (! is_string($secret) || $secret === '') {
            return false;
        }

        $configuredSecret = config('email-monitor.ingest.shared_secret');

        if (is_string($configuredSecret) && $configuredSecret !== '' && hash_equals($configuredSecret, $secret)) {
            return true;
        }

        return IngestClient::query()
            ->where('is_active', true)
            ->get()
            ->contains(function (IngestClient $client) use ($secret): bool {
                return Hash::check($secret, $client->shared_secret_hash);
            });
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $max = (int) config('email-monitor.ingest.max_events_per_request', 5000);

        return [
            'source' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'whm_account' => ['required', 'string', 'max:255'],
            'mail_events' => ['required_without:auth_events', 'array', 'max:'.$max],
            'auth_events' => ['required_without:mail_events', 'array', 'max:'.$max],

            'mail_events.*.occurred_at' => ['required', 'date', 'regex:'.self::ISO8601_REGEX],
            'mail_events.*.direction' => ['required', 'in:inbound,outbound,local'],
            'mail_events.*.event_type' => ['required', 'string', 'max:255'],
            'mail_events.*.exim_message_id' => ['nullable', 'string', 'max:255'],
            'mail_events.*.sender' => ['nullable', 'string', 'max:255'],
            'mail_events.*.recipient' => ['nullable', 'string', 'max:255'],
            'mail_events.*.domain' => ['nullable', 'string', 'max:255'],
            'mail_events.*.whm_account' => ['nullable', 'string', 'max:255'],
            'mail_events.*.ip' => ['nullable', 'ip'],
            'mail_events.*.remote_mta' => ['nullable', 'string', 'max:255'],
            'mail_events.*.smtp_code' => ['nullable', 'string', 'max:50'],
            'mail_events.*.smtp_response' => ['nullable', 'string', 'max:255'],
            'mail_events.*.status' => ['nullable', 'string', 'max:50'],
            'mail_events.*.error_category' => ['nullable', 'string', 'max:100'],
            'mail_events.*.error_message' => ['nullable', 'string'],
            'mail_events.*.meta' => ['nullable', 'array'],

            'auth_events.*.occurred_at' => ['required', 'date', 'regex:'.self::ISO8601_REGEX],
            'auth_events.*.proto' => ['required', 'in:imap,pop3,smtp'],
            'auth_events.*.event_type' => ['required', 'string', 'max:255'],
            'auth_events.*.user_email' => ['nullable', 'email', 'max:255'],
            'auth_events.*.domain' => ['nullable', 'string', 'max:255'],
            'auth_events.*.whm_account' => ['nullable', 'string', 'max:255'],
            'auth_events.*.ip' => ['nullable', 'ip'],
            'auth_events.*.auth_result' => ['required', 'in:success,fail'],
            'auth_events.*.failure_reason' => ['nullable', 'string', 'max:255'],
            'auth_events.*.meta' => ['nullable', 'array'],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $max = (int) config('email-monitor.ingest.max_events_per_request', 5000);

            $mailEvents = $this->input('mail_events', []);
            $authEvents = $this->input('auth_events', []);

            $total = count($mailEvents) + count($authEvents);

            if ($total > $max) {
                $validator->errors()->add('events', "O payload excede o limite de {$max} eventos por requisição.");
            }
        });
    }
}
