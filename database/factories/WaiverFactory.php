<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Waiver>
 */
class WaiverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasAllergies = $this->faker->boolean(20);
        
        return [
            'client_name' => $this->faker->name(),
            'client_email' => $this->faker->unique()->safeEmail(),
            'date_of_birth' => $this->faker->date('Y-m-d', '-18 years'),
            'address' => $this->faker->address(),
            'phone_number' => $this->faker->phoneNumber(),
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'medical_conditions' => $this->faker->optional(30)->paragraph(),
            'has_allergies' => $hasAllergies,
            'allergies_description' => $hasAllergies ? $this->faker->paragraph() : null,
            'tattoo_description' => $this->faker->paragraph(),
            'tattoo_placement' => $this->faker->randomElement(['Arm', 'Leg', 'Back', 'Chest', 'Shoulder', 'Neck', 'Hand']),
            'accepted_terms' => true,
            'accepted_aftercare' => true,
            'signed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'signature' => $this->faker->name(),
        ];
    }
}
