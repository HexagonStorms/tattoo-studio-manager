<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepositPaidNotification extends Notification implements ShouldQueue
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
        $artist = $this->appointment->artist;

        return (new MailMessage)
            ->subject('Deposit Received - ' . $studio->name)
            ->greeting('Hello ' . $this->appointment->client_name . ',')
            ->line('Thank you! Your deposit has been received for your upcoming appointment.')
            ->line('**Appointment Details:**')
            ->line('- **Date:** ' . $this->appointment->scheduled_at->format('l, F j, Y'))
            ->line('- **Time:** ' . $this->appointment->scheduled_at->format('g:i A'))
            ->line('- **Artist:** ' . ($artist ? $artist->display_name : 'TBD'))
            ->line('- **Deposit Amount:** $' . number_format($this->appointment->deposit_amount, 2))
            ->line('')
            ->line('Your deposit will be applied to your final total on the day of your appointment.')
            ->line('')
            ->line('**What to bring:**')
            ->line('- Valid photo ID')
            ->line('- Reference images (if applicable)')
            ->line('')
            ->line('Please arrive 10-15 minutes before your scheduled time.')
            ->line('')
            ->line('If you need to reschedule or cancel, please contact us at least 48 hours in advance.')
            ->salutation('See you soon!')
            ->from($studio->email ?? config('mail.from.address'), $studio->name);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'deposit_paid',
            'appointment_id' => $this->appointment->id,
            'client_name' => $this->appointment->client_name,
            'client_email' => $this->appointment->client_email,
            'amount' => $this->appointment->deposit_amount,
            'payment_method' => $this->appointment->payment_method,
            'scheduled_at' => $this->appointment->scheduled_at->toIso8601String(),
        ];
    }

    /**
     * Get the database notification type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'deposit-paid';
    }
}
