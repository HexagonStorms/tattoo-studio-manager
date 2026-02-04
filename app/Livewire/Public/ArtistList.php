<?php

namespace App\Livewire\Public;

use App\Models\Artist;
use App\Models\Studio;
use App\Services\TenantService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class ArtistList extends Component
{
    public Studio $studio;
    public string $selectedSpecialty = '';

    public function mount(TenantService $tenantService): void
    {
        $this->studio = $tenantService->current();
    }

    public function getArtistsProperty(): Collection
    {
        $query = Artist::query()
            ->where('studio_id', $this->studio->id)
            ->where('is_active', true)
            ->with(['featuredImages' => fn($q) => $q->limit(1)])
            ->orderBy('sort_order');

        if ($this->selectedSpecialty) {
            $query->whereJsonContains('specialties', $this->selectedSpecialty);
        }

        return $query->get();
    }

    public function getAllSpecialtiesProperty(): array
    {
        $artists = Artist::query()
            ->where('studio_id', $this->studio->id)
            ->where('is_active', true)
            ->pluck('specialties')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        return $artists;
    }

    public function filterBySpecialty(string $specialty): void
    {
        $this->selectedSpecialty = $this->selectedSpecialty === $specialty ? '' : $specialty;
    }

    public function clearFilter(): void
    {
        $this->selectedSpecialty = '';
    }

    public function render()
    {
        return view('livewire.public.artist-list', [
            'artists' => $this->artists,
            'allSpecialties' => $this->allSpecialties,
        ]);
    }
}
