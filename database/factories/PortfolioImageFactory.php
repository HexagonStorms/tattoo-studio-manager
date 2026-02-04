<?php

namespace Database\Factories;

use App\Models\Artist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PortfolioImage>
 */
class PortfolioImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $styles = [
            'Traditional',
            'Japanese',
            'Blackwork',
            'Realism',
            'Neo-Traditional',
            'Watercolor',
            'Geometric',
            'Dotwork',
            'Minimalist',
            'Fine Line',
        ];

        return [
            'image_path' => 'portfolio/' . $this->faker->uuid() . '.jpg',
            'title' => $this->faker->optional(70)->words(3, true),
            'description' => $this->faker->optional(50)->sentence(),
            'style' => $this->faker->randomElement($styles),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_featured' => $this->faker->boolean(20),
        ];
    }

    /**
     * Mark the image as featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Set a specific style for the image.
     */
    public function withStyle(string $style): static
    {
        return $this->state(fn (array $attributes) => [
            'style' => $style,
        ]);
    }

    /**
     * Associate the image with an artist.
     */
    public function forArtist(Artist $artist): static
    {
        return $this->state(fn (array $attributes) => [
            'artist_id' => $artist->id,
        ]);
    }
}
