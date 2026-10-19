<?php

namespace Database\Factories;

use App\Models\License;
use App\Models\LicenseAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LicenseAssignment> */
class LicenseAssignmentFactory extends Factory
{
    protected $model = LicenseAssignment::class;

    public function definition(): array
    {
        return [
            'license_id' => License::factory(),
            'user_email' => fake()->unique()->safeEmail(),
            'assigned_at' => now(),
            'released_at' => null,
        ];
    }
}
