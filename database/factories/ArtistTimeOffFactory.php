<?php

namespace Database\Factories;

use App\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ArtistTimeOff>
 */
class ArtistTimeOffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('+1 week', '+3 months');
        $endDate = (clone $startDate)->modify('+' . $this->faker->numberBetween(1, 14) . ' days');

        $reasons = [
            'Vacation',
            'Personal day',
            'Convention attendance',
            'Family event',
            'Medical appointment',
            'Guest spot at another studio',
            'Workshop/Training',
            null,
        ];

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $this->faker->randomElement($reasons),
            'is_all_day' => true,
        ];
    }

    /**
     * Create a single day time off.
     */
    public function singleDay(): static
    {
        return $this->state(function (array $attributes) {
            $date = $this->faker->dateTimeBetween('+1 week', '+3 months');
            return [
                'start_date' => $date,
                'end_date' => $date,
                'is_all_day' => true,
            ];
        });
    }

    /**
     * Create a vacation (week or more).
     */
    public function vacation(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = $this->faker->dateTimeBetween('+1 month', '+6 months');
            $endDate = (clone $startDate)->modify('+' . $this->faker->numberBetween(5, 14) . ' days');
            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'reason' => 'Vacation',
                'is_all_day' => true,
            ];
        });
    }

    /**
     * Create a partial day time off (specific hours).
     */
    public function partialDay(): static
    {
        return $this->state(function (array $attributes) {
            $date = $this->faker->dateTimeBetween('+1 week', '+1 month');
            $startHour = $this->faker->numberBetween(8, 12);
            $endHour = $this->faker->numberBetween(13, 18);

            return [
                'start_date' => (clone $date)->setTime($startHour, 0),
                'end_date' => (clone $date)->setTime($endHour, 0),
                'reason' => $this->faker->randomElement(['Doctor appointment', 'Personal errand', 'Meeting']),
                'is_all_day' => false,
            ];
        });
    }

    /**
     * Create past time off (for testing history).
     */
    public function past(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = $this->faker->dateTimeBetween('-3 months', '-1 week');
            $endDate = (clone $startDate)->modify('+' . $this->faker->numberBetween(1, 7) . ' days');
            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
        });
    }

    /**
     * Create time off happening now.
     */
    public function current(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = $this->faker->dateTimeBetween('-3 days', 'now');
            $endDate = (clone $startDate)->modify('+' . $this->faker->numberBetween(3, 7) . ' days');
            return [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
        });
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
}
