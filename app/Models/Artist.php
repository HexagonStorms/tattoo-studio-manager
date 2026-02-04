<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStudio;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Artist extends Model
{
    use HasFactory, BelongsToStudio;

    protected $fillable = [
        'studio_id',
        'user_id',
        'display_name',
        'slug',
        'bio',
        'specialties',
        'instagram_handle',
        'hourly_rate',
        'is_active',
        'is_accepting_bookings',
        'sort_order',
    ];

    protected $casts = [
        'specialties' => 'array',
        'hourly_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_accepting_bookings' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Artist $artist) {
            if (empty($artist->slug)) {
                $artist->slug = Str::slug($artist->display_name);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function portfolioImages(): HasMany
    {
        return $this->hasMany(PortfolioImage::class)->orderBy('sort_order');
    }

    public function featuredImages(): HasMany
    {
        return $this->hasMany(PortfolioImage::class)
            ->where('is_featured', true)
            ->orderBy('sort_order');
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(ArtistAvailability::class)->orderBy('day_of_week')->orderBy('start_time');
    }

    public function timeOffs(): HasMany
    {
        return $this->hasMany(ArtistTimeOff::class)->orderBy('start_date');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class)->orderByDesc('scheduled_at');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAcceptingBookings($query)
    {
        return $query->where('is_accepting_bookings', true);
    }

    public function getInstagramUrlAttribute(): ?string
    {
        if (empty($this->instagram_handle)) {
            return null;
        }

        $handle = ltrim($this->instagram_handle, '@');
        return "https://instagram.com/{$handle}";
    }

    /**
     * Check if the artist is available at a specific datetime.
     *
     * @param Carbon $datetime The datetime to check
     * @return bool True if the artist is available
     */
    public function isAvailableAt(Carbon $datetime): bool
    {
        // Check if accepting bookings
        if (!$this->is_accepting_bookings || !$this->is_active) {
            return false;
        }

        // Check regular availability for this day of week
        $dayOfWeek = $datetime->dayOfWeek; // 0 = Sunday, 6 = Saturday
        $timeString = $datetime->format('H:i:s');

        $availability = $this->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->where('start_time', '<=', $timeString)
            ->where('end_time', '>', $timeString)
            ->first();

        if (!$availability) {
            return false;
        }

        // Check for time off
        $hasTimeOff = $this->timeOffs()
            ->where(function ($query) use ($datetime) {
                $query->where('start_date', '<=', $datetime)
                      ->where('end_date', '>=', $datetime);
            })
            ->exists();

        if ($hasTimeOff) {
            return false;
        }

        // Check for existing appointments that overlap
        $hasConflict = $this->appointments()
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
            ->where('scheduled_at', '<=', $datetime)
            ->whereRaw("datetime(scheduled_at, '+' || duration_minutes || ' minutes') > ?", [$datetime->toDateTimeString()])
            ->exists();

        return !$hasConflict;
    }

    /**
     * Get available time slots for a specific date.
     *
     * @param Carbon $date The date to check
     * @param int $duration Duration in minutes needed for the slot
     * @param int $slotInterval Interval between slot start times (default 30 minutes)
     * @return array Array of available Carbon datetime objects
     */
    public function getAvailableTimeSlotsForDate(Carbon $date, int $duration, int $slotInterval = 30): array
    {
        $slots = [];

        // Check if accepting bookings
        if (!$this->is_accepting_bookings || !$this->is_active) {
            return $slots;
        }

        $dayOfWeek = $date->dayOfWeek;

        // Get availability blocks for this day
        $availabilityBlocks = $this->availabilities()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->orderBy('start_time')
            ->get();

        if ($availabilityBlocks->isEmpty()) {
            return $slots;
        }

        // Get time offs that overlap with this date
        $dateStart = $date->copy()->startOfDay();
        $dateEnd = $date->copy()->endOfDay();

        $timeOffs = $this->timeOffs()
            ->where('start_date', '<=', $dateEnd)
            ->where('end_date', '>=', $dateStart)
            ->get();

        // Get existing appointments for this date
        $appointments = $this->appointments()
            ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
            ->whereDate('scheduled_at', $date)
            ->get();

        foreach ($availabilityBlocks as $block) {
            $blockStart = $date->copy()->setTimeFromTimeString($block->getRawOriginal('start_time'));
            $blockEnd = $date->copy()->setTimeFromTimeString($block->getRawOriginal('end_time'));

            // Generate potential slots
            $current = $blockStart->copy();
            while ($current->copy()->addMinutes($duration)->lte($blockEnd)) {
                $slotEnd = $current->copy()->addMinutes($duration);

                // Check if slot is blocked by time off
                $blockedByTimeOff = false;
                foreach ($timeOffs as $timeOff) {
                    if ($timeOff->overlapsRange($current, $slotEnd)) {
                        $blockedByTimeOff = true;
                        break;
                    }
                }

                if (!$blockedByTimeOff) {
                    // Check if slot conflicts with existing appointments
                    $hasConflict = false;
                    foreach ($appointments as $appointment) {
                        if ($appointment->overlapsWithRange($current, $slotEnd)) {
                            $hasConflict = true;
                            break;
                        }
                    }

                    if (!$hasConflict) {
                        // Don't include slots in the past
                        if ($current->isFuture()) {
                            $slots[] = $current->copy();
                        }
                    }
                }

                $current->addMinutes($slotInterval);
            }
        }

        return $slots;
    }

    /**
     * Get the next available slot for the artist.
     *
     * @param int $duration Duration needed in minutes
     * @param int $daysAhead How many days ahead to search
     * @return Carbon|null The next available datetime or null
     */
    public function getNextAvailableSlot(int $duration, int $daysAhead = 30): ?Carbon
    {
        $date = Carbon::today();

        for ($i = 0; $i < $daysAhead; $i++) {
            $slots = $this->getAvailableTimeSlotsForDate($date, $duration);
            if (!empty($slots)) {
                return $slots[0];
            }
            $date->addDay();
        }

        return null;
    }
}
