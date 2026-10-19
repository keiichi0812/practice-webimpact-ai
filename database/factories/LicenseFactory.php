<?php

namespace Database\Factories;

use App\Models\License;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<License> */
class LicenseFactory extends Factory
{
    protected $model = License::class;

    public function definition(): array
    {
        $starts = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'customer_name' => fake()->company(),
            'product_code' => fake()->randomElement(['WI-CORE', 'WI-PLUS', 'WI-ENT']),
            'seats' => fake()->numberBetween(1, 50),
            'seats_used' => 0,
            'starts_on' => $starts,
            'expires_on' => (clone $starts)->modify('+1 year'),
            'status' => License::STATUS_ACTIVE,
        ];
    }

    public function expiresOn(string $date): static
    {
        return $this->state(fn () => ['expires_on' => $date]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => License::STATUS_CANCELLED]);
    }

    public function expired(): static
    {
        return $this->state(fn () => ['status' => License::STATUS_EXPIRED]);
    }
}
