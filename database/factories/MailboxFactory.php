<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mailbox>
 */
class MailboxFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $domain = $this->faker->domainName;

        return [
            'whm_account' => $this->faker->userName,
            'domain' => $domain,
            'email' => $this->faker->userName.'@'.$domain,
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
