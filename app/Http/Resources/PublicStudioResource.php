<?php

namespace App\Http\Resources;

use App\Models\Studio;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Resource for formatting studio data for public-facing views.
 *
 * This resource provides all the information needed to render
 * a white-labeled studio website, including branding, content,
 * hours, and social links.
 */
class PublicStudioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Studio $studio */
        $studio = $this->resource;

        return [
            // Basic Info
            'name' => $studio->name,
            'slug' => $studio->slug,

            // Branding
            'logo_url' => $studio->logo_path ? Storage::url($studio->logo_path) : null,
            'primary_color' => $studio->primary_color ?? '#f59e0b',
            'secondary_color' => $studio->secondary_color,

            // Contact
            'email' => $studio->email,
            'phone' => $studio->phone,
            'address' => $studio->address,
            'timezone' => $studio->timezone ?? 'America/Los_Angeles',

            // Website Content
            'tagline' => $studio->tagline,
            'about_text' => $studio->about_text,
            'meta_description' => $studio->meta_description,

            // Social Links (only non-empty)
            'social_links' => $studio->getActiveSocialLinks(),

            // Business Hours
            'business_hours' => $studio->business_hours,
            'formatted_business_hours' => $studio->getFormattedBusinessHours(),
            'is_currently_open' => $studio->isCurrentlyOpen(),

            // Booking
            'booking_enabled' => $studio->booking_enabled,
            'booking_settings' => $studio->booking_enabled ? [
                'minimum_notice_hours' => $studio->booking_minimum_notice_hours,
                'deposit_type' => $studio->booking_deposit_type,
                'deposit_amount' => $studio->booking_deposit_amount,
                'instructions' => $studio->booking_instructions,
            ] : null,
        ];
    }

    /**
     * Get a simplified version for list views.
     */
    public static function collection($resource): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        return parent::collection($resource);
    }

    /**
     * Create a new resource instance from a studio model.
     */
    public static function fromStudio(Studio $studio): self
    {
        return new self($studio);
    }

    /**
     * Get public studio data as an array (helper for Blade views).
     */
    public static function getPublicData(Studio $studio): array
    {
        return (new self($studio))->toArray(request());
    }
}
