<?php

namespace Database\Factories;

use App\Models\Artist;
use App\Models\ArtistAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ArtistAvailability>
 */
class ArtistAvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day_of_week' => $this->faker->numberBetween(0, 6),
            'start_time' => '10:00',
            'end_time' => '18:00',
            'is_available' => true,
        ];
    }

    /**
     * Set the day of week.
     */
    public function forDay(int $dayOfWeek): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => $dayOfWeek,
        ]);
    }

    /**
     * Create weekday availability (Monday-Friday, 10am-6pm).
     */
    public function weekday(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => $this->faker->numberBetween(1, 5),
            'start_time' => '10:00',
            'end_time' => '18:00',
            'is_available' => true,
        ]);
    }

    /**
     * Create Saturday availability (11am-5pm).
     */
    public function saturday(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => ArtistAvailability::SATURDAY,
            'start_time' => '11:00',
            'end_time' => '17:00',
            'is_available' => true,
        ]);
    }

    /**
     * Create Sunday availability (unavailable).
     */
    public function sunday(): static
    {
        return $this->state(fn (array $attributes) => [
            'day_of_week' => ArtistAvailability::SUNDAY,
            'is_available' => false,
        ]);
    }

    /**
     * Mark as unavailable.
     */
    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_available' => false,
        ]);
    }

    /**
     * Associate with a specific artist.
     */
    public function forArtist(Artist $artist): static
    {
        return $this->state(fn (array $attributes) => [
            'artist_id' => $artist->id,
        ]);
    }

    /**
     * Create a full week of standard availability for an artist.
     * Monday-Friday 10am-6pm, Saturday 11am-5pm, Sunday off.
     */
    public static function createStandardWeekForArtist(Artist $artist): void
    {
        // Monday - Friday: 10am - 6pm
        for ($day = 1; $day <= 5; $day++) {
            ArtistAvailability::create([
                'artist_id' => $artist->id,
                'day_of_week' => $day,
                'start_time' => '10:00',
                'end_time' => '18:00',
                'is_available' => true,
            ]);
        }

        // Saturday: 11am - 5pm
        ArtistAvailability::create([
            'artist_id' => $artist->id,
            'day_of_week' => ArtistAvailability::SATURDAY,
            'start_time' => '11:00',
            'end_time' => '17:00',
            'is_available' => true,
        ]);

        // Sunday: Unavailable
        ArtistAvailability::create([
            'artist_id' => $artist->id,
            'day_of_week' => ArtistAvailability::SUNDAY,
            'start_time' => '10:00',
            'end_time' => '18:00',
            'is_available' => false,
        ]);
    }
}
