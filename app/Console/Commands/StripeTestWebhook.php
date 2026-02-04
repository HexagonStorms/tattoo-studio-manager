<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\PaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class StripeTestWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:test-webhook
                            {--type=checkout.session.completed : The webhook event type to simulate}
                            {--appointment= : Appointment ID to use for testing}
                            {--url= : Custom webhook URL (defaults to local)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate a Stripe webhook event for testing purposes';

    /**
     * Available webhook types.
     */
    protected array $webhookTypes = [
        'checkout.session.completed',
        'charge.refunded',
        'payment_intent.succeeded',
        'payment_intent.payment_failed',
    ];

    /**
     * Execute the console command.
     */
    public function handle(PaymentService $paymentService): int
    {
        $type = $this->option('type');

        if (!in_array($type, $this->webhookTypes)) {
            $this->error("Invalid webhook type: {$type}");
            $this->line('Available types:');
            foreach ($this->webhookTypes as $webhookType) {
                $this->line("  - {$webhookType}");
            }
            return Command::FAILURE;
        }

        // Check if Stripe is configured
        if (!PaymentService::isConfigured()) {
            $this->warn('Stripe is not configured. Set STRIPE_KEY and STRIPE_SECRET in .env');
            $this->line('Proceeding with test mode...');
        }

        // Get or create test appointment
        $appointment = $this->getOrCreateTestAppointment();

        if (!$appointment) {
            $this->error('Could not find or create test appointment.');
            return Command::FAILURE;
        }

        $this->info("Using appointment ID: {$appointment->id}");
        $this->info("Client: {$appointment->client_name}");

        // Build webhook payload
        $payload = $this->buildWebhookPayload($type, $appointment);

        $this->line('');
        $this->info("Simulating webhook: {$type}");
        $this->line('Payload:');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT));

        // Send to local webhook endpoint
        $url = $this->option('url') ?? route('webhooks.stripe');

        $this->line('');
        $this->info("Sending to: {$url}");

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                // Note: No signature for test mode
            ])->post($url, $payload);

            $this->line('');
            $this->line("Response Status: {$response->status()}");
            $this->line("Response Body: {$response->body()}");

            if ($response->successful()) {
                $this->info('Webhook processed successfully!');

                // Refresh and show appointment state
                $appointment->refresh();
                $this->showAppointmentState($appointment);

                return Command::SUCCESS;
            } else {
                $this->error('Webhook processing failed.');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Error sending webhook: {$e->getMessage()}");
            $this->line('');
            $this->warn('Make sure your local server is running (php artisan serve)');
            return Command::FAILURE;
        }
    }

    /**
     * Get existing appointment or create a test one.
     */
    protected function getOrCreateTestAppointment(): ?Appointment
    {
        $appointmentId = $this->option('appointment');

        if ($appointmentId) {
            $appointment = Appointment::withoutGlobalScopes()->find($appointmentId);
            if (!$appointment) {
                $this->error("Appointment ID {$appointmentId} not found.");
                return null;
            }
            return $appointment;
        }

        // Find an existing appointment
        $appointment = Appointment::withoutGlobalScopes()
            ->whereNull('deposit_paid_at')
            ->first();

        if ($appointment) {
            return $appointment;
        }

        // No appointment found
        $this->warn('No unpaid appointments found.');

        if ($this->confirm('Would you like to use the most recent appointment instead?')) {
            return Appointment::withoutGlobalScopes()->latest()->first();
        }

        return null;
    }

    /**
     * Build webhook payload for the given event type.
     */
    protected function buildWebhookPayload(string $type, Appointment $appointment): array
    {
        $eventId = 'evt_test_' . uniqid();
        $sessionId = $appointment->stripe_checkout_session_id ?? 'cs_test_' . uniqid();
        $paymentIntentId = $appointment->stripe_payment_intent_id ?? 'pi_test_' . uniqid();

        $basePayload = [
            'id' => $eventId,
            'object' => 'event',
            'api_version' => '2023-10-16',
            'created' => time(),
            'livemode' => false,
            'pending_webhooks' => 1,
            'request' => [
                'id' => 'req_test_' . uniqid(),
                'idempotency_key' => null,
            ],
            'type' => $type,
        ];

        $data = match ($type) {
            'checkout.session.completed' => [
                'object' => [
                    'id' => $sessionId,
                    'object' => 'checkout.session',
                    'amount_total' => (int) (($appointment->deposit_amount ?? 50) * 100),
                    'currency' => 'usd',
                    'customer_email' => $appointment->client_email,
                    'metadata' => [
                        'appointment_id' => (string) $appointment->id,
                        'studio_id' => (string) $appointment->studio_id,
                        'client_name' => $appointment->client_name,
                    ],
                    'mode' => 'payment',
                    'payment_intent' => $paymentIntentId,
                    'payment_status' => 'paid',
                    'status' => 'complete',
                    'success_url' => route('booking.success', ['appointment' => $appointment->id]),
                ],
            ],

            'charge.refunded' => [
                'object' => [
                    'id' => 'ch_test_' . uniqid(),
                    'object' => 'charge',
                    'amount' => (int) (($appointment->deposit_amount ?? 50) * 100),
                    'amount_refunded' => (int) (($appointment->deposit_amount ?? 50) * 100),
                    'currency' => 'usd',
                    'payment_intent' => $appointment->stripe_payment_intent_id ?? $paymentIntentId,
                    'refunded' => true,
                    'status' => 'succeeded',
                ],
            ],

            'payment_intent.succeeded' => [
                'object' => [
                    'id' => $paymentIntentId,
                    'object' => 'payment_intent',
                    'amount' => (int) (($appointment->deposit_amount ?? 50) * 100),
                    'currency' => 'usd',
                    'metadata' => [
                        'appointment_id' => (string) $appointment->id,
                        'studio_id' => (string) $appointment->studio_id,
                    ],
                    'status' => 'succeeded',
                ],
            ],

            'payment_intent.payment_failed' => [
                'object' => [
                    'id' => $paymentIntentId,
                    'object' => 'payment_intent',
                    'amount' => (int) (($appointment->deposit_amount ?? 50) * 100),
                    'currency' => 'usd',
                    'last_payment_error' => [
                        'code' => 'card_declined',
                        'message' => 'Your card was declined.',
                        'type' => 'card_error',
                    ],
                    'metadata' => [
                        'appointment_id' => (string) $appointment->id,
                        'studio_id' => (string) $appointment->studio_id,
                    ],
                    'status' => 'requires_payment_method',
                ],
            ],

            default => ['object' => []],
        };

        $basePayload['data'] = $data;

        return $basePayload;
    }

    /**
     * Display current appointment state.
     */
    protected function showAppointmentState(Appointment $appointment): void
    {
        $this->line('');
        $this->info('Current Appointment State:');
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $appointment->id],
                ['Client', $appointment->client_name],
                ['Status', $appointment->status],
                ['Deposit Amount', $appointment->deposit_amount ? '$' . number_format($appointment->deposit_amount, 2) : 'N/A'],
                ['Deposit Paid', $appointment->deposit_paid_at ? $appointment->deposit_paid_at->format('Y-m-d H:i:s') : 'No'],
                ['Payment Method', $appointment->payment_method ?? 'N/A'],
                ['Stripe Session', $appointment->stripe_checkout_session_id ?? 'N/A'],
                ['Payment Intent', $appointment->stripe_payment_intent_id ?? 'N/A'],
                ['Refunded', $appointment->refunded_at ? $appointment->refunded_at->format('Y-m-d H:i:s') : 'No'],
                ['Refund Amount', $appointment->refund_amount ? '$' . number_format($appointment->refund_amount, 2) : 'N/A'],
            ]
        );
    }
}
