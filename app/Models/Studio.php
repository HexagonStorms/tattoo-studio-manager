<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasCurrentTenantLabel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Studio extends Model implements HasAvatar, HasCurrentTenantLabel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'custom_domain',
        'logo_path',
        'primary_color',
        'secondary_color',
        'email',
        'phone',
        'address',
        'timezone',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Default settings structure for new studios.
     */
    public const DEFAULT_SETTINGS = [
        'tagline' => 'Your custom tattoo experience',
        'about_text' => 'Welcome to our studio. We specialize in creating unique, custom tattoos in a clean and professional environment.',
        'meta_description' => '',
        'social_links' => [
            'instagram' => '',
            'facebook' => '',
            'tiktok' => '',
            'yelp' => '',
        ],
        'business_hours' => [
            ['day' => 'Monday', 'open' => '10:00', 'close' => '19:00', 'is_closed' => false],
            ['day' => 'Tuesday', 'open' => '10:00', 'close' => '19:00', 'is_closed' => false],
            ['day' => 'Wednesday', 'open' => '10:00', 'close' => '19:00', 'is_closed' => false],
            ['day' => 'Thursday', 'open' => '10:00', 'close' => '19:00', 'is_closed' => false],
            ['day' => 'Friday', 'open' => '10:00', 'close' => '19:00', 'is_closed' => false],
            ['day' => 'Saturday', 'open' => '10:00', 'close' => '19:00', 'is_closed' => false],
            ['day' => 'Sunday', 'open' => '10:00', 'close' => '19:00', 'is_closed' => true],
        ],
        'booking_enabled' => false,
        'booking_minimum_notice_hours' => 24,
        'booking_deposit_type' => 'percentage', // 'percentage' or 'fixed'
        'booking_deposit_amount' => 20,
        'booking_instructions' => '',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function waivers(): HasMany
    {
        return $this->hasMany(Waiver::class);
    }

    public function artists(): HasMany
    {
        return $this->hasMany(Artist::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->logo_path
            ? Storage::url($this->logo_path)
            : null;
    }

    public function getCurrentTenantLabel(): string
    {
        return 'Current studio';
    }

    /*
    |--------------------------------------------------------------------------
    | Settings Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Get a specific setting value with fallback to default.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->settings ?? [];
        $defaults = self::DEFAULT_SETTINGS;

        return $settings[$key] ?? $default ?? ($defaults[$key] ?? null);
    }

    /**
     * Set a specific setting value.
     */
    public function setSetting(string $key, mixed $value): self
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;

        return $this;
    }

    /**
     * Get the studio tagline.
     */
    protected function tagline(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getSetting('tagline', self::DEFAULT_SETTINGS['tagline']),
        );
    }

    /**
     * Get the studio about text.
     */
    protected function aboutText(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getSetting('about_text', self::DEFAULT_SETTINGS['about_text']),
        );
    }

    /**
     * Get the studio meta description.
     */
    protected function metaDescription(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getSetting('meta_description', ''),
        );
    }

    /**
     * Get the studio business hours.
     */
    protected function businessHours(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getSetting('business_hours', self::DEFAULT_SETTINGS['business_hours']),
        );
    }

    /**
     * Get the studio social links.
     */
    protected function socialLinks(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getSetting('social_links', self::DEFAULT_SETTINGS['social_links']),
        );
    }

    /**
     * Check if online booking is enabled.
     */
    protected function bookingEnabled(): Attribute
    {
        return Attribute::make(
            get: fn () => (bool) $this->getSetting('booking_enabled', false),
        );
    }

    /**
     * Get the minimum notice hours for booking.
     */
    protected function bookingMinimumNoticeHours(): Attribute
    {
        return Attribute::make(
            get: fn () => (int) $this->getSetting('booking_minimum_notice_hours', 24),
        );
    }

    /**
     * Get the deposit type (percentage or fixed).
     */
    protected function bookingDepositType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getSetting('booking_deposit_type', 'percentage'),
        );
    }

    /**
     * Get the deposit amount.
     */
    protected function bookingDepositAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => (float) $this->getSetting('booking_deposit_amount', 20),
        );
    }

    /**
     * Get the booking instructions.
     */
    protected function bookingInstructions(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getSetting('booking_instructions', ''),
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get formatted business hours for display.
     */
    public function getFormattedBusinessHours(): array
    {
        $hours = $this->business_hours;
        $formatted = [];

        foreach ($hours as $day) {
            if ($day['is_closed'] ?? false) {
                $formatted[$day['day']] = 'Closed';
            } else {
                $open = $this->formatTime($day['open'] ?? '10:00');
                $close = $this->formatTime($day['close'] ?? '19:00');
                $formatted[$day['day']] = "{$open} - {$close}";
            }
        }

        return $formatted;
    }

    /**
     * Format time from 24h to 12h format.
     */
    protected function formatTime(string $time): string
    {
        return date('g:i A', strtotime($time));
    }

    /**
     * Check if the studio is currently open.
     */
    public function isCurrentlyOpen(): bool
    {
        $now = now()->setTimezone($this->timezone ?? 'America/Los_Angeles');
        $dayOfWeek = $now->format('l');
        $currentTime = $now->format('H:i');

        foreach ($this->business_hours as $day) {
            if ($day['day'] === $dayOfWeek) {
                if ($day['is_closed'] ?? false) {
                    return false;
                }

                $open = $day['open'] ?? '10:00';
                $close = $day['close'] ?? '19:00';

                return $currentTime >= $open && $currentTime <= $close;
            }
        }

        return false;
    }

    /**
     * Get active social links (non-empty).
     */
    public function getActiveSocialLinks(): array
    {
        return array_filter($this->social_links, fn ($url) => ! empty($url));
    }

    /**
     * Initialize settings with defaults if not set.
     */
    public function initializeSettings(): self
    {
        if (empty($this->settings)) {
            $this->settings = self::DEFAULT_SETTINGS;
        } else {
            // Merge with defaults to ensure all keys exist
            $this->settings = array_merge(self::DEFAULT_SETTINGS, $this->settings);
        }

        return $this;
    }
}
