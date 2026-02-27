<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMailboxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $mailboxId = $this->route('mailbox')?->id;

        return [
            'whm_account' => ['sometimes', 'string', 'max:255'],
            'domain' => ['sometimes', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('mailboxes', 'email')->ignore($mailboxId),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
