<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStudio;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Appointment extends Model
{
    use HasFactory, BelongsToStudio, Notifiable;

    protected $fillable = [
        'studio_id',
        'artist_id',
        'service_id',
        'waiver_id',
        'client_name',
        'client_email',
        'client_phone',
        'scheduled_at',
        'duration_minutes',
        'status',
        'notes',
        'artist_notes',
        'tattoo_description',
        'tattoo_placement',
        'estimated_price',
        'deposit_amount',
        'deposit_paid_at',
        'payment_method',
        'payment_reference',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'refunded_at',
        'refund_amount',
        'refund_reason',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'deposit_paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'estimated_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'duration_minutes' => 'integer',
    ];

    /**
     * Payment method constants.
     */
    public const PAYMENT_METHOD_STRIPE = 'stripe';
    public const PAYMENT_METHOD_CASH = 'cash';
    public const PAYMENT_METHOD_VENMO = 'venmo';
    public const PAYMENT_METHOD_ZELLE = 'zelle';
    public const PAYMENT_METHOD_SQUARE = 'square';
    public const PAYMENT_METHOD_PAYPAL = 'paypal';
    public const PAYMENT_METHOD_OTHER = 'other';

    /**
     * Get all available payment methods.
     */
    public static function paymentMethods(): array
    {
        return [
            self::PAYMENT_METHOD_STRIPE => 'Stripe (Card)',
            self::PAYMENT_METHOD_CASH => 'Cash',
            self::PAYMENT_METHOD_VENMO => 'Venmo',
            self::PAYMENT_METHOD_ZELLE => 'Zelle',
            self::PAYMENT_METHOD_SQUARE => 'Square (In-Person)',
            self::PAYMENT_METHOD_PAYPAL => 'PayPal',
            self::PAYMENT_METHOD_OTHER => 'Other',
        ];
    }

    /**
     * Get manual payment methods (excludes Stripe).
     */
    public static function manualPaymentMethods(): array
    {
        $methods = self::paymentMethods();
        unset($methods[self::PAYMENT_METHOD_STRIPE]);
        return $methods;
    }

    /**
     * Status constants.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    /**
     * Get all available statuses.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_NO_SHOW => 'No Show',
        ];
    }

    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function waiver(): BelongsTo
    {
        return $this->belongsTo(Waiver::class);
    }

    /**
     * Get the end time of the appointment.
     */
    public function getEndsAtAttribute(): Carbon
    {
        return $this->scheduled_at->copy()->addMinutes($this->duration_minutes);
    }

    /**
     * Get formatted duration display.
     */
    public function getFormattedDurationAttribute(): string
    {
        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    /**
     * Check if appointment is upcoming.
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->scheduled_at->isFuture() &&
               in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Check if deposit has been paid.
     */
    public function getIsDepositPaidAttribute(): bool
    {
        return $this->deposit_paid_at !== null;
    }

    /**
     * Check if deposit has been refunded.
     */
    public function getIsRefundedAttribute(): bool
    {
        return $this->refunded_at !== null;
    }

    /**
     * Check if this is a Stripe payment.
     */
    public function getIsStripePaymentAttribute(): bool
    {
        return $this->payment_method === self::PAYMENT_METHOD_STRIPE;
    }

    /**
     * Check if deposit can be refunded.
     */
    public function canBeRefunded(): bool
    {
        return $this->is_deposit_paid &&
               !$this->is_refunded &&
               $this->is_stripe_payment &&
               $this->stripe_payment_intent_id !== null;
    }

    /**
     * Record a refund.
     */
    public function recordRefund(float $amount, ?string $reason = null): void
    {
        $this->update([
            'refunded_at' => now(),
            'refund_amount' => $amount,
            'refund_reason' => $reason,
        ]);
    }

    /**
     * Check if the appointment can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]) &&
               $this->scheduled_at->isFuture();
    }

    /**
     * Cancel the appointment.
     */
    public function cancel(?string $reason = null): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Confirm the appointment.
     */
    public function confirm(): bool
    {
        if ($this->status !== self::STATUS_PENDING) {
            return false;
        }

        $this->update(['status' => self::STATUS_CONFIRMED]);

        return true;
    }

    /**
     * Mark the appointment as completed.
     */
    public function complete(): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED])) {
            return false;
        }

        $this->update(['status' => self::STATUS_COMPLETED]);

        return true;
    }

    /**
     * Mark the appointment as no-show.
     */
    public function markNoShow(): bool
    {
        if (!in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED])) {
            return false;
        }

        $this->update(['status' => self::STATUS_NO_SHOW]);

        return true;
    }

    /**
     * Record deposit payment.
     */
    public function recordDeposit(float $amount, string $method, ?string $reference = null): void
    {
        $this->update([
            'deposit_amount' => $amount,
            'deposit_paid_at' => now(),
            'payment_method' => $method,
            'payment_reference' => $reference,
        ]);
    }

    /**
     * Scope to get appointments for a specific date.
     */
    public function scopeForDate($query, Carbon $date)
    {
        return $query->whereDate('scheduled_at', $date);
    }

    /**
     * Scope to get appointments within a date range.
     */
    public function scopeBetweenDates($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('scheduled_at', [$start, $end]);
    }

    /**
     * Scope to get upcoming appointments.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())
                     ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED])
                     ->orderBy('scheduled_at');
    }

    /**
     * Scope to get past appointments.
     */
    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now())
                     ->orderByDesc('scheduled_at');
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get appointments for a specific artist.
     */
    public function scopeForArtist($query, int $artistId)
    {
        return $query->where('artist_id', $artistId);
    }

    /**
     * Scope to get appointments that need confirmation.
     */
    public function scopeNeedsConfirmation($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                     ->where('scheduled_at', '>=', now());
    }

    /**
     * Check if this appointment overlaps with another time range.
     */
    public function overlapsWithRange(Carbon $start, Carbon $end): bool
    {
        return $this->scheduled_at->lt($end) && $this->ends_at->gt($start);
    }

    /**
     * Route notifications for the mail channel.
     */
    public function routeNotificationForMail(): string
    {
        return $this->client_email;
    }
}
