<?php

namespace Database\Factories;

use App\Models\Studio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Artist>
 */
class ArtistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $displayName = $this->faker->name();

        $allSpecialties = [
            'Traditional',
            'Japanese',
            'Blackwork',
            'Realism',
            'Neo-Traditional',
            'Watercolor',
            'Geometric',
            'Dotwork',
            'Tribal',
            'Script/Lettering',
            'Portrait',
            'New School',
            'Trash Polka',
            'Minimalist',
            'Fine Line',
        ];

        return [
            'display_name' => $displayName,
            'slug' => Str::slug($displayName) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'bio' => $this->faker->paragraphs(2, true),
            'specialties' => $this->faker->randomElements($allSpecialties, $this->faker->numberBetween(2, 5)),
            'instagram_handle' => '@' . $this->faker->userName(),
            'hourly_rate' => $this->faker->randomElement([100, 125, 150, 175, 200, 250, 300]),
            'is_active' => true,
            'is_accepting_bookings' => $this->faker->boolean(80),
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }

    /**
     * Indicate that the artist is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'is_accepting_bookings' => false,
        ]);
    }

    /**
     * Indicate that the artist is not accepting bookings.
     */
    public function notAcceptingBookings(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_accepting_bookings' => false,
        ]);
    }

    /**
     * Associate the artist with a user.
     */
    public function withUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }

    /**
     * Associate the artist with a studio.
     */
    public function forStudio(Studio $studio): static
    {
        return $this->state(fn (array $attributes) => [
            'studio_id' => $studio->id,
        ]);
    }
}
