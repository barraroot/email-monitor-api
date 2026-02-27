<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MailEvent>
 */
class MailEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventTypes = [
            'mail_in_accepted',
            'mail_in_rejected',
            'mail_out_sent',
            'mail_out_deferred',
            'mail_bounced',
            'mail_delivered_local',
            'queue_depth',
        ];

        $eventType = $this->faker->randomElement($eventTypes);

        $meta = null;

        if ($eventType === 'queue_depth') {
            $meta = ['queue_depth' => $this->faker->numberBetween(0, 500)];
        }

        return [
            'occurred_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'direction' => $this->faker->randomElement(['inbound', 'outbound', 'local']),
            'event_type' => $eventType,
            'exim_message_id' => $this->faker->boolean(70) ? $this->faker->uuid : null,
            'sender' => $this->faker->boolean(80) ? $this->faker->safeEmail : null,
            'recipient' => $this->faker->boolean(80) ? $this->faker->safeEmail : null,
            'domain' => $this->faker->domainName,
            'whm_account' => $this->faker->userName,
            'ip' => $this->faker->boolean(70) ? $this->faker->ipv4 : null,
            'remote_mta' => $this->faker->boolean(60) ? $this->faker->domainName : null,
            'smtp_code' => $this->faker->boolean(50) ? (string) $this->faker->numberBetween(200, 599) : null,
            'smtp_response' => $this->faker->boolean(50) ? $this->faker->sentence(6) : null,
            'status' => $this->faker->randomElement(['delivered', 'rejected', 'deferred', 'bounced', 'accepted']),
            'error_category' => $this->faker->boolean(40) ? $this->faker->randomElement(['policy', 'auth', 'rbl', 'mailboxfull', 'unknownuser', 'spam', 'timeout', 'dns']) : null,
            'error_message' => $this->faker->boolean(30) ? $this->faker->sentence(8) : null,
            'meta' => $meta,
        ];
    }
}
