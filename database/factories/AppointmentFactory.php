<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Artist;
use App\Models\Service;
use App\Models\Studio;
use App\Models\Waiver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement([
            Appointment::STATUS_PENDING,
            Appointment::STATUS_CONFIRMED,
            Appointment::STATUS_COMPLETED,
            Appointment::STATUS_CANCELLED,
            Appointment::STATUS_NO_SHOW,
        ]);

        $scheduledAt = match ($status) {
            Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED => $this->faker->dateTimeBetween('+1 day', '+2 months'),
            Appointment::STATUS_COMPLETED, Appointment::STATUS_NO_SHOW => $this->faker->dateTimeBetween('-3 months', '-1 day'),
            Appointment::STATUS_CANCELLED => $this->faker->dateTimeBetween('-1 month', '+1 month'),
        };

        $durationMinutes = $this->faker->randomElement([60, 90, 120, 180, 240, 360]);
        $estimatedPrice = $this->faker->randomElement([150, 200, 300, 500, 800, 1200, null]);
        $depositAmount = $estimatedPrice ? round($estimatedPrice * 0.2, 2) : $this->faker->randomElement([50, 100, 150, null]);

        $placements = ['Arm', 'Forearm', 'Upper Arm', 'Leg', 'Thigh', 'Calf', 'Back', 'Chest', 'Shoulder', 'Ribs', 'Hand', 'Foot', 'Neck', 'Wrist', 'Ankle'];

        $tattooDescriptions = [
            'A small rose with thorns',
            'Traditional style anchor',
            'Japanese koi fish swimming upstream',
            'Geometric wolf head',
            'Minimalist mountain range',
            'Watercolor galaxy design',
            'Realistic portrait of my grandmother',
            'Celtic knot pattern',
            'Script lettering of a meaningful quote',
            'Blackwork mandala',
            'Neo-traditional fox',
            'Fine line botanical illustration',
            'Trash polka skull design',
            'Cover-up of an old tribal tattoo',
        ];

        return [
            'client_name' => $this->faker->name(),
            'client_email' => $this->faker->unique()->safeEmail(),
            'client_phone' => $this->faker->phoneNumber(),
            'scheduled_at' => $scheduledAt,
            'duration_minutes' => $durationMinutes,
            'status' => $status,
            'notes' => $this->faker->optional(60)->sentence(),
            'artist_notes' => $this->faker->optional(30)->sentence(),
            'tattoo_description' => $this->faker->randomElement($tattooDescriptions),
            'tattoo_placement' => $this->faker->randomElement($placements),
            'estimated_price' => $estimatedPrice,
            'deposit_amount' => $depositAmount,
            'deposit_paid_at' => $depositAmount && $this->faker->boolean(70) ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'payment_method' => $this->faker->optional(50)->randomElement(['stripe', 'cash', 'venmo', 'square', 'paypal']),
            'payment_reference' => $this->faker->optional(30)->uuid(),
            'cancelled_at' => $status === Appointment::STATUS_CANCELLED ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'cancellation_reason' => $status === Appointment::STATUS_CANCELLED ? $this->faker->randomElement(['Client requested', 'Schedule conflict', 'Artist unavailable', 'No deposit received', null]) : null,
        ];
    }

    /**
     * Create a pending appointment.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_PENDING,
            'scheduled_at' => $this->faker->dateTimeBetween('+1 day', '+2 months'),
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Create a confirmed appointment.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_CONFIRMED,
            'scheduled_at' => $this->faker->dateTimeBetween('+1 day', '+2 months'),
            'deposit_paid_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Create a completed appointment.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_COMPLETED,
            'scheduled_at' => $this->faker->dateTimeBetween('-3 months', '-1 day'),
            'deposit_paid_at' => $this->faker->dateTimeBetween('-4 months', '-3 months'),
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Create a cancelled appointment.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_CANCELLED,
            'cancelled_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'cancellation_reason' => $this->faker->randomElement(['Client requested', 'Schedule conflict', 'Artist unavailable', 'No deposit received']),
        ]);
    }

    /**
     * Create a no-show appointment.
     */
    public function noShow(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_NO_SHOW,
            'scheduled_at' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ]);
    }

    /**
     * Create an appointment for tomorrow.
     */
    public function tomorrow(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_CONFIRMED,
            'scheduled_at' => now()->addDay()->setHour($this->faker->numberBetween(10, 16))->setMinute(0),
        ]);
    }

    /**
     * Create an appointment for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Appointment::STATUS_CONFIRMED,
            'scheduled_at' => now()->setHour($this->faker->numberBetween(10, 16))->setMinute(0),
        ]);
    }

    /**
     * Associate the appointment with a studio.
     */
    public function forStudio(Studio $studio): static
    {
        return $this->state(fn (array $attributes) => [
            'studio_id' => $studio->id,
        ]);
    }

    /**
     * Associate the appointment with an artist.
     */
    public function forArtist(Artist $artist): static
    {
        return $this->state(fn (array $attributes) => [
            'artist_id' => $artist->id,
            'studio_id' => $artist->studio_id,
        ]);
    }

    /**
     * Associate the appointment with a service.
     */
    public function forService(Service $service): static
    {
        return $this->state(fn (array $attributes) => [
            'service_id' => $service->id,
            'duration_minutes' => $service->duration_minutes,
        ]);
    }

    /**
     * Associate the appointment with a waiver.
     */
    public function withWaiver(Waiver $waiver): static
    {
        return $this->state(fn (array $attributes) => [
            'waiver_id' => $waiver->id,
        ]);
    }
}
