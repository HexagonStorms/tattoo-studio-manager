<?php

namespace App\Services;

use App\Models\Studio;

class TenantService
{
    protected ?Studio $currentStudio = null;

    /**
     * Get the current tenant (studio).
     */
    public function current(): ?Studio
    {
        return $this->currentStudio;
    }

    /**
     * Set the current tenant (studio).
     */
    public function set(Studio $studio): void
    {
        $this->currentStudio = $studio;
    }

    /**
     * Clear the current tenant.
     */
    public function clear(): void
    {
        $this->currentStudio = null;
    }

    /**
     * Check if a tenant is currently set.
     */
    public function has(): bool
    {
        return $this->currentStudio !== null;
    }

    /**
     * Get CSS color variables for the current tenant.
     */
    public function colors(): array
    {
        if (!$this->currentStudio) {
            return [
                '--color-primary' => '#1f2937',
                '--color-secondary' => '#6b7280',
            ];
        }

        return [
            '--color-primary' => $this->currentStudio->primary_color ?? '#1f2937',
            '--color-secondary' => $this->currentStudio->secondary_color ?? '#6b7280',
        ];
    }

    /**
     * Get CSS style string for inline styles.
     */
    public function colorStyles(): string
    {
        $colors = $this->colors();
        $styles = [];

        foreach ($colors as $property => $value) {
            $styles[] = "{$property}: {$value}";
        }

        return implode('; ', $styles);
    }

    /**
     * Get the logo URL for the current tenant.
     */
    public function logoUrl(): ?string
    {
        if (!$this->currentStudio || !$this->currentStudio->logo_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::url($this->currentStudio->logo_path);
    }
}
