<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CpanelAccount>
 */
class CpanelAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'whm_account' => $this->faker->userName,
            'domain' => $this->faker->domainName,
            'cpanel_username' => $this->faker->userName,
            'cpanel_host' => 'https://'.$this->faker->domainName.':2087',
            'api_token_encrypted' => Crypt::encryptString($this->faker->sha256),
            'token_last_verified_at' => $this->faker->dateTimeBetween('-10 days', 'now'),
            'meta' => null,
        ];
    }
}
