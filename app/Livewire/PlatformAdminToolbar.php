<?php

namespace App\Livewire;

use App\Models\Studio;
use Filament\Facades\Filament;
use Livewire\Component;

class PlatformAdminToolbar extends Component
{
    public ?string $currentStudioSlug = null;

    public function mount(): void
    {
        $tenant = Filament::getTenant();
        $this->currentStudioSlug = $tenant?->slug;
    }

    public function switchStudio(string $slug): void
    {
        $this->redirect("/admin/{$slug}");
    }

    public function render()
    {
        return view('livewire.platform-admin-toolbar', [
            'studios' => Studio::orderBy('name')->get(),
            'currentStudio' => Filament::getTenant(),
        ]);
    }
}
