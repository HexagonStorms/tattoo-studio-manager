<?php

namespace App\Services;

use App\Models\Studio;
use Illuminate\Support\Facades\Storage;

/**
 * Service for preparing studio data for public-facing views.
 *
 * This service provides convenient methods for accessing studio
 * information in Blade templates and Livewire components.
 */
class PublicStudioService
{
    protected Studio $studio;

    public function __construct(Studio $studio)
    {
        $this->studio = $studio;
    }

    /**
     * Create a new instance from a studio.
     */
    public static function make(Studio $studio): self
    {
        return new self($studio);
    }

    /**
     * Get the underlying studio model.
     */
    public function getStudio(): Studio
    {
        return $this->studio;
    }

    /**
     * Get all public-facing studio information.
     */
    public function all(): array
    {
        return [
            'basic' => $this->getBasicInfo(),
            'branding' => $this->getBranding(),
            'contact' => $this->getContactInfo(),
            'content' => $this->getWebsiteContent(),
            'social' => $this->getSocialLinks(),
            'hours' => $this->getBusinessHours(),
            'booking' => $this->getBookingInfo(),
        ];
    }

    /**
     * Get basic studio information.
     */
    public function getBasicInfo(): array
    {
        return [
            'name' => $this->studio->name,
            'slug' => $this->studio->slug,
            'timezone' => $this->studio->timezone ?? 'America/Los_Angeles',
        ];
    }

    /**
     * Get branding information.
     */
    public function getBranding(): array
    {
        return [
            'logo_url' => $this->studio->logo_path
                ? Storage::url($this->studio->logo_path)
                : null,
            'primary_color' => $this->studio->primary_color ?? '#f59e0b',
            'secondary_color' => $this->studio->secondary_color,
            'has_logo' => ! empty($this->studio->logo_path),
        ];
    }

    /**
     * Get contact information.
     */
    public function getContactInfo(): array
    {
        return [
            'email' => $this->studio->email,
            'phone' => $this->studio->phone,
            'address' => $this->studio->address,
            'has_contact_info' => ! empty($this->studio->email) || ! empty($this->studio->phone),
        ];
    }

    /**
     * Get website content (tagline, about, SEO).
     */
    public function getWebsiteContent(): array
    {
        return [
            'tagline' => $this->studio->tagline,
            'about_text' => $this->studio->about_text,
            'about_text_plain' => strip_tags($this->studio->about_text),
            'meta_description' => $this->studio->meta_description ?: $this->generateMetaDescription(),
        ];
    }

    /**
     * Generate a meta description if none is set.
     */
    protected function generateMetaDescription(): string
    {
        $name = $this->studio->name;
        $tagline = $this->studio->tagline;

        if ($tagline) {
            return "{$name} - {$tagline}";
        }

        return "{$name} - Professional tattoo studio";
    }

    /**
     * Get active social links.
     */
    public function getSocialLinks(): array
    {
        $links = $this->studio->getActiveSocialLinks();

        return [
            'links' => $links,
            'has_social' => ! empty($links),
            'instagram' => $links['instagram'] ?? null,
            'facebook' => $links['facebook'] ?? null,
            'tiktok' => $links['tiktok'] ?? null,
            'yelp' => $links['yelp'] ?? null,
        ];
    }

    /**
     * Get business hours information.
     */
    public function getBusinessHours(): array
    {
        return [
            'raw' => $this->studio->business_hours,
            'formatted' => $this->studio->getFormattedBusinessHours(),
            'is_open' => $this->studio->isCurrentlyOpen(),
            'today' => $this->getTodayHours(),
        ];
    }

    /**
     * Get today's hours.
     */
    protected function getTodayHours(): array
    {
        $now = now()->setTimezone($this->studio->timezone ?? 'America/Los_Angeles');
        $dayOfWeek = $now->format('l');

        foreach ($this->studio->business_hours as $day) {
            if ($day['day'] === $dayOfWeek) {
                return [
                    'day' => $day['day'],
                    'is_closed' => $day['is_closed'] ?? false,
                    'open' => $day['open'] ?? null,
                    'close' => $day['close'] ?? null,
                    'formatted' => ($day['is_closed'] ?? false)
                        ? 'Closed'
                        : $this->formatTimeRange($day['open'] ?? '10:00', $day['close'] ?? '19:00'),
                ];
            }
        }

        return [
            'day' => $dayOfWeek,
            'is_closed' => true,
            'open' => null,
            'close' => null,
            'formatted' => 'Closed',
        ];
    }

    /**
     * Format a time range for display.
     */
    protected function formatTimeRange(string $open, string $close): string
    {
        $openFormatted = date('g:i A', strtotime($open));
        $closeFormatted = date('g:i A', strtotime($close));

        return "{$openFormatted} - {$closeFormatted}";
    }

    /**
     * Get booking configuration.
     */
    public function getBookingInfo(): array
    {
        $enabled = $this->studio->booking_enabled;

        return [
            'enabled' => $enabled,
            'minimum_notice_hours' => $this->studio->booking_minimum_notice_hours,
            'minimum_notice_text' => $this->formatNoticeText($this->studio->booking_minimum_notice_hours),
            'deposit' => [
                'type' => $this->studio->booking_deposit_type,
                'amount' => $this->studio->booking_deposit_amount,
                'formatted' => $this->formatDeposit(),
            ],
            'instructions' => $this->studio->booking_instructions,
            'can_book' => $enabled,
        ];
    }

    /**
     * Format the minimum notice text.
     */
    protected function formatNoticeText(int $hours): string
    {
        if ($hours < 24) {
            return "{$hours} hour" . ($hours !== 1 ? 's' : '');
        }

        $days = (int) floor($hours / 24);

        return "{$days} day" . ($days !== 1 ? 's' : '');
    }

    /**
     * Format the deposit amount for display.
     */
    protected function formatDeposit(): string
    {
        $type = $this->studio->booking_deposit_type;
        $amount = $this->studio->booking_deposit_amount;

        if ($type === 'percentage') {
            return "{$amount}%";
        }

        return '$' . number_format($amount, 2);
    }

    /**
     * Get CSS custom properties for theming.
     */
    public function getCssVariables(): string
    {
        $primary = $this->studio->primary_color ?? '#f59e0b';
        $secondary = $this->studio->secondary_color ?? $primary;

        return <<<CSS
            --studio-primary: {$primary};
            --studio-secondary: {$secondary};
            --studio-primary-rgb: {$this->hexToRgb($primary)};
            --studio-secondary-rgb: {$this->hexToRgb($secondary)};
        CSS;
    }

    /**
     * Convert hex color to RGB values.
     */
    protected function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r}, {$g}, {$b}";
    }

    /**
     * Get structured data (JSON-LD) for SEO.
     */
    public function getStructuredData(): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'TattooParlor',
            'name' => $this->studio->name,
            'description' => $this->studio->meta_description ?: $this->generateMetaDescription(),
        ];

        if ($this->studio->address) {
            $data['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $this->studio->address,
            ];
        }

        if ($this->studio->phone) {
            $data['telephone'] = $this->studio->phone;
        }

        if ($this->studio->email) {
            $data['email'] = $this->studio->email;
        }

        if ($this->studio->logo_path) {
            $data['image'] = Storage::url($this->studio->logo_path);
        }

        // Add opening hours
        $openingHours = [];
        foreach ($this->studio->business_hours as $day) {
            if (! ($day['is_closed'] ?? false)) {
                $dayAbbrev = substr($day['day'], 0, 2);
                $openingHours[] = "{$dayAbbrev} {$day['open']}-{$day['close']}";
            }
        }
        if (! empty($openingHours)) {
            $data['openingHours'] = $openingHours;
        }

        // Add social links
        $socialLinks = $this->studio->getActiveSocialLinks();
        if (! empty($socialLinks)) {
            $data['sameAs'] = array_values($socialLinks);
        }

        return $data;
    }

    /**
     * Get JSON-LD script tag for SEO.
     */
    public function getStructuredDataScript(): string
    {
        $json = json_encode($this->getStructuredData(), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        return '<script type="application/ld+json">' . $json . '</script>';
    }
}
