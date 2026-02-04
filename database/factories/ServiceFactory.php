<?php

namespace Database\Factories;

use App\Models\Studio;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $services = [
            ['name' => 'Consultation', 'price_type' => 'consultation', 'duration' => 30, 'price' => null],
            ['name' => 'Small Tattoo', 'price_type' => 'fixed', 'duration' => 60, 'price' => 150],
            ['name' => 'Medium Piece', 'price_type' => 'hourly', 'duration' => 180, 'price' => 175],
            ['name' => 'Large Piece', 'price_type' => 'hourly', 'duration' => 360, 'price' => 175],
            ['name' => 'Half Sleeve', 'price_type' => 'hourly', 'duration' => 480, 'price' => 200],
            ['name' => 'Full Sleeve', 'price_type' => 'hourly', 'duration' => 720, 'price' => 200],
            ['name' => 'Touch-up', 'price_type' => 'fixed', 'duration' => 45, 'price' => 75],
            ['name' => 'Cover-up', 'price_type' => 'hourly', 'duration' => 240, 'price' => 200],
            ['name' => 'Flash Tattoo', 'price_type' => 'fixed', 'duration' => 90, 'price' => 200],
            ['name' => 'Custom Design Session', 'price_type' => 'fixed', 'duration' => 60, 'price' => 100],
        ];

        $service = $this->faker->randomElement($services);
        $name = $service['name'];

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'description' => $this->faker->optional(70)->paragraph(),
            'duration_minutes' => $service['duration'],
            'price_type' => $service['price_type'],
            'price' => $service['price'],
            'deposit_required' => $service['price_type'] !== 'consultation',
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 10),
        ];
    }

    /**
     * Create a consultation service.
     */
    public function consultation(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Consultation',
            'slug' => 'consultation',
            'description' => 'Free consultation to discuss your tattoo idea, placement, and pricing.',
            'duration_minutes' => 30,
            'price_type' => 'consultation',
            'price' => null,
            'deposit_required' => false,
            'sort_order' => 1,
        ]);
    }

    /**
     * Create a small tattoo service.
     */
    public function smallTattoo(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Small Tattoo',
            'slug' => 'small-tattoo',
            'description' => 'Perfect for pieces under 2 inches. Includes simple designs, words, or small symbols.',
            'duration_minutes' => 60,
            'price_type' => 'fixed',
            'price' => 150.00,
            'deposit_required' => true,
            'sort_order' => 2,
        ]);
    }

    /**
     * Create a medium piece service.
     */
    public function mediumPiece(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Medium Piece',
            'slug' => 'medium-piece',
            'description' => 'For tattoos between 2-6 inches. Charged by the hour.',
            'duration_minutes' => 180,
            'price_type' => 'hourly',
            'price' => 175.00,
            'deposit_required' => true,
            'sort_order' => 3,
        ]);
    }

    /**
     * Create a large piece service.
     */
    public function largePiece(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Large Piece',
            'slug' => 'large-piece',
            'description' => 'For pieces larger than 6 inches. May require multiple sessions.',
            'duration_minutes' => 360,
            'price_type' => 'hourly',
            'price' => 175.00,
            'deposit_required' => true,
            'sort_order' => 4,
        ]);
    }

    /**
     * Create a touch-up service.
     */
    public function touchUp(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Touch-up',
            'slug' => 'touch-up',
            'description' => 'Free touch-ups within 3 months of original tattoo. After 3 months, a flat fee applies.',
            'duration_minutes' => 45,
            'price_type' => 'fixed',
            'price' => 75.00,
            'deposit_required' => true,
            'sort_order' => 5,
        ]);
    }

    /**
     * Associate the service with a studio.
     */
    public function forStudio(Studio $studio): static
    {
        return $this->state(fn (array $attributes) => [
            'studio_id' => $studio->id,
        ]);
    }

    /**
     * Make the service inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
