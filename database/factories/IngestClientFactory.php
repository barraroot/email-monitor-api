<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IngestClient>
 */
class IngestClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $secret = $this->faker->password(16, 32);

        return [
            'name' => $this->faker->company,
            'shared_secret_hash' => Hash::make($secret),
            'is_active' => true,
        ];
    }
}
