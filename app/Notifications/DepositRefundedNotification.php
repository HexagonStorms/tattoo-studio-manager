<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepositRefundedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Appointment $appointment
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $studio = $this->appointment->studio;

        $message = (new MailMessage)
            ->subject('Deposit Refund Confirmation - ' . $studio->name)
            ->greeting('Hello ' . $this->appointment->client_name . ',')
            ->line('This is a confirmation that your deposit has been refunded.')
            ->line('')
            ->line('**Refund Details:**')
            ->line('- **Amount:** $' . number_format($this->appointment->refund_amount, 2))
            ->line('- **Original Appointment:** ' . $this->appointment->scheduled_at->format('l, F j, Y \a\t g:i A'));

        if ($this->appointment->refund_reason) {
            $message->line('- **Reason:** ' . $this->appointment->refund_reason);
        }

        $message->line('')
            ->line('The refund has been processed and should appear on your original payment method within 5-10 business days, depending on your bank.')
            ->line('')
            ->line('If you have any questions about this refund, please don\'t hesitate to contact us.')
            ->salutation('Thank you,')
            ->from($studio->email ?? config('mail.from.address'), $studio->name);

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'deposit_refunded',
            'appointment_id' => $this->appointment->id,
            'client_name' => $this->appointment->client_name,
            'client_email' => $this->appointment->client_email,
            'refund_amount' => $this->appointment->refund_amount,
            'refund_reason' => $this->appointment->refund_reason,
            'refunded_at' => $this->appointment->refunded_at->toIso8601String(),
        ];
    }

    /**
     * Get the database notification type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'deposit-refunded';
    }
}
