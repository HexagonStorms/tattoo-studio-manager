<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Studio>
 */
class StudioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company() . ' Tattoo';

        return [
            'name' => $name,
            'slug' => $this->faker->unique()->slug(2),
            'custom_domain' => null,
            'logo_path' => null,
            'primary_color' => $this->faker->hexColor(),
            'secondary_color' => $this->faker->optional()->hexColor(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'timezone' => $this->faker->randomElement([
                'America/New_York',
                'America/Los_Angeles',
                'America/Chicago',
                'America/Denver',
            ]),
            'settings' => null,
        ];
    }
}
