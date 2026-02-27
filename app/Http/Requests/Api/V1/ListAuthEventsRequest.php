<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ListAuthEventsRequest extends FormRequest
{
    private const ISO8601_REGEX = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'start_at' => ['sometimes', 'date', 'regex:'.self::ISO8601_REGEX],
            'end_at' => ['sometimes', 'date', 'regex:'.self::ISO8601_REGEX],
            'domain' => ['sometimes', 'string', 'max:255'],
            'whm_account' => ['sometimes', 'string', 'max:255'],
            'proto' => ['sometimes', 'in:imap,pop3,smtp'],
            'auth_result' => ['sometimes', 'in:success,fail'],
            'user_email' => ['sometimes', 'email', 'max:255'],
            'ip' => ['sometimes', 'ip'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ];
    }
}
