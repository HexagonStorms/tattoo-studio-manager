<?php

namespace App\Livewire\Public;

use App\Models\Artist;
use App\Models\Studio;
use App\Services\TenantService;
use Livewire\Component;

class HomePage extends Component
{
    public Studio $studio;

    public function mount(TenantService $tenantService): void
    {
        $this->studio = $tenantService->current();
    }

    public function getFeaturedArtistsProperty()
    {
        return Artist::query()
            ->where('studio_id', $this->studio->id)
            ->where('is_active', true)
            ->whereHas('featuredImages')
            ->with(['featuredImages' => fn($q) => $q->limit(1)])
            ->orderBy('sort_order')
            ->limit(3)
            ->get();
    }

    public function getTaglineProperty(): string
    {
        return $this->studio->tagline;
    }

    public function getAboutTextProperty(): string
    {
        return $this->studio->about_text;
    }

    public function render()
    {
        return view('livewire.public.home-page', [
            'featuredArtists' => $this->featuredArtists,
            'tagline' => $this->tagline,
            'aboutText' => $this->aboutText,
        ]);
    }
}
