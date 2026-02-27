<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuthEvent>
 */
class AuthEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventType = $this->faker->randomElement(['login_success', 'login_failed', 'logout', 'auth_failed']);
        $authResult = str_contains($eventType, 'success') ? 'success' : 'fail';

        return [
            'occurred_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'proto' => $this->faker->randomElement(['imap', 'pop3', 'smtp']),
            'event_type' => $eventType,
            'user_email' => $this->faker->boolean(80) ? $this->faker->safeEmail : null,
            'domain' => $this->faker->domainName,
            'whm_account' => $this->faker->userName,
            'ip' => $this->faker->boolean(70) ? $this->faker->ipv4 : null,
            'auth_result' => $authResult,
            'failure_reason' => $authResult === 'fail'
                ? $this->faker->randomElement(['invalid_password', 'unknown_user', 'locked', 'timeout'])
                : null,
            'meta' => null,
        ];
    }
}
