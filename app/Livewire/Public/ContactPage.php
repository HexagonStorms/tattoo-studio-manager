<?php

namespace App\Livewire\Public;

use App\Models\Studio;
use App\Services\TenantService;
use Livewire\Component;

class ContactPage extends Component
{
    public Studio $studio;

    // Contact form fields
    public string $name = '';
    public string $email = '';
    public string $message = '';

    public bool $submitted = false;

    protected $rules = [
        'name' => 'required|min:2|max:100',
        'email' => 'required|email|max:255',
        'message' => 'required|min:10|max:2000',
    ];

    public function mount(TenantService $tenantService): void
    {
        $this->studio = $tenantService->current();
    }

    public function submit(): void
    {
        $this->validate();

        // TODO: Implement actual form submission (email, database, etc.)
        // For now, just mark as submitted
        $this->submitted = true;

        // Reset form
        $this->reset(['name', 'email', 'message']);
    }

    public function getBusinessHoursProperty(): array
    {
        // Placeholder - will come from studio settings later
        return $this->studio->settings['business_hours'] ?? [
            'Monday' => '10:00 AM - 8:00 PM',
            'Tuesday' => '10:00 AM - 8:00 PM',
            'Wednesday' => '10:00 AM - 8:00 PM',
            'Thursday' => '10:00 AM - 8:00 PM',
            'Friday' => '10:00 AM - 9:00 PM',
            'Saturday' => '11:00 AM - 7:00 PM',
            'Sunday' => 'Closed',
        ];
    }

    public function render()
    {
        return view('livewire.public.contact-page', [
            'businessHours' => $this->businessHours,
        ]);
    }
}
