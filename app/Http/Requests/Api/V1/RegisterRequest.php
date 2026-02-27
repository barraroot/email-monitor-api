<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'whm_account' => $this->input('whm_account', config('email-monitor.defaults.whm_account')),
            'domain' => $this->input('domain', config('email-monitor.defaults.domain')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'whm_account' => ['required', 'string', 'max:255'],
            'domain' => ['required', 'string', 'max:255'],
            'cpanel_host' => ['required', 'url', 'max:255'],
            'cpanel_api_token' => ['required', 'string'],
            'cpanel_username' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cpanel_host.url' => 'O campo cpanel_host deve ser uma URL válida.',
        ];
    }
}
