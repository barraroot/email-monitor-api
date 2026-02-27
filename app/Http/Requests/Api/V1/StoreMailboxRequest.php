<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreMailboxRequest extends FormRequest
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
        return [
            'whm_account' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:mailboxes,email'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
